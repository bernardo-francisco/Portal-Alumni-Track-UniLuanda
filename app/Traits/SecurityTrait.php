<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // <-- CORREÇÃO: Adicionar esta linha
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait SecurityTrait
{
    /**
     * Valida e sanitiza um email
     */
    protected function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Valida e sanitiza um input
     */
    protected function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Gera um token seguro
     */
    protected function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Verifica se a senha é forte
     */
    protected function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }

    /**
     * Gera uma senha forte aleatória
     */
    protected function generateStrongPassword(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Rate limiting para tentativas de login
     */
    protected function checkLoginAttempts(Request $request): bool
    {
        $key = 'login_attempts_' . $request->ip();
        $attempts = Cache::get($key, 0);

        if ($attempts >= 5) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes(15));
        return true;
    }

    /**
     * Resetar tentativas de login
     */
    protected function resetLoginAttempts(Request $request): void
    {
        $key = 'login_attempts_' . $request->ip();
        Cache::forget($key);
    }

    /**
     * Verifica se o IP está bloqueado
     */
    protected function isIpBlocked(Request $request): bool
    {
        $key = 'blocked_ip_' . $request->ip();
        return Cache::has($key);
    }

    /**
     * Bloqueia um IP por tempo determinado
     */
    protected function blockIp(Request $request, int $minutes = 30): void
    {
        $key = 'blocked_ip_' . $request->ip();
        Cache::put($key, true, now()->addMinutes($minutes));
    }

    /**
     * CSRF Token adicional para ações críticas
     */
    protected function validateCriticalAction(Request $request): bool
    {
        $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_critical_token');
        $sessionToken = session('critical_token');

        if (!$token || !$sessionToken) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Gera token crítico
     */
    protected function generateCriticalToken(): string
    {
        $token = bin2hex(random_bytes(32));
        session(['critical_token' => $token]);
        return $token;
    }

    /**
     * Verifica se o usuário está autenticado e ativo
     */
    protected function ensureAuthenticatedAndActive(): bool
    {
        // CORREÇÃO: Auth:: em vez de auth::
        if (!Auth::check()) {
            return false;
        }
/** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->isActive();
    }

    /**
     * Log de atividade sensível
     */
    protected function logSecurityEvent(string $event, array $data = []): void
    {
        $log = [
            'event' => $event,
            'user_id' => Auth::id(),  // CORREÇÃO: Auth:: em vez de auth::
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'data' => $data,
        ];

        Log::channel('security')->info(json_encode($log));
    }

    /**
     * Valida email com verificação de domínio
     */
    protected function validateEmailDomain(string $email, array $allowedDomains = []): bool
    {
        $domain = substr(strrchr($email, "@"), 1);
        
        if (empty($allowedDomains)) {
            return true;
        }
        
        return in_array($domain, $allowedDomains);
    }

    /**
     * Obtém ID do usuário logado com segurança
     */
    protected function getUserId(): ?int
    {
        if (Auth::check()) {  // CORREÇÃO: Auth:: em vez de auth::
            return Auth::id();
        }
        return null;
    }

    /**
     * Obtém o usuário logado com segurança
     */
    protected function getUser(): ?\App\Models\User
    {
        if (Auth::check()) {  // CORREÇÃO: Auth:: em vez de auth::
            return Auth::user();
        }
        return null;
    }
}