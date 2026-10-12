<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $codigo,
        public string $nomeDestinatario
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Código de verificação — UniLuanda Alumni',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'codigo' => $this->codigo,
                'nome'   => $this->nomeDestinatario,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}