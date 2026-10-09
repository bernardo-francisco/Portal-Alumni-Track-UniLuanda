<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\UnidadeOrganica;
use App\Models\Egresso;
use App\Models\InscricaoEvento;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventoController extends Controller
{
    // ============================================================
    // 📋 LISTA DE EVENTOS
    // ============================================================
    public function index(Request $request)
    {
        $query = Evento::with('unidade')->withCount('inscricoes');

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

        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('periodo')) {
            $now = now();
            if ($request->periodo == 'futuros') {
                $query->where('data_inicio', '>=', $now);
            } elseif ($request->periodo == 'passados') {
                $query->where('data_inicio', '<', $now);
            }
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_fim', '<=', $request->data_fim);
        }

        $orderBy = $request->get('order_by', 'data_inicio');
        $orderDir = $request->get('order_dir', 'desc');

        $allowedOrderFields = ['titulo', 'tipo', 'data_inicio', 'created_at'];
        if (in_array($orderBy, $allowedOrderFields)) {
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->orderBy('data_inicio', 'desc');
        }

        $eventos = $query->get();
        $unidades = UnidadeOrganica::orderBy('nome')->get();

        return view('admin.eventos.index', compact('eventos', 'unidades'));
    }

    // ============================================================
    // ➕ FORMULÁRIO CRIAR
    // ============================================================
    public function create()
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.eventos.create', compact('unidades'));
    }

    // ============================================================
    // 💾 GUARDAR NOVO EVENTO
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'tipo' => 'required|in:presencial,online,hibrido',
            'categoria' => 'required|in:workshop,palestra,networking,job_fair,curso,outro',
            'data_inicio' => 'required|date|after:now',
            'data_fim' => 'nullable|date|after:data_inicio',
            'local' => 'nullable|string|max:200',
            'link_reuniao' => 'nullable|url|max:255',
            'max_participantes' => 'nullable|integer|min:1',
            'unidade_id' => 'nullable|exists:unidades_organicas,id',
            'is_active' => 'boolean',
        ]);

        try {
            $validated['created_by'] = Auth::id();
            $evento = Evento::create($validated);

            return redirect()->route('admin.eventos.index')
                           ->with('success', 'Evento criado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar evento: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 👁️ MOSTRAR EVENTO
    // ============================================================
    public function show(Evento $evento)
    {
        $evento->load(['unidade', 'inscricoes.egresso']);
        return view('admin.eventos.show', compact('evento'));
    }

    // ============================================================
    // ✏️ FORMULÁRIO EDITAR
    // ============================================================
    public function edit(Evento $evento)
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.eventos.edit', compact('evento', 'unidades'));
    }

    // ============================================================
    // 💾 ACTUALIZAR EVENTO
    // ============================================================
    public function update(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'tipo' => 'required|in:presencial,online,hibrido',
            'categoria' => 'required|in:workshop,palestra,networking,job_fair,curso,outro',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'local' => 'nullable|string|max:200',
            'link_reuniao' => 'nullable|url|max:255',
            'max_participantes' => 'nullable|integer|min:1',
            'unidade_id' => 'nullable|exists:unidades_organicas,id',
            'is_active' => 'boolean',
        ]);

        try {
            $evento->update($validated);

            return redirect()->route('admin.eventos.index')
                           ->with('success', 'Evento actualizado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao actualizar evento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao actualizar evento: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🗑️ ELIMINAR EVENTO
    // ============================================================
    public function destroy(Evento $evento)
    {
        try {
            $evento->delete();

            return redirect()->route('admin.eventos.index')
                           ->with('success', 'Evento removido com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao remover evento: ' . $e->getMessage());
            return back()->with('error', 'Erro ao remover evento: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 👥 INSCRITOS EM EVENTO
    // ============================================================
    public function inscritos($eventoId)
    {
        $evento = Evento::with(['inscricoes.egresso'])
            ->findOrFail($eventoId);

        $stats = [
            'total'     => $evento->inscricoes->count(),
            'presentes' => $evento->inscricoes->where('presente', true)->count(),
            'ausentes'  => $evento->inscricoes->where('presente', false)->count(),
        ];

        return view('admin.eventos.inscritos', compact('evento', 'stats'));
    }

    // ============================================================
    // ✅ MARCAR PRESENÇA
    // ============================================================
    public function marcarPresenca(Request $request, $inscricaoId)
    {
        $request->validate([
            'presente' => 'required|boolean',
        ]);

        $inscricao = InscricaoEvento::findOrFail($inscricaoId);

        try {
            DB::beginTransaction();

            $inscricao->update([
                'presente'   => $request->presente,
                'checkin_em' => now(),
            ]);

            DB::commit();

            NotificacaoService::presencaMarcada($inscricao, (bool) $request->presente);

            return back()->with('success', 'Presença registada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao marcar presença: ' . $e->getMessage());
            return back()->with('error', 'Erro ao registar presença.');
        }
    }

    // ============================================================
    // 📥 EXPORTAR INSCRITOS (CSV)
    // ============================================================
    public function exportarInscritos($eventoId)
    {
        $evento = Evento::with(['inscricoes.egresso.curso'])->findOrFail($eventoId);

        $filename = 'inscritos_' . Str::slug($evento->titulo) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($evento) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'Nome',
                'Email',
                'Telefone',
                'Curso',
                'Inscrição',
                'Presente',
                'Check-in',
            ], ';');

            foreach ($evento->inscricoes as $inscricao) {
                $egresso = $inscricao->egresso;

                fputcsv($file, [
                    $egresso?->nome_completo ?? 'N/A',
                    $egresso?->email ?? 'N/A',
                    $egresso?->telefone ?? 'N/A',
                    $egresso?->curso?->nome ?? 'N/A',
                    $inscricao->created_at->format('d/m/Y H:i'),
                    $inscricao->presente ? 'Sim' : 'Não',
                    $inscricao->checkin_em ? $inscricao->checkin_em->format('d/m/Y H:i') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    // ============================================================
// ✅ APROVAR INSCRIÇÃO
// ============================================================
public function aprovarInscricao($inscricaoId)
{
    $inscricao = InscricaoEvento::with(['egresso', 'evento'])->findOrFail($inscricaoId);

    if ($inscricao->status === 'confirmada') {
        return back()->with('warning', 'Esta inscrição já está confirmada.');
    }

    try {
        DB::beginTransaction();

        // ✅ Gera código de comprovativo apenas na aprovação
        $inscricao->update([
            'status'               => 'confirmada',
            'codigo_comprovativo'  => InscricaoEvento::gerarCodigoComprovativo(),
        ]);

        DB::commit();

        // Notificar egresso
        try {
            NotificacaoService::inscricaoAprovada($inscricao);
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar aprovação: ' . $e->getMessage());
        }

        return back()->with('success', 'Inscrição aprovada com sucesso.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao aprovar inscrição: ' . $e->getMessage());
        return back()->with('error', 'Erro ao aprovar inscrição.');
    }
}

// ============================================================
// ❌ REJEITAR INSCRIÇÃO
// ============================================================
public function rejeitarInscricao(Request $request, $inscricaoId)
{
    $request->validate([
        'motivo_rejeicao' => 'nullable|string|max:500',
    ]);

    $inscricao = InscricaoEvento::with(['egresso', 'evento'])->findOrFail($inscricaoId);

    if ($inscricao->status === 'rejeitada') {
        return back()->with('warning', 'Esta inscrição já foi rejeitada.');
    }

    try {
        DB::beginTransaction();

        $inscricao->update([
            'status'         => 'rejeitada',
            'motivo_rejeicao' => $request->motivo_rejeicao,
        ]);

        DB::commit();

        // Notificar egresso
        try {
            NotificacaoService::inscricaoRejeitada($inscricao);
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar rejeição: ' . $e->getMessage());
        }

        return back()->with('success', 'Inscrição rejeitada.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao rejeitar inscrição: ' . $e->getMessage());
        return back()->with('error', 'Erro ao rejeitar inscrição.');
    }
}
}