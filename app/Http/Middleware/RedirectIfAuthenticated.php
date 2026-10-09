<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                // Redirecionar baseado no tipo de usuário
                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }
                
                if ($user->isEgresso()) {
                    // Verifica se tem perfil de egresso
                    $egresso = $user->egresso;
                    
                    if (!$egresso) {
                        return redirect()->route('egresso.perfil')
                            ->with('warning', 'Complete seu perfil.');
                    }
                    
                    // Marcar como verificado automaticamente
                    if (!$egresso->verificado) {
                        $egresso->update([
                            'verificado' => true,
                            'data_verificacao' => now(),
                        ]);
                    }
                    
                    return redirect()->route('egresso.dashboard');
                }
                
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}