<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;                          // ✅ NOVO

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
    }
}