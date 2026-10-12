<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ NOVO — Carbon em Português
        Carbon::setLocale('pt');
        setlocale(LC_TIME, 'pt_PT.UTF-8', 'Portuguese_Portugal', 'pt_PT');

        // Helpers
        $helpersFile = app_path('Helpers/helpers.php');
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }

        // HTTPS
        if (
            request()->header('X-Forwarded-Proto') === 'https' ||
            request()->secure()
        ) {
            URL::forceScheme('https');
        }

        // Compartilhar notificações
        View::composer('*', function ($view) {
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();

            // ============================================================
            // EGRESSO
            // ============================================================
            $egresso = $user->egresso;
            if ($egresso && Schema::hasTable('notificacoes')) {
                try {
                    $notificacoesNaoLidas = \App\Models\Notificacao::where('egresso_id', $egresso->id)
                        ->where('lida', false)
                        ->count();
                } catch (\Exception $e) {
                    $notificacoesNaoLidas = 0;
                }
                $view->with('notificacoesNaoLidas', $notificacoesNaoLidas ?? 0);
            }

            // ============================================================
            // EMPRESA
            // ============================================================
            $empresa = $user->empresa;
            if ($empresa && Schema::hasTable('notificacoes')) {
                try {
                    $notificacoesEmpresa = \App\Models\Notificacao::paraEmpresa($empresa->id)
                        ->orderByDesc('created_at')
                        ->take(15)
                        ->get();

                    $notificacoesNaoLidasEmpresa = \App\Models\Notificacao::paraEmpresa($empresa->id)
                        ->where('lida', false)
                        ->count();

                    $contagemOportunidades = \App\Models\Oportunidade::where('empresa_id', $empresa->id)->count();

                    $contagemCandidaturas = \App\Models\Candidatura::whereIn(
                        'oportunidade_id',
                        \App\Models\Oportunidade::where('empresa_id', $empresa->id)->pluck('id')
                    )->count();

                    $view->with([
                        'notificacoesEmpresa'          => $notificacoesEmpresa,
                        'notificacoesNaoLidasEmpresa'  => $notificacoesNaoLidasEmpresa,
                        'contagemOportunidades'        => $contagemOportunidades,
                        'contagemCandidaturas'         => $contagemCandidaturas,
                    ]);
                } catch (\Exception $e) {
                    $view->with([
                        'notificacoesEmpresa'          => collect(),
                        'notificacoesNaoLidasEmpresa'  => 0,
                        'contagemOportunidades'        => 0,
                        'contagemCandidaturas'         => 0,
                    ]);
                }
            }
        });

        // ============================================================
        // ✅ FASE 1 — RATE LIMITERS ANTI-SPAM
        // ============================================================

        // ------------------------------------------------------------
        // Rate limiter para o REGISTO
        // ------------------------------------------------------------
        // Bloqueia: máximo 3 registos por IP a cada hora
        //           máximo 2 registos por email a cada hora
        // ------------------------------------------------------------
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perHour(3)->by('ip:' . $request->ip()),

                Limit::perHour(2)->by(
                    'email:' . strtolower((string) $request->input('email'))
                ),
            ];
        });

        // ------------------------------------------------------------
        // Rate limiter para a VERIFICAÇÃO DE Nº DE PROCESSO (AJAX)
        // ------------------------------------------------------------
        // Bloqueia: máximo 5 consultas por minuto por IP
        // ------------------------------------------------------------
        RateLimiter::for('verificar-processo', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // ------------------------------------------------------------
        // ✅ FASE 3 — Rate limiter para VERIFICAÇÃO DE OTP
        // ------------------------------------------------------------
        // Bloqueia: máximo 5 tentativas por minuto por IP
        //           máximo 3 tentativas por minuto por email
        // ------------------------------------------------------------
        RateLimiter::for('register-otp', function (Request $request) {
            return [
                Limit::perMinute(5)->by('ip:' . $request->ip()),

                Limit::perMinute(3)->by(
                    'email:' . strtolower((string) $request->input('email'))
                ),
            ];
        });
    }
}