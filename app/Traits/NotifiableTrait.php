<?php

namespace App\Traits;

use App\Models\Notificacao;

trait NotifiableTrait
{
    /**
     * Relacionamento com notificações
     */
    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class, 'egresso_id');
    }

    /**
     * Obter notificações não lidas
     */
    public function notificacoesNaoLidas()
    {
        return $this->notificacoes()
                    ->where('lida', false)
                    ->get();
    }

    /**
     * Total de notificações não lidas
     */
    public function totalNotificacoesNaoLidas()
    {
        return $this->notificacoes()
                    ->where('lida', false)
                    ->count();
    }

    /**
     * Criar uma notificação
     */
    public function notificar(
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ) {
        $egressoId = $this->getKey();

        if (!$egressoId) {
            return null;
        }

        return Notificacao::create([
            'egresso_id' => $egressoId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensagem'   => $mensagem,
            'link'       => $link,
            'lida'       => false,
        ]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasNotificacoesComoLidas()
    {
        return $this->notificacoes()
                    ->where('lida', false)
                    ->update([
                        'lida' => true,
                        'updated_at' => now(),
                    ]);
    }
}