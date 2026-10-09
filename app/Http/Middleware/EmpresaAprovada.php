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

            if (!$empresa || !$empresa->isAprovada()) {
                return redirect()->route('empresa.pendente');
            }
        }

        return $next($request);
    }
}