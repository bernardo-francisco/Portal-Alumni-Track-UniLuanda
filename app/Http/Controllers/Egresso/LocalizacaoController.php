<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Localizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocalizacaoController extends Controller
{
    /**
     * Display a listing of the user's locations with filters.
     */
    public function index(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $query = $egresso->localizacoes();

        // ============================================================
        // FILTROS
        // ============================================================

        // 🔍 Filtro por País
        if ($request->filled('pais')) {
            $query->where('pais', 'LIKE', '%' . $request->pais . '%');
        }

        // 🔍 Filtro por Cidade
        if ($request->filled('cidade')) {
            $query->where('cidade', 'LIKE', '%' . $request->cidade . '%');
        }

        // 🔍 Filtro por Status (Atual/Anterior)
        if ($request->filled('status')) {
            if ($request->status == 'current') {
                $query->where('is_current', true);
            } elseif ($request->status == 'past') {
                $query->where('is_current', false);
            }
        }

        // 🔍 Filtro por Período
        if ($request->filled('periodo')) {
            $periodo = $request->periodo;
            $now = now();
            
            if ($periodo == '7d') {
                $query->where('data_desde', '>=', $now->copy()->subDays(7));
            } elseif ($periodo == '30d') {
                $query->where('data_desde', '>=', $now->copy()->subDays(30));
            } elseif ($periodo == '90d') {
                $query->where('data_desde', '>=', $now->copy()->subDays(90));
            } elseif ($periodo == '2024') {
                $query->whereYear('data_desde', 2024);
            } elseif ($periodo == '2025') {
                $query->whereYear('data_desde', 2025);
            }
        }

        // ============================================================
        // RESULTADOS
        // ============================================================

        $localizacoes = $query->orderBy('is_current', 'desc')
                              ->orderBy('data_desde', 'desc')
                              ->get();

        $localizacaoAtual = $egresso->localizacaoAtual;

        return view('egresso.localizacao.index', compact('localizacoes', 'localizacaoAtual'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create()
    {
        return view('egresso.localizacao.create');
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $validated = $request->validate([
            'pais' => 'required|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'data_desde' => 'nullable|date',
            'is_current' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Se for atual, desativar outros
            if ($request->has('is_current')) {
                Localizacao::where('egresso_id', $egresso->id)->update(['is_current' => false]);
            }

            $localizacao = Localizacao::create([
                'egresso_id' => $egresso->id,
                'pais' => $validated['pais'],
                'provincia' => $validated['provincia'] ?? null,
                'cidade' => $validated['cidade'] ?? null,
                'endereco' => $validated['endereco'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'data_desde' => $validated['data_desde'] ?? now(),
                'is_current' => $request->has('is_current'),
            ]);

            DB::commit();

            // 🔔 NOTIFICAÇÃO: Localização atualizada
            $egresso->notificar(
                '📍 Localização atualizada',
                'Sua localização foi atualizada para ' . $validated['pais'] . ($validated['cidade'] ? ', ' . $validated['cidade'] : ''),
                'sistema',
                route('egresso.localizacao')
            );

            return redirect()->route('egresso.localizacao')
                ->with('success', 'Localização salva com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar localização: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Localizacao $localizacao)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        if ($localizacao->egresso_id !== $egresso->id) {
            return redirect()->route('egresso.localizacao')->with('error', 'Não autorizado.');
        }

        return view('egresso.localizacao.edit', compact('localizacao'));
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Localizacao $localizacao)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        if ($localizacao->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        $validated = $request->validate([
            'pais' => 'required|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'data_desde' => 'nullable|date',
            'is_current' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Se for atual, desativar outros
            if ($request->has('is_current')) {
                Localizacao::where('egresso_id', $egresso->id)
                    ->where('id', '!=', $localizacao->id)
                    ->update(['is_current' => false]);
            }

            $localizacao->update($validated);

            DB::commit();

            // 🔔 NOTIFICAÇÃO: Localização atualizada
            $egresso->notificar(
                '📍 Localização atualizada',
                'Sua localização foi atualizada para ' . $validated['pais'] . ($validated['cidade'] ? ', ' . $validated['cidade'] : ''),
                'sistema',
                route('egresso.localizacao')
            );

            return redirect()->route('egresso.localizacao')
                ->with('success', 'Localização atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar localização: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Localizacao $localizacao)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        if ($localizacao->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            $localizacao->delete();

            // 🔔 NOTIFICAÇÃO: Localização removida
            $egresso->notificar(
                '🗑️ Localização removida',
                'Sua localização foi removida do sistema.',
                'sistema',
                route('egresso.localizacao')
            );

            return redirect()->route('egresso.localizacao')
                ->with('success', 'Localização removida com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover localização: ' . $e->getMessage());
        }
    }
}