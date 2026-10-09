<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\NotificacaoService;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Compartilhar notificações e mensagens com o topbar
        View::composer('admin.partials.topbar', function ($view) {
            if (!Auth::check()) {
                return;
            }

            /** @var User|null $user */
            $user = Auth::user();

            // Garantir que é uma instância do nosso User
            if (!$user instanceof User) {
                return;
            }

            if (!$user->isAdmin()) {
                return;
            }

            $notificacoes = NotificacaoService::getNotificacoesAdmin();
            $mensagens = NotificacaoService::getMensagensAdmin();

            $view->with([
                'totalNotificacoes' => $notificacoes['total'],
                'notificacoes'      => $notificacoes['items'],
                'totalMensagens'    => $mensagens['total'],
                'mensagens'         => $mensagens['items'],
            ]);
        });
    }
}