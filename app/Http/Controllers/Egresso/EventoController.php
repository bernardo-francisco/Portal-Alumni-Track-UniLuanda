<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\InscricaoEvento;
use App\Services\NotificacaoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventoController extends Controller
{
    // ============================================================
    // 📋 LISTA DE EVENTOS
    // ============================================================
    public function index(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        // ✅ Obter a unidade do egresso (via curso)
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        // ============================================================
        // 🔍 QUERY BASE — eventos visíveis para este egresso
        // ============================================================
        $query = Evento::with('unidade')
            ->where('is_active', true)
            ->where(function ($q) use ($unidadeEgressoId) {
                // ✅ Eventos globais (sem unidade específica)
                $q->whereNull('unidade_id');

                // ✅ OU eventos da unidade do egresso (se tiver unidade)
                if ($unidadeEgressoId) {
                    $q->orWhere('unidade_id', $unidadeEgressoId);
                }
            });

        // ============================================================
        // 🔍 FILTROS
        // ============================================================
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhere('local', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // ✅ NOVO — filtro de âmbito (todos / minha / globais)
        if ($request->filled('ambito') && $unidadeEgressoId) {
            if ($request->ambito === 'minha') {
                $query->where('unidade_id', $unidadeEgressoId);
            } elseif ($request->ambito === 'global') {
                $query->whereNull('unidade_id');
            }
        }

        $query->orderByRaw('CASE WHEN data_inicio >= NOW() THEN 0 ELSE 1 END')
              ->orderBy('data_inicio', 'asc');

        $eventos = $query->paginate(10)->appends($request->query());

        $eventosInscritos = InscricaoEvento::where('egresso_id', $egresso->id)
                                           ->pluck('evento_id')
                                           ->toArray();

        // ============================================================
        // 📊 ESTATÍSTICAS (filtradas por unidade do egresso)
        // ============================================================
        $baseQuery = Evento::where('is_active', true)
            ->where(function ($q) use ($unidadeEgressoId) {
                $q->whereNull('unidade_id');
                if ($unidadeEgressoId) {
                    $q->orWhere('unidade_id', $unidadeEgressoId);
                }
            });

        $totalEventos   = (clone $baseQuery)->count();
        $totalFuturos   = (clone $baseQuery)->where('data_inicio', '>=', now())->count();
        $totalPassados  = (clone $baseQuery)->where('data_inicio', '<', now())->count();
        $totalInscricoes = InscricaoEvento::where('egresso_id', $egresso->id)->count();

        return view('egresso.eventos.index', compact(
            'eventos', 'eventosInscritos',
            'totalEventos', 'totalFuturos', 'totalPassados', 'totalInscricoes',
            'unidadeEgressoId'
        ));
    }

    // ============================================================
    // ✅ INSCREVER EM EVENTO
    // ============================================================
    public function inscrever($eventoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $evento = Evento::findOrFail($eventoId);

        // ✅ NOVO — Verificar se o evento é acessível ao egresso
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        $eventoEhAcessivel =
            is_null($evento->unidade_id) ||                                       // global
            ($unidadeEgressoId && $evento->unidade_id === $unidadeEgressoId);     // mesma unidade

        if (!$eventoEhAcessivel) {
            return back()->with('error', 'Este evento não está disponível para a tua unidade.');
        }

        // Já inscrito?
        $existe = InscricaoEvento::where('egresso_id', $egresso->id)
            ->where('evento_id', $evento->id)
            ->exists();

        if ($existe) {
            return back()->with('warning', 'Já está inscrito neste evento.');
        }

        // Vagas?
        if ($evento->vagas_totais) {
            $inscritos = InscricaoEvento::where('evento_id', $evento->id)->count();
            if ($inscritos >= $evento->vagas_totais) {
                return back()->with('error', 'Evento lotado.');
            }
        }

        // Prazo?
        if ($evento->data_limite_inscricao && now() > $evento->data_limite_inscricao) {
            return back()->with('error', 'O prazo de inscrição terminou.');
        }

        try {
            DB::beginTransaction();

            $inscricao = InscricaoEvento::create([
                'egresso_id'            => $egresso->id,
                'evento_id'             => $evento->id,
                'status'                => 'confirmada',
                'codigo_comprovativo'   => InscricaoEvento::gerarCodigoComprovativo(),
            ]);

            DB::commit();

            // ✅ NOTIFICAR SINO (egresso + admins)
            try {
                NotificacaoService::novaInscricaoEvento($egresso, $evento, $inscricao->id);
            } catch (\Exception $e) {
                Log::warning('Erro ao notificar inscrição: ' . $e->getMessage());
            }

            return back()->with('success', 'Inscrição confirmada!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao inscrever: ' . $e->getMessage());
            return back()->with('error', 'Erro ao inscrever no evento.');
        }
    }

    // ============================================================
    // 📄 COMPROVATIVO PDF
    // ============================================================
    public function comprovativo($inscricaoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $inscricao = InscricaoEvento::with(['evento', 'egresso.curso'])
            ->where('id', $inscricaoId)
            ->where('egresso_id', $egresso->id)
            ->firstOrFail();

        // ✅ Garantir que existe código
        if (!$inscricao->codigo_comprovativo) {
            $inscricao->update([
                'codigo_comprovativo' => InscricaoEvento::gerarCodigoComprovativo(),
            ]);
        }

        try {
            $pdf = Pdf::loadView('pdf.comprovativo', compact('inscricao'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont'          => 'DejaVu Sans',
                ]);

            $filename = 'comprovativo_' . str_pad($inscricao->id, 6, '0', STR_PAD_LEFT) . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar comprovativo: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar comprovativo.');
        }
    }

    // ============================================================
    // 📋 MINHAS INSCRIÇÕES
    // ============================================================
    public function minhasInscricoes()
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $inscricoes = InscricaoEvento::with('evento')
            ->where('egresso_id', $egresso->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('egresso.eventos.inscricoes', compact('inscricoes'));
    }

    // ============================================================
    // 👁️ VER DETALHES DE UM EVENTO
    // ============================================================
    public function show($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $evento = Evento::with('unidade')
            ->where('is_active', true)
            ->findOrFail($id);

        // ✅ NOVO — Verificar se o evento é acessível ao egresso
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        $eventoEhAcessivel =
            is_null($evento->unidade_id) ||
            ($unidadeEgressoId && $evento->unidade_id === $unidadeEgressoId);

        if (!$eventoEhAcessivel) {
            return redirect()->route('egresso.eventos')
                ->with('error', 'Este evento não está disponível para a tua unidade.');
        }

        // Verificar se o egresso já está inscrito
        $inscricao = InscricaoEvento::where('egresso_id', $egresso->id)
            ->where('evento_id', $evento->id)
            ->first();

        $jaInscrito = !is_null($inscricao);

        // Contagem de inscrições
        $totalInscritos = InscricaoEvento::where('evento_id', $evento->id)->count();

        // Regras de negócio
        $passado   = $evento->data_inicio && $evento->data_inicio->isPast();
        $lotado    = $evento->vagas_totais && $totalInscritos >= $evento->vagas_totais;
        $prazoFim  = $evento->data_limite_inscricao
            && now() > $evento->data_limite_inscricao;

        $diasAte = (!$passado && $evento->data_inicio)
            ? (int) now()->startOfDay()->diffInDays($evento->data_inicio->startOfDay(), false)
            : null;

        // Percentual de vagas
        $percentual = $evento->vagas_totais
            ? round(($totalInscritos / $evento->vagas_totais) * 100)
            : 0;

        return view('egresso.eventos.show', compact(
            'evento',
            'inscricao',
            'jaInscrito',
            'totalInscritos',
            'passado',
            'lotado',
            'prazoFim',
            'diasAte',
            'percentual',
            'unidadeEgressoId'
        ));
    }

    // ============================================================
    // ❌ CANCELAR INSCRIÇÃO
    // ============================================================
    public function cancelarInscricao($inscricaoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $inscricao = InscricaoEvento::where('id', $inscricaoId)
            ->where('egresso_id', $egresso->id)
            ->with('evento')
            ->first();

        if (!$inscricao) {
            return back()->with('error', 'Inscrição não encontrada.');
        }

        if ($inscricao->evento->data_inicio && $inscricao->evento->data_inicio->isPast()) {
            return back()->with('error', 'Não é possível cancelar inscrição de um evento já realizado.');
        }

        if (isset($inscricao->evento->permite_cancelamento) && !$inscricao->evento->permite_cancelamento) {
            return back()->with('error', 'Este evento não permite cancelamento.');
        }

        try {
            $inscricaoCompleta = $inscricao->load('egresso', 'evento');
            $inscricao->delete();

            try {
                NotificacaoService::inscricaoCancelada($inscricaoCompleta);
            } catch (\Exception $e) {
                Log::warning('Erro ao notificar cancelamento: ' . $e->getMessage());
            }

            return back()->with('success', 'Inscrição cancelada com sucesso.');

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar inscrição: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar inscrição.');
        }
    }
}