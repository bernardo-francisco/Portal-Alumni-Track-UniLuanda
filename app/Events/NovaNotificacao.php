<?php

namespace App\Events;

use App\Models\Admin;
use App\Models\Egresso;
use App\Models\Notificacao;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NovaNotificacao implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notificacao;

    public function __construct(Notificacao $notificacao)
    {
        $this->notificacao = $notificacao;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        // Se tem egresso_id → é para o egresso
        if ($this->notificacao->egresso_id) {
            $channels[] = new PrivateChannel('user.' . $this->notificacao->egresso_id);
        }

        // Se tem admin_id → descobrir o egresso associado ao admin
        if ($this->notificacao->admin_id) {
            $admin = Admin::find($this->notificacao->admin_id);

            if ($admin) {
                $egressoDoAdmin = Egresso::where('user_id', $admin->user_id)->first();

                if ($egressoDoAdmin) {
                    $channels[] = new PrivateChannel('user.' . $egressoDoAdmin->id);
                }
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'NovaNotificacao';
    }

    public function broadcastWith(): array
    {
        return [
            'id'       => $this->notificacao->id,
            'titulo'   => $this->notificacao->titulo,
            'mensagem' => $this->notificacao->mensagem,
            'tipo'     => $this->notificacao->tipo,
            'link'     => $this->notificacao->link,
        ];
    }
}