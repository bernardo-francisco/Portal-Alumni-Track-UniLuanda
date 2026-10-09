<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEgresso
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Faça login para aceder ao sistema.');
        }

        // ✅ Admins passam SEMPRE — nunca ficam presos aqui
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar se é egresso usando o método do Trait
        if (!$user->isEgresso()) {
            return redirect()->route('login')
                ->with('error', 'Acesso negado. Esta área é restrita a egressos.');
        }

        // Verificação do status de validação
        $egresso = $user->egresso;

        if (!$egresso) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Perfil de egresso não encontrado. Entre em contato com a administração.');
        }

        // Caso 1: Pendente
        if ($egresso->status_validacao === 'pendente') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', '⏳ Sua conta está aguardando validação manual. Você receberá um email quando for aprovada.');
        }

        // Caso 2: Reprovado
        if ($egresso->status_validacao === 'reprovado') {
            Auth::logout();
            $motivo = $egresso->motivo_reprovacao ?? 'Motivo não informado.';
            return redirect()->route('login')
                ->with('error', '❌ Seu cadastro foi reprovado. Motivo: ' . $motivo . ' Entre em contato com a administração.');
        }

        // Caso 3: Aprovado mas inativo
        if ($egresso->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', '⚠️ Sua conta está inativa. Entre em contato com a administração.');
        }

        // Caso 4: Aprovado e ativo → Libera acesso
        if (!$egresso->verificado) {
            $egresso->update([
                'verificado' => true,
                'data_verificacao' => now(),
            ]);
        }

        return $next($request);
    }
}