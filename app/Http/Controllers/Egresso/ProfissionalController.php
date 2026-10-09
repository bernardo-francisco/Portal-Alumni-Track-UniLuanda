<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Profissional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfissionalController extends Controller
{
    public function index()
    {
        $egresso = Auth::user()->egresso;
        $profissionais = $egresso->profissionais()->orderBy('data_inicio', 'desc')->get();
        $profissionalAtual = $egresso->profissionalAtual;

        return view('egresso.profissional.index', compact('profissionais', 'profissionalAtual'));
    }

    public function create()
    {
        return view('egresso.profissional.create');
    }

    public function store(Request $request)
    {
        $egresso = Auth::user()->egresso;

        $validated = $request->validate([
            'tipo_emprego' => 'required|string|in:full_time,part_time,freelance,self_employed,unemployed,student,unknown',
            'cargo' => 'nullable|string|max:150',
            'empregador' => 'nullable|string|max:200',
            'sector' => 'nullable|string|max:100',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'is_current' => 'nullable|boolean',
            'linkedin_url' => 'nullable|url|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Se for atual, desativar outros
            if ($request->has('is_current')) {
                Profissional::where('egresso_id', $egresso->id)->update(['is_current' => false]);
            }

            $profissional = Profissional::create([
                'egresso_id' => $egresso->id,
                'tipo_emprego' => $validated['tipo_emprego'],
                'cargo' => $validated['cargo'] ?? null,
                'empregador' => $validated['empregador'] ?? null,
                'sector' => $validated['sector'] ?? null,
                'data_inicio' => $validated['data_inicio'] ?? null,
                'data_fim' => $validated['data_fim'] ?? null,
                'is_current' => $request->has('is_current'),
                'linkedin_url' => $validated['linkedin_url'] ?? null,
            ]);

            DB::commit();

            // 🔔 NOTIFICAÇÃO: Perfil profissional atualizado
            $egresso->notificar(
                '💼 Perfil profissional atualizado',
                'Suas informações profissionais foram atualizadas com sucesso.',
                'sistema',
                route('egresso.profissional')
            );

            return redirect()->route('egresso.profissional')
                ->with('success', 'Informações profissionais salvas com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar informações: ' . $e->getMessage());
        }
    }

    public function edit(Profissional $profissional)
    {
        $egresso = Auth::user()->egresso;

        if ($profissional->egresso_id !== $egresso->id) {
            return redirect()->route('egresso.profissional')->with('error', 'Não autorizado.');
        }

        return view('egresso.profissional.edit', compact('profissional'));
    }

    public function update(Request $request, Profissional $profissional)
    {
        $egresso = Auth::user()->egresso;

        if ($profissional->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        $validated = $request->validate([
            'tipo_emprego' => 'required|string|in:full_time,part_time,freelance,self_employed,unemployed,student,unknown',
            'cargo' => 'nullable|string|max:150',
            'empregador' => 'nullable|string|max:200',
            'sector' => 'nullable|string|max:100',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'is_current' => 'nullable|boolean',
            'linkedin_url' => 'nullable|url|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Se for atual, desativar outros
            if ($request->has('is_current')) {
                Profissional::where('egresso_id', $egresso->id)
                    ->where('id', '!=', $profissional->id)
                    ->update(['is_current' => false]);
            }

            $profissional->update($validated);

            DB::commit();

            // 🔔 NOTIFICAÇÃO: Perfil profissional atualizado
            $egresso->notificar(
                '💼 Perfil profissional atualizado',
                'Suas informações profissionais foram atualizadas com sucesso.',
                'sistema',
                route('egresso.profissional')
            );

            return redirect()->route('egresso.profissional')
                ->with('success', 'Informações profissionais atualizadas com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar informações: ' . $e->getMessage());
        }
    }

    public function destroy(Profissional $profissional)
    {
        $egresso = Auth::user()->egresso;

        if ($profissional->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            $profissional->delete();

            // 🔔 NOTIFICAÇÃO: Registro profissional removido
            $egresso->notificar(
                '🗑️ Registro profissional removido',
                'Seu registro profissional foi removido do sistema.',
                'sistema',
                route('egresso.profissional')
            );

            return redirect()->route('egresso.profissional')
                ->with('success', 'Registro profissional removido com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover registro: ' . $e->getMessage());
        }
    }
}