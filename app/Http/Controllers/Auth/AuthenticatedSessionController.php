<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // =============================================
        // REDIRECIONAMENTO INTELIGENTE
        // =============================================

        // Verificar se é ADMIN (usando a UserRolesTrait)
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Bem-vindo, ' . $user->name . '!');
        }

        // Verificar se é EGRESSO (usando a UserRolesTrait)
        if ($user->isEgresso()) {
            // Verifica se tem perfil de egresso
            $egresso = $user->egresso;
            
            // Se NÃO tiver perfil de egresso, redireciona para criar
            if (!$egresso) {
                return redirect()->route('egresso.perfil')
                    ->with('warning', 'Complete seu perfil para aceder ao sistema.');
            }
            
            // Se NÃO estiver verificado, marcar como verificado automaticamente
            if (!$egresso->verificado) {
                $egresso->update([
                    'verificado' => true,
                    'data_verificacao' => now(),
                ]);
            }
            
            return redirect()->route('egresso.dashboard')
                ->with('success', 'Bem-vindo de volta, ' . $user->name . '!');
        }

        // Fallback para outros tipos de usuário
        return redirect()->route('home');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}