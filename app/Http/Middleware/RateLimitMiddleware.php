<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Obter o IP do cliente
        $ip = $request->ip() ?? $request->getClientIp() ?? 'unknown';
        
        // Obter o nome da rota
        $routeName = $request->route() ? $request->route()->getName() : 'unknown';
        
        // Chave única para rate limiting
        $key = 'rate_limit_' . $ip . '_' . $routeName;
        $maxAttempts = 60; // 60 requisições
        $decayMinutes = 1; // por minuto

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            Log::warning('Rate limit exceeded', [
                'ip' => $ip,
                'route' => $routeName,
                'user_id' => Auth::id() ?? 'guest',
            ]);

            return response()->json([
                'message' => 'Muitas requisições. Tente novamente em alguns instantes.',
                'retry_after' => $decayMinutes * 60,
            ], 429);
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }
}