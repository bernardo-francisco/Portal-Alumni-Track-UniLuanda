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
        ]);
    })

    /*
    |--------------------------------------------------------------------------
    | EXCEÇÕES
    |--------------------------------------------------------------------------
    */

    ->withExceptions(function (Exceptions $exceptions) {

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
    })

    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'empresa.aprovada' => \App\Http\Middleware\EmpresaAprovada::class,
    ]);
})

    ->create();

    
