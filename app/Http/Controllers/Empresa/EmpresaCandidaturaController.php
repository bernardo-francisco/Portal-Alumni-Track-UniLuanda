<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Candidatura;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaCandidaturaController extends Controller
{
    /**
     * Lista de candidaturas com filtros
     */
    public function index(Request $request)
    {
        $empresa = Auth::user()->empresa;

        // IDs das oportunidades da empresa
        $oportunidadeIds = $empresa->oportunidades()->pluck('id');

        // Query base
        $query = Candidatura::with(['egresso', 'oportunidade'])
            ->whereIn('oportunidade_id', $oportunidadeIds);

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por oportunidade
        if ($request->filled('oportunidade')) {
            $query->where('oportunidade_id', $request->oportunidade);
        }

        // Filtro por pesquisa (nome do egresso)
        if ($request->filled('q')) {
            $query->whereHas('egresso', function($q) use ($request) {
                $q->where('nome_completo', 'LIKE', '%' . $request->q . '%');
            });
        }

        $candidaturas = $query->orderBy('created_at', 'desc')->paginate(15);

        // Lista de oportunidades para o filtro
        $oportunidades = $empresa->oportunidades()->orderBy('titulo')->get();

        return view('empresa.candidaturas.index', compact(
            'empresa',
            'candidaturas',
            'oportunidades'
        ));
    }

    /**
     * Detalhes de uma candidatura
     */
    public function show($id)
    {
        $empresa = Auth::user()->empresa;
        $oportunidadeIds = $empresa->oportunidades()->pluck('id');

        $candidatura = Candidatura::with(['egresso', 'oportunidade'])
            ->whereIn('oportunidade_id', $oportunidadeIds)
            ->findOrFail($id);

        return view('empresa.candidaturas.show', compact('candidatura'));
    }

    /**
     * Actualizar o estado da candidatura
     */
    public function actualizarStatus(Request $request, $id)
    {
        $empresa = Auth::user()->empresa;
        $oportunidadeIds = $empresa->oportunidades()->pluck('id');

        $candidatura = Candidatura::whereIn('oportunidade_id', $oportunidadeIds)
            ->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pendente,em_analise,entrevista,aprovado,aceite,rejeitado',
            'data_entrevista' => 'nullable|date',
            'local_entrevista' => 'nullable|string|max:255',
            'motivo_rejeicao' => 'nullable|string',
        ]);

        $candidatura->update([
            'status' => $request->status,
            'data_entrevista' => $request->data_entrevista,
            'local_entrevista' => $request->local_entrevista,
            'motivo_rejeicao' => $request->motivo_rejeicao,
            'avaliado_em' => now(),
        ]);

        // Notificar egresso
        $this->notificarEgresso($candidatura);

        return redirect()
            ->route('empresa.candidaturas.show', $candidatura->id)
            ->with('success', 'Estado da candidatura actualizado com sucesso!');
    }

    /**
     * Notificar o egresso sobre a actualização
     */
    private function notificarEgresso(Candidatura $candidatura)
    {
        $titulo = match($candidatura->status) {
            'entrevista' => '📅 Entrevista marcada!',
            'aprovado' => '🎉 Candidatura aprovada!',
            'aceite' => '✅ Candidatura aceite!',
            'rejeitado' => '❌ Candidatura não aceite',
            'em_analise' => '🔍 Candidatura em análise',
            default => '📋 Candidatura actualizada',
        };

        Notificacao::create([
            'egresso_id' => $candidatura->egresso_id,
            'tipo' => 'oportunidade',
            'titulo' => $titulo,
            'mensagem' => 'A tua candidatura a \'' . ($candidatura->oportunidade->titulo ?? '') . '\' foi actualizada.',
            'link' => '/egresso/minhas-candidaturas',
            'lida' => false,
        ]);
    }
}