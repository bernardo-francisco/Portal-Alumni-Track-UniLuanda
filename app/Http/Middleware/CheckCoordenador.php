<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCoordenador
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        // Verificar se é coordenador ou administrador usando os métodos do Trait
        if (!$user->isCoordenador() && !$user->isAdmin()) {
            return redirect()->route('login')
                ->with('error', 'Acesso negado. Área restrita a coordenadores e administradores.');
        }

        return $next($request);
    }
}