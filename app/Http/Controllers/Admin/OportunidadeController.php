<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use App\Models\UnidadeOrganica;
use App\Models\Egresso;
use Illuminate\Http\Request;

class OportunidadeController extends Controller
{
    /**
     * Display a listing of the opportunities.
     */
    public function index(Request $request)
    {
        $query = Oportunidade::with('unidade')
                             ->withCount('candidaturas');

        // ============================================================
        // FILTROS
        // ============================================================

        // 🔍 Busca por título, empresa ou descrição
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('empresa', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        // 🔍 Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // 🔍 Filtro por unidade
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        // 🔍 Filtro por status (ativa/inativa)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // 🔍 Filtro por data_limite (com prazo)
        if ($request->filled('prazo')) {
            if ($request->prazo == 'com_prazo') {
                $query->whereNotNull('data_limite')
                      ->where('data_limite', '>=', now());
            } elseif ($request->prazo == 'sem_prazo') {
                $query->whereNull('data_limite');
            } elseif ($request->prazo == 'expirado') {
                $query->where('data_limite', '<', now());
            }
        }

        // ============================================================
        // ORDENAÇÃO
        // ============================================================

        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');

        $allowedOrderFields = ['titulo', 'tipo', 'empresa', 'created_at', 'data_limite'];
        if (in_array($orderBy, $allowedOrderFields)) {
            $query->orderBy($orderBy, $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // ============================================================
        // RESULTADOS
        // ============================================================

        $oportunidades = $query->get();
        $unidades = UnidadeOrganica::orderBy('nome')->get();

        return view('admin.oportunidades.index', compact('oportunidades', 'unidades'));
    }

    /**
     * Show the form for creating a new opportunity.
     */
    public function create()
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.oportunidades.create', compact('unidades'));
    }

    /**
     * Store a newly created opportunity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'required|string',
            'tipo' => 'required|in:emprego,estagio,bolsa,curso,evento',
            'empresa' => 'nullable|string|max:200',
            'localizacao' => 'nullable|string|max:200',
            'salario' => 'nullable|string|max:100',
            'requisitos' => 'nullable|string',
            'data_limite' => 'nullable|date|after:today',
            'unidade_id' => 'nullable|exists:unidades_organicas,id',
            'is_active' => 'boolean',
        ]);

        try {
            $validated['created_by'] = Auth::id();
            $oportunidade = Oportunidade::create($validated);

            // 🔔 NOTIFICAÇÃO: Notificar todos os egressos ativos
            $egressos = Egresso::where('status', 'active')->get();
            foreach ($egressos as $egresso) {
                $egresso->notificar(
                    'Nova oportunidade disponível!',
                    $oportunidade->titulo . ' - ' . ($oportunidade->empresa ?? 'Oportunidade'),
                    'oportunidade',
                    route('egresso.oportunidades')
                );
            }

            return redirect()->route('admin.oportunidades.index')
                           ->with('success', 'Oportunidade criada e egressos notificados com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao criar oportunidade: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified opportunity.
     */
    public function show(Oportunidade $oportunidade)
    {
        $oportunidade->load(['unidade', 'candidaturas.egresso']);
        return view('admin.oportunidades.show', compact('oportunidade'));
    }

    /**
     * Show the form for editing the specified opportunity.
     */
    public function edit(Oportunidade $oportunidade)
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        return view('admin.oportunidades.edit', compact('oportunidade', 'unidades'));
    }

    /**
     * Update the specified opportunity in storage.
     */
    public function update(Request $request, Oportunidade $oportunidade)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descricao' => 'required|string',
            'tipo' => 'required|in:emprego,estagio,bolsa,curso,evento',
            'empresa' => 'nullable|string|max:200',
            'localizacao' => 'nullable|string|max:200',
            'salario' => 'nullable|string|max:100',
            'requisitos' => 'nullable|string',
            'data_limite' => 'nullable|date|after:today',
            'unidade_id' => 'nullable|exists:unidades_organicas,id',
            'is_active' => 'boolean',
        ]);

        try {
            $oportunidade->update($validated);

            // 🔔 NOTIFICAÇÃO: Notificar todos os egressos ativos sobre a atualização
            $egressos = Egresso::where('status', 'active')->get();
            foreach ($egressos as $egresso) {
                $egresso->notificar(
                    'Oportunidade atualizada: ' . $oportunidade->titulo,
                    'A oportunidade foi atualizada. Confira as novidades.',
                    'oportunidade',
                    route('egresso.oportunidades')
                );
            }

            return redirect()->route('admin.oportunidades.index')
                           ->with('success', 'Oportunidade actualizada com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao actualizar oportunidade: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified opportunity from storage.
     */
    public function destroy(Oportunidade $oportunidade)
    {
        try {
            $oportunidade->delete();

            // 🔔 NOTIFICAÇÃO: Notificar todos os egressos ativos sobre a remoção
            $egressos = Egresso::where('status', 'active')->get();
            foreach ($egressos as $egresso) {
                $egresso->notificar(
                    'Oportunidade removida: ' . $oportunidade->titulo,
                    'A oportunidade foi removida do sistema.',
                    'oportunidade',
                    route('egresso.oportunidades')
                );
            }

            return redirect()->route('admin.oportunidades.index')
                           ->with('success', 'Oportunidade removida com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover oportunidade: ' . $e->getMessage());
        }
    }
}