<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmpresaOportunidadeController extends Controller
{
    /**
     * Lista de oportunidades da empresa
     */
    public function index(Request $request)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return redirect()->route('empresa.pendente');
        }

        $query = $empresa->oportunidades()->orderBy('created_at', 'desc');

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('is_active', $request->estado == 'ativa' ? 1 : 0);
        }

        // Pesquisa
        if ($request->filled('q')) {
            $query->where('titulo', 'LIKE', '%' . $request->q . '%');
        }

        $oportunidades = $query->paginate(10);

        // Total de candidaturas
        $totalCandidaturas = \App\Models\Candidatura::whereIn(
            'oportunidade_id',
            $empresa->oportunidades()->pluck('id')
        )->count();

        return view('empresa.oportunidades.index', compact(
            'empresa',
            'oportunidades',
            'totalCandidaturas'
        ));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $empresa = Auth::user()->empresa;

        return view('empresa.oportunidades.create', compact('empresa'));
    }

    /**
     * Guardar nova oportunidade
     */
    public function store(Request $request)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'Perfil de empresa não encontrado.');
        }

        // ============================================================
        // VALIDAÇÃO
        // ============================================================
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'required|string',
            'tipo' => 'required|in:emprego,estagio,bolsa,curso,evento',
            'localizacao' => 'nullable|string|max:200',
            'salario' => 'nullable|string|max:100',
            'requisitos' => 'nullable|string',
            'data_limite' => 'nullable|date|after:today',
        ]);

        // ============================================================
        // CRIAR OPORTUNIDADE
        // ============================================================
        $oportunidade = Oportunidade::create([
            'empresa_id' => $empresa->id,
            'created_by' => Auth::id(),
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'tipo' => $request->tipo,
            'empresa' => $empresa->nome,
            'localizacao' => $request->localizacao,
            'salario' => $request->salario,
            'requisitos' => $request->requisitos,
            'data_limite' => $request->data_limite,
            'is_active' => true,
        ]);

        // ============================================================
        // NOTIFICAR EGRESSOS
        // ============================================================
        $this->notificarEgressos($oportunidade);

        return redirect()
            ->route('empresa.oportunidades.index')
            ->with('success', '✅ Oportunidade publicada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return redirect()->route('empresa.pendente');
        }

        $oportunidade = Oportunidade::where('empresa_id', $empresa->id)->findOrFail($id);

        return view('empresa.oportunidades.edit', compact('oportunidade', 'empresa'));
    }

    /**
     * Actualizar oportunidade
     */
    public function update(Request $request, $id)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'Perfil de empresa não encontrado.');
        }

        $oportunidade = Oportunidade::where('empresa_id', $empresa->id)->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'required|string',
            'tipo' => 'required|in:emprego,estagio,bolsa,curso,evento',
            'localizacao' => 'nullable|string|max:200',
            'salario' => 'nullable|string|max:100',
            'requisitos' => 'nullable|string',
            'data_limite' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $oportunidade->update([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'tipo' => $request->tipo,
            'localizacao' => $request->localizacao,
            'salario' => $request->salario,
            'requisitos' => $request->requisitos,
            'data_limite' => $request->data_limite,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()
            ->route('empresa.oportunidades.index')
            ->with('success', '✅ Oportunidade actualizada com sucesso!');
    }

    /**
     * Remover oportunidade
     */
    public function destroy($id)
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            return back()->with('error', 'Perfil de empresa não encontrado.');
        }

        $oportunidade = Oportunidade::where('empresa_id', $empresa->id)->findOrFail($id);

        // Verificar se tem candidaturas
        $totalCandidaturas = \App\Models\Candidatura::where('oportunidade_id', $oportunidade->id)->count();

        if ($totalCandidaturas > 0) {
            return back()->with('error', 'Não é possível remover uma oportunidade com candidaturas. Desactive-a em vez disso.');
        }

        $oportunidade->delete();

        return redirect()
            ->route('empresa.oportunidades.index')
            ->with('success', 'Oportunidade removida com sucesso!');
    }

    /**
     * Notificar egressos sobre nova oportunidade
     */
    private function notificarEgressos(Oportunidade $oportunidade)
    {
        $egressos = User::where('tipo', 'egresso')
            ->where('is_active', true)
            ->get();

        foreach ($egressos as $user) {
            if ($user->egresso) {
                try {
                    Notificacao::create([
                        'egresso_id' => $user->egresso->id,
                        'tipo' => 'oportunidade',
                        'titulo' => '💼 Nova oportunidade disponível!',
                        'mensagem' => $oportunidade->titulo . ' - ' . $oportunidade->empresa,
                        'link' => route('egresso.oportunidades'),
                        'lida' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao notificar egresso: ' . $e->getMessage());
                }
            }
        }
    }
}