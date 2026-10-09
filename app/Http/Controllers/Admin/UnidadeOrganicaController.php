<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadeOrganica;
use Illuminate\Http\Request;

class UnidadeOrganicaController extends Controller
{
    public function index()
    {
        $unidades = UnidadeOrganica::withCount('cursos')->get();
        return view('admin.unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('admin.unidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sigla' => 'required|string|max:20|unique:unidades_organicas',
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
        ]);

        UnidadeOrganica::create($validated);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidade criada com sucesso!');
    }

    public function edit(UnidadeOrganica $unidade)
    {
        return view('admin.unidades.edit', compact('unidade'));
    }

    public function update(Request $request, UnidadeOrganica $unidade)
    {
        $validated = $request->validate([
            'sigla' => 'required|string|max:20|unique:unidades_organicas,sigla,' . $unidade->id,
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
        ]);

        $unidade->update($validated);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidade atualizada com sucesso!');
    }

    public function destroy(UnidadeOrganica $unidade)
    {
        $unidade->delete();
        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidade removida com sucesso!');
    }
}