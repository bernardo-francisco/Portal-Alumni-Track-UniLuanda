<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpresaDashboardController extends Controller
{
    /**
     * Dashboard principal
     */
   public function index()
{
    $empresa = Auth::user()->empresa;

    if (!$empresa || !$empresa->isAprovada()) {
        return redirect()->route('empresa.pendente');
    }

    // ============================================================
    // ESTATÍSTICAS GERAIS
    // ============================================================
    $oportunidadeIds = $empresa->oportunidades()->pluck('id');

    $totalOportunidades = $empresa->oportunidades()->count();
    $totalCandidaturas = \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->count();
    $candidaturasPendentes = \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->where('status', 'pendente')->count();
    $candidaturasAprovadas = \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->whereIn('status', ['aprovado', 'aceite'])->count();

    // Taxa de aprovação
    $taxaAprovacao = $totalCandidaturas > 0
        ? round(($candidaturasAprovadas / $totalCandidaturas) * 100, 1)
        : 0;

    // ============================================================
    // DADOS PARA O GRÁFICO: STATUS DAS CANDIDATURAS
    // ============================================================
    $dadosStatus = [
        'pendente' => \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->where('status', 'pendente')->count(),
        'em_analise' => \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->where('status', 'em_analise')->count(),
        'entrevista' => \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->where('status', 'entrevista')->count(),
        'aprovado' => \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->whereIn('status', ['aprovado', 'aceite'])->count(),
        'rejeitado' => \App\Models\Candidatura::whereIn('oportunidade_id', $oportunidadeIds)->where('status', 'rejeitado')->count(),
    ];

    // ============================================================
    // DADOS PARA O GRÁFICO: OPORTUNIDADES POR TIPO
    // ============================================================
    $dadosTipos = [
        'emprego' => $empresa->oportunidades()->where('tipo', 'emprego')->count(),
        'estagio' => $empresa->oportunidades()->where('tipo', 'estagio')->count(),
        'bolsa' => $empresa->oportunidades()->where('tipo', 'bolsa')->count(),
        'curso' => $empresa->oportunidades()->where('tipo', 'curso')->count(),
        'evento' => $empresa->oportunidades()->where('tipo', 'evento')->count(),
    ];

    // ============================================================
    // LISTAS RECENTES
    // ============================================================
    $candidaturasRecentes = \App\Models\Candidatura::with(['egresso', 'oportunidade'])
        ->whereIn('oportunidade_id', $oportunidadeIds)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    $oportunidadesRecentes = $empresa->oportunidades()
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    return view('empresa.dashboard', compact(
        'empresa',
        'totalOportunidades',
        'totalCandidaturas',
        'candidaturasPendentes',
        'candidaturasAprovadas',
        'taxaAprovacao',
        'dadosStatus',
        'dadosTipos',
        'candidaturasRecentes',
        'oportunidadesRecentes'
    ));
}

    /**
     * Página de empresa pendente de validação
     */
    public function pendente()
    {
        $empresa = Auth::user()->empresa;
        return view('empresa.pendente', compact('empresa'));
    }

    /**
     * Perfil da empresa
     */
    public function perfil()
    {
        $empresa = Auth::user()->empresa;
        return view('empresa.perfil', compact('empresa'));
    }

    /**
     * Actualizar perfil
     */
    public function actualizarPerfil(Request $request)
    {
        $empresa = Auth::user()->empresa;

        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'telefone' => 'nullable|string|max:25',
            'website' => 'nullable|url|max:255',
            'sector' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'localizacao' => 'nullable|string|max:200',
            'provincia' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload logo
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logo = $request->file('logo');
            $nomeLogo = 'empresa_' . uniqid() . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/empresas'), $nomeLogo);
            $empresa->logo_url = 'uploads/empresas/' . $nomeLogo;
        }

        $empresa->update([
            'nome' => $request->nome,
            'telefone' => $request->telefone,
            'website' => $request->website,
            'sector' => $request->sector,
            'descricao' => $request->descricao,
            'localizacao' => $request->localizacao,
            'provincia' => $request->provincia,
            'logo_url' => $empresa->logo_url,
        ]);

        return redirect()->route('empresa.perfil')->with('success', 'Perfil actualizado com sucesso!');
    }
}