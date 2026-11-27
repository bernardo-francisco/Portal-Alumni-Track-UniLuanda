<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifySingleSession
{
    /**
     * Garante que apenas uma sessão ativa existe por utilizador.
     *
     * Regras:
     *  - Não autenticado → deixa passar (o auth trata disso).
     *  - session_id NULL (utilizador antigo) → adota a sessão atual.
     *  - session_id coincide → deixa passar.
     *  - Caso contrário → termina a sessão, grava o motivo e redireciona
     *    (ou devolve 401 JSON para AJAX).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user       = Auth::user();
        $currentSid = $request->session()->getId();

        // 🔧 Utilizador antigo (sem session_id) → adota a sessão atual
        if (empty($user->session_id)) {
            $user->forceFill(['session_id' => $currentSid])->save();
            return $next($request);
        }

        // ✅ Sessão válida
        if (hash_equals((string) $user->session_id, (string) $currentSid)) {
            return $next($request);
        }

        // ============================================================
        // ❌ Sessão inválida — iniciada noutro dispositivo
        // ============================================================
        $mensagem = '🔒 A sua conta foi iniciada noutro dispositivo. Faça login novamente.';

        Auth::logout();

        // ✅ Grava a flash com chave própria (lida diretamente pela view)
        $request->session()->flash('session_invalidated', $mensagem);

        // Regenera o token CSRF (a flash 'session_invalidated' sobrevive)
        $request->session()->regenerateToken();

        // Resposta JSON para AJAX/polling (chatbot, notificações, etc.)
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => false,
                'message'  => $mensagem,
                'code'     => 'SESSION_INVALIDATED',
                'redirect' => route('login'),
            ], 401);
        }

        return redirect()->route('login');
    }
}