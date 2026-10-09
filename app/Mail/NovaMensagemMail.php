<?php

namespace App\Mail;

use App\Models\Egresso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaMensagemMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param object $remetente    Quem enviou (Egresso ou Admin)
     * @param object $destinatario Quem recebe (Egresso ou Admin)
     * @param string $tipo         'texto' | 'audio' | 'ficheiro'
     * @param string $conteudo     Texto da mensagem, ou nome/descrição do ficheiro
     */
    public function __construct(
        public $remetente,
        public $destinatario,
        public string $tipo = 'texto',
        public string $conteudo = ''
    ) {}

    public function envelope(): Envelope
    {
        $nome = $this->remetente->nome_completo ?? 'Alguém';

        $subject = match($this->tipo) {
            'audio'    => '🎤 Mensagem de voz de ' . $nome,
            'ficheiro' => '📎 Ficheiro de ' . $nome,
            default    => '💬 Nova mensagem de ' . $nome,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nova-mensagem',
            with: [
                'remetente'    => $this->remetente,
                'destinatario' => $this->destinatario,
                'tipo'         => $this->tipo,
                'conteudo'     => $this->conteudo,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}