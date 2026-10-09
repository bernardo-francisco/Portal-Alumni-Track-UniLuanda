<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        // Verificar se é administrador usando o método do Trait
        if (!$user->isAdmin()) {
            return redirect()->route('login')
                ->with('error', 'Acesso negado. Esta área é restrita a administradores.');
        }

        return $next($request);
    }
}