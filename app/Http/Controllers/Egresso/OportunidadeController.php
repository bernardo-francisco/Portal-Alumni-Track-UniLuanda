<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OportunidadeController extends Controller
{
    // ============================================================
    // 📋 LISTAR OPORTUNIDADES (com filtros)
    // ============================================================
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $egresso = $usuario->egresso;

        if (!$egresso) {
            return redirect()->back()
                ->with('error', 'Perfil de egresso não encontrado.');
        }

        // ✅ NOVO — Obter a unidade do egresso (via curso)
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        // --------------------------------------------------------
        // 🔍 QUERY BASE — oportunidades visíveis para este egresso
        // --------------------------------------------------------
        $query = Oportunidade::with('unidade')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('data_limite')
                  ->orWhere('data_limite', '>=', now());
            })
            // ✅ NOVO — Filtrar por unidade
            ->where(function ($q) use ($unidadeEgressoId) {
                $q->whereNull('unidade_id');
                if ($unidadeEgressoId) {
                    $q->orWhere('unidade_id', $unidadeEgressoId);
                }
            });

        // --------------------------------------------------------
        // 🔍 FILTRO: Pesquisa (título, descrição, empresa)
        // --------------------------------------------------------
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhere('empresa', 'like', "%{$search}%");
            });
        }

        // --------------------------------------------------------
        // 🔍 FILTRO: Tipo
        // --------------------------------------------------------
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // --------------------------------------------------------
        // 🔍 FILTRO: Empresa
        // --------------------------------------------------------
        if ($request->filled('empresa')) {
            $query->where('empresa', 'like', "%{$request->empresa}%");
        }

        // ✅ NOVO — Filtro de âmbito
        if ($request->filled('ambito') && $unidadeEgressoId) {
            if ($request->ambito === 'minha') {
                $query->where('unidade_id', $unidadeEgressoId);
            } elseif ($request->ambito === 'global') {
                $query->whereNull('unidade_id');
            }
        }

        // --------------------------------------------------------
        // 📊 ORDENAÇÃO
        // --------------------------------------------------------
        $query->orderBy('created_at', 'desc');

        // --------------------------------------------------------
        // 📄 PAGINAÇÃO (mantém os filtros na URL)
        // --------------------------------------------------------
        $oportunidades = $query->paginate(10)->appends($request->query());

        // --------------------------------------------------------
        // 🎯 CANDIDATURAS DO EGRESSO
        // --------------------------------------------------------
        $candidaturas = Candidatura::where('egresso_id', $egresso->id)
            ->pluck('oportunidade_id')
            ->toArray();

        // --------------------------------------------------------
        // 📊 ESTATÍSTICAS
        // --------------------------------------------------------
        $totalOportunidades = (clone $query)->count();  // ← usa a mesma query filtrada

        $totalCandidaturas = count($candidaturas);

        // --------------------------------------------------------
        // 📤 RETORNAR VIEW
        // --------------------------------------------------------
        return view('egresso.oportunidades.index', compact(
            'oportunidades',
            'candidaturas',
            'totalOportunidades',
            'totalCandidaturas',
            'unidadeEgressoId'    // ✅ NOVO
        ));
    }

    // ============================================================
    // 🎯 CANDIDATAR-SE
    // ============================================================
    public function candidatar(Request $request, $oportunidadeId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        // ✅ NOVO — Verificar se a oportunidade é acessível ao egresso
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        $oportunidadeEhAcessivel =
            is_null($oportunidade->unidade_id) ||                                       // global
            ($unidadeEgressoId && $oportunidade->unidade_id === $unidadeEgressoId);     // mesma unidade

        if (!$oportunidadeEhAcessivel) {
            return back()->with('error', 'Esta oportunidade não está disponível para a tua unidade.');
        }

        // Verificar se já se candidatou
        $existe = Candidatura::where('egresso_id', $egresso->id)
            ->where('oportunidade_id', $oportunidade->id)
            ->exists();

        if ($existe) {
            return back()->with('warning', 'Já se candidatou a esta oportunidade.');
        }

        // Verificar prazo
        if ($oportunidade->data_limite && now() > $oportunidade->data_limite) {
            return back()->with('error', 'O prazo de candidatura já terminou.');
        }

        $request->validate([
            'mensagem_motivacional' => 'nullable|string|max:1000',
            'cv_anexo'              => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $cvPath = null;

            if ($request->hasFile('cv_anexo')) {
                $file     = $request->file('cv_anexo');
                $filename = 'cv_' . $egresso->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $cvPath   = $file->storeAs('candidaturas/cv', $filename, 'public');
            }

            $candidatura = Candidatura::create([
                'egresso_id'            => $egresso->id,
                'oportunidade_id'       => $oportunidade->id,
                'status'                => 'pendente',
                'mensagem_motivacional' => $request->mensagem_motivacional,
                'cv_anexo'              => $cvPath,
            ]);

            DB::commit();

            // ✅ Notifica admins + empresa (NovaCandidaturaMail) + egresso (ConfirmacaoCandidaturaMail)
            NotificacaoService::novaCandidatura($egresso, $oportunidade, $candidatura->id);

            return back()->with('success', 'Candidatura enviada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao candidatar: ' . $e->getMessage());
            return back()->with('error', 'Erro ao enviar candidatura.');
        }
    }

    // ============================================================
    // 📋 MINHAS CANDIDATURAS
    // ============================================================
    public function minhasCandidaturas()
    {
        $usuario = Auth::user();
        $egresso = $usuario->egresso;

        if (!$egresso) {
            return redirect()->back()
                ->with('error', 'Perfil de egresso não encontrado.');
        }

        $candidaturas = Candidatura::with([
                'oportunidade' => function ($query) {
                    $query->with('unidade');
                }
            ])
            ->where('egresso_id', $egresso->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('egresso.oportunidades.candidaturas', compact('candidaturas'));
    }

    // ============================================================
    // 👁️ VER DETALHES DE UMA OPORTUNIDADE
    // ============================================================
    public function show($id)
    {
        $usuario = Auth::user();
        $egresso = $usuario->egresso;

        if (!$egresso) {
            return redirect()->back()
                ->with('error', 'Perfil de egresso não encontrado.');
        }

        $oportunidade = Oportunidade::with(['unidade'])
            ->where('is_active', true)
            ->findOrFail($id);

        // ✅ NOVO — Verificar se a oportunidade é acessível ao egresso
        $unidadeEgressoId = $egresso->curso?->unidade_id;

        $oportunidadeEhAcessivel =
            is_null($oportunidade->unidade_id) ||
            ($unidadeEgressoId && $oportunidade->unidade_id === $unidadeEgressoId);

        if (!$oportunidadeEhAcessivel) {
            return redirect()->route('egresso.oportunidades')
                ->with('error', 'Esta oportunidade não está disponível para a tua unidade.');
        }

        // Verificar se o egresso já se candidatou
        $jaCandidatou = Candidatura::where('egresso_id', $egresso->id)
            ->where('oportunidade_id', $oportunidade->id)
            ->exists();

        // Contagem de candidaturas
        $totalCandidaturas = Candidatura::where('oportunidade_id', $oportunidade->id)->count();

        // Prazo
        $expirado = $oportunidade->data_limite
            && \Carbon\Carbon::parse($oportunidade->data_limite)->isPast();

        $diasAte = (!$expirado && $oportunidade->data_limite)
            ? now()->diffInDays(\Carbon\Carbon::parse($oportunidade->data_limite), false)
            : null;

        return view('egresso.oportunidades.show', compact(
            'oportunidade',
            'jaCandidatou',
            'totalCandidaturas',
            'expirado',
            'diasAte',
            'unidadeEgressoId'    // ✅ NOVO
        ));
    }

    // ============================================================
    // ❌ CANCELAR CANDIDATURA
    // ============================================================
    public function cancelarCandidatura($candidaturaId)
    {
        $usuario = Auth::user();
        $egresso = $usuario->egresso;

        if (!$egresso) {
            return back()->with('error', 'Perfil de egresso não encontrado.');
        }

        $candidatura = Candidatura::where('id', $candidaturaId)
            ->where('egresso_id', $egresso->id)
            ->where('status', 'pendente')
            ->first();

        if (!$candidatura) {
            return back()->with('error', 'Candidatura não encontrada ou não pode ser cancelada.');
        }

        try {
            $candidatura->delete();

            return back()->with('success', 'Candidatura cancelada com sucesso.');

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar candidatura.', [
                'candidatura_id' => $candidaturaId,
                'egresso_id'     => $egresso->id,
                'erro'           => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao cancelar a candidatura. Tente novamente.');
        }
    }
}