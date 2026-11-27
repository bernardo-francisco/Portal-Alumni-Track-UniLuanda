<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaAprovada
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->isEmpresa()) {
            $empresa = $user->empresa;

            if ($empresa && !$empresa->isAprovada()) {
                
                // ✅ Exceções: Página de pendente, logout, e as rotas do Perfil
                if (
                    !$request->routeIs('empresa.pendente') && 
                    !$request->routeIs('logout') &&
                    !$request->routeIs('empresa.perfil') && 
                    !$request->routeIs('empresa.perfil.update')
                ) {
                    return redirect()->route('empresa.pendente');
                }
            }
        }

        return $next($request);
    }
}