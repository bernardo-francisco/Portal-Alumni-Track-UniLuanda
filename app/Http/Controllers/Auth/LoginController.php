<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Mostra o formulário de login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Processa o pedido de autenticação.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // =============================================
        // REDIRECIONAMENTO INTELIGENTE POR TIPO
        // =============================================

        // =============================================
        // 🔥 ADMIN
        // =============================================
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Bem-vindo, ' . $user->name . '!');
        }

        // =============================================
        // 🔥 EMPRESA
        // =============================================
        if ($user->isEmpresa()) {
            $empresa = $user->empresa;

            // Se não tiver perfil de empresa
            if (!$empresa) {
                Auth::logout();
                return back()->with('error', 'Perfil de empresa não encontrado. Entre em contacto com a administração.');
            }

            // Se a empresa foi reprovada
            if ($empresa->status_validacao === 'reprovado') {
                Auth::logout();
                $motivo = $empresa->motivo_reprovacao ?? 'Motivo não informado.';
                return back()->with('error', '❌ O registo da sua empresa foi reprovado. Motivo: ' . $motivo);
            }

            // Se a empresa ainda está pendente (NÃO faz logout — permite ver o estado)
            if ($empresa->status_validacao === 'pendente') {
                return redirect()->route('empresa.pendente')
                    ->with('warning', '⏳ A sua empresa aguarda validação pela administração.');
            }

            // Se a empresa está inativa
            if (!$empresa->ativo) {
                Auth::logout();
                return back()->with('error', '⚠️ A conta da sua empresa está inativa. Contacte a administração.');
            }

            // ✅ Empresa aprovada e ativa
            return redirect()->route('empresa.dashboard')
                ->with('success', 'Bem-vindo, ' . $empresa->nome . '!');
        }

        // =============================================
        // 🔥 EGRESSO
        // =============================================
        if ($user->isEgresso()) {
            $egresso = $user->egresso;

            // Se NÃO tiver perfil de egresso
            if (!$egresso) {
                Auth::logout();
                return back()->with('error', 'Perfil de egresso não encontrado. Entre em contacto com a administração.');
            }

            // Caso 1: Pendente
            if ($egresso->status_validacao === 'pendente') {
                Auth::logout();
                return back()->with('error', '⏳ Sua conta está aguardando validação manual. Você receberá um email quando for aprovada.');
            }

            // Caso 2: Reprovado
            if ($egresso->status_validacao === 'reprovado') {
                Auth::logout();
                $motivo = $egresso->motivo_reprovacao ?? 'Motivo não informado.';
                return back()->with('error', '❌ Seu cadastro foi reprovado. Motivo: ' . $motivo);
            }

            // Caso 3: Aprovado mas inativo
            if ($egresso->status !== 'active') {
                Auth::logout();
                return back()->with('error', '⚠️ Sua conta está inativa. Entre em contacto com a administração.');
            }

            // ✅ Caso 4: Aprovado e ativo → Libera acesso
            if (!$egresso->verificado) {
                $egresso->update([
                    'verificado' => true,
                    'data_verificacao' => now(),
                ]);
            }

            return redirect()->route('egresso.dashboard')
                ->with('success', 'Bem-vindo de volta, ' . $user->name . '!');
        }

        // =============================================
        // FALLBACK
        // =============================================
        return redirect()->route('home');
    }

    /**
     * Termina a sessão autenticada.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Sessão encerrada com sucesso!');
    }
}