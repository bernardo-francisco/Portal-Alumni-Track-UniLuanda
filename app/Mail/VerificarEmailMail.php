<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificarEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nome,
        public string $linkVerificacao,
        public string $tipo = 'egresso'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✉️ Confirme o seu email — UniLuanda Alumni',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verificar-email',
            with: [
                'nome' => $this->nome,
                'link' => $this->linkVerificacao,
                'tipo' => $this->tipo,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}