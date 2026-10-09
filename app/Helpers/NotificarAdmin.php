<?php

namespace App\Helpers;

use App\Models\Admin;
use App\Models\Notificacao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificarAdmin
{
    /**
     * Notifica todos os admins activos.
     */
    public static function todos(
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ): void {
        try {
            $admins = Admin::query()
                ->when(
                    Schema::hasColumn('admins', 'ativo'),
                    fn ($q) => $q->where('ativo', true)
                )
                ->get();

            foreach ($admins as $admin) {
                Notificacao::create([
                    'admin_id' => $admin->id,
                    'tipo'     => $tipo,
                    'titulo'   => $titulo,
                    'mensagem' => $mensagem,
                    'link'     => $link,
                    'lida'     => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao notificar admin: ' . $e->getMessage());
        }
    }
}