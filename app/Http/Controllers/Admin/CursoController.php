<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        // ============================================
        // QUERY BASE
        // ============================================
        $query = Curso::with('unidade')->withCount('egressos');

        // ============================================
        // FILTROS CORRIGIDOS
        // ============================================
        
        // Busca por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('departamento', 'LIKE', "%{$search}%");
            });
        }

        // 🔥 FILTRO POR UNIDADE (CORRIGIDO)
        if ($request->filled('unidade_id') && $request->unidade_id != '') {
            $query->where('unidade_id', $request->unidade_id);
        }

        // Filtro por Departamento
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        // ============================================
        // ORDENAÇÃO
        // ============================================
        $query->orderBy('nome');

        // ============================================
        // RESULTADOS
        // ============================================
        $cursos = $query->get();
        
        // Para os filtros (unidades e departamentos)
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        
        // 🔥 LISTA DE DEPARTAMENTOS DINÂMICA (considerando o filtro)
        $departamentos = Curso::whereNotNull('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');

        // ============================================
        // DEBUG (REMOVER EM PRODUÇÃO)
        // ============================================
        // Se estiver em desenvolvimento, descomente para debug:
        // if ($request->filled('unidade_id')) {
        //     \Log::info('Filtrando por unidade_id:', [
        //         'unidade_id' => $request->unidade_id,
        //         'cursos_encontrados' => $query->count()
        //     ]);
        // }

        return view('admin.cursos.index', compact('cursos', 'unidades', 'departamentos'));
    }

    public function create()
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.cursos.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unidade_id' => 'required|exists:unidades_organicas,id',
            'nome' => 'required|string|max:150',
            'codigo' => 'required|string|max:20|unique:cursos',
            'departamento' => 'nullable|string|max:100',
            'duracao' => 'nullable|integer|min:1|max:10',
        ]);

        Curso::create($validated);

        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso criado com sucesso!');
    }

    public function edit(Curso $curso)
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.cursos.edit', compact('curso', 'unidades'));
    }

    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'unidade_id' => 'required|exists:unidades_organicas,id',
            'nome' => 'required|string|max:150',
            'codigo' => 'required|string|max:20|unique:cursos,codigo,' . $curso->id,
            'departamento' => 'nullable|string|max:100',
            'duracao' => 'nullable|integer|min:1|max:10',
        ]);

        $curso->update($validated);

        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Curso $curso)
    {
        // Verificar se há egressos vinculados
        if ($curso->egressos()->count() > 0) {
            return back()->with('error', 'Não é possível excluir um curso com egressos vinculados.');
        }

        $curso->delete();
        return redirect()->route('admin.cursos.index')
            ->with('success', 'Curso removido com sucesso!');
    }
}