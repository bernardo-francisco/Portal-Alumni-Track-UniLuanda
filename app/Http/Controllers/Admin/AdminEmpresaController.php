<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Notificacao;
use App\Mail\EmpresaValidadaMail;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminEmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::with('user');

        if ($request->filled('status')) {
            $query->where('status_validacao', $request->status);
        }

        $empresas = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.empresas.index', compact('empresas'));
    }

    public function show($id)
    {
        $empresa = Empresa::with('user')->findOrFail($id);
        return view('admin.empresas.show', compact('empresa'));
    }

    public function aprovar($id)
    {
        $empresa = Empresa::findOrFail($id);

        $empresa->update([
            'status_validacao' => 'aprovado',
            'data_validacao'   => now(),
        ]);

        // Ativar o user associado
        if ($empresa->user) {
            $empresa->user->update(['is_active' => true]);
        }

        // ✅ 1. Notificação no SINO da empresa
        try {
            NotificacaoService::criarParaEmpresa(
                $empresa->id,
                '✅ Empresa aprovada!',
                'A sua empresa foi aprovada. Já pode aceder ao dashboard e publicar oportunidades.',
                'sistema',
                '/empresa/dashboard'
            );
        } catch (\Exception $e) {
            Log::warning('Erro ao criar notificação para empresa: ' . $e->getMessage());
        }

        // ✅ 2. Enviar EMAIL
        try {
            $emailDestino = $empresa->email ?? $empresa->user?->email;
            if ($emailDestino) {
                Mail::to($emailDestino)->send(new EmpresaValidadaMail($empresa, true));
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao enviar email de aprovação: ' . $e->getMessage());
        }

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa aprovada com sucesso!');
    }

    public function reprovar(Request $request, $id)
    {
        $request->validate([
            'motivo_reprovacao' => 'required|string|max:500',
        ]);

        $empresa = Empresa::findOrFail($id);

        $empresa->update([
            'status_validacao'  => 'reprovado',
            'motivo_reprovacao' => $request->motivo_reprovacao,
            'data_validacao'    => now(),
        ]);

        // ✅ 1. Notificação no SINO da empresa
        try {
            NotificacaoService::criarParaEmpresa(
                $empresa->id,
                '❌ Empresa não aprovada',
                'A sua empresa não foi aprovada. Motivo: ' . $request->motivo_reprovacao,
                'sistema',
                null
            );
        } catch (\Exception $e) {
            Log::warning('Erro ao criar notificação para empresa: ' . $e->getMessage());
        }

        // ✅ 2. Enviar EMAIL
        try {
            $emailDestino = $empresa->email ?? $empresa->user?->email;
            if ($emailDestino) {
                Mail::to($emailDestino)->send(
                    new EmpresaValidadaMail($empresa, false, $request->motivo_reprovacao)
                );
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao enviar email de reprovação: ' . $e->getMessage());
        }

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa reprovada.');
    }

    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);

        if ($empresa->user) {
            $empresa->user->delete();
        }

        $empresa->delete();

        return redirect()->route('admin.empresas.index')
            ->with('success', 'Empresa removida.');
    }
}