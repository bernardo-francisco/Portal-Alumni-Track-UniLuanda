<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    /*
    |--------------------------------------------------------------------------
    | ROTAS
    |--------------------------------------------------------------------------
    */

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | BROADCASTING / REVERB
    |--------------------------------------------------------------------------
    |
    | O endpoint /broadcasting/auth utiliza a sessão WEB para identificar
    | o utilizador autenticado e autorizar os canais privados do Reverb.
    |
    */

    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        [
            'middleware' => ['web', 'auth:web'],
        ]
    )

    /*
    |--------------------------------------------------------------------------
    | MIDDLEWARE
    |--------------------------------------------------------------------------
    */

    ->withMiddleware(function (Middleware $middleware) {

        // ============================================================
        // ✅ NOVO — Auditoria automática de acções de admins
        // ============================================================
        // Regista automaticamente todas as acções POST/PUT/PATCH/DELETE
        // realizadas por administradores autenticados.
        // ============================================================
        $middleware->web(append: [
            \App\Http\Middleware\AuditarAcoesAdmin::class,
        ]);

        // ============================================================
        // ALIASES
        // ============================================================
        $middleware->alias([

            'auth' => \App\Http\Middleware\Authenticate::class,

            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,

            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,

            'can' => \Illuminate\Auth\Middleware\Authorize::class,

            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,

            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,

            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,

            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,

            'check.admin' => \App\Http\Middleware\CheckAdmin::class,

            'check.egresso' => \App\Http\Middleware\CheckEgresso::class,

            'check.coordenador' => \App\Http\Middleware\CheckCoordenador::class,

            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,

            'empresa.aprovada' => \App\Http\Middleware\EmpresaAprovada::class,
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | EXCEÇÕES
    |--------------------------------------------------------------------------
    */

    ->withExceptions(function (Exceptions $exceptions) {

        // ============================================================
        // Autenticação — redireciona para login
        // ============================================================
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            $request
        ) {

            if ($request->expectsJson()) {

                return response()->json([
                    'message' => 'Não autenticado.'
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Por favor, faça login para aceder ao sistema.'
                );
        });

        // ============================================================
        // ✅ FASE 1 — Rate Limiting (429) personalizado
        // ============================================================
        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            \Illuminate\Http\Request $request
        ) {

            // Calcular tempo de espera em minutos
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            $minutos    = max(1, (int) ceil($retryAfter / 60));

            // --------------------------------------------------------
            // Rotas de REGISTO → redireciona para o formulário
            // --------------------------------------------------------
            if (
                $request->routeIs('register.post') ||
                $request->routeIs('register') ||
                str_contains($request->path(), 'registar')
            ) {
                return redirect()
                    ->route('register')
                    ->with(
                        'error',
                        "⏳ Demasiadas tentativas de registo. Por favor, aguarde {$minutos} minuto(s) antes de tentar novamente."
                    );
            }

            // --------------------------------------------------------
            // Verificação de processo (AJAX) → devolve JSON
            // --------------------------------------------------------
            if (
                $request->expectsJson() ||
                $request->routeIs('verificar.processo')
            ) {
                return response()->json([
                    'success'              => false,
                    'message'              => "Demasiadas tentativas. Aguarde {$minutos} minuto(s).",
                    'retry_after_minutes'  => $minutos,
                ], 429);
            }

            // --------------------------------------------------------
            // Fallback — comportamento padrão do Laravel
            // --------------------------------------------------------
            return null;
        });
    })

    ->create();