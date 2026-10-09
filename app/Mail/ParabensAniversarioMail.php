<?php

namespace App\Mail;

use App\Models\Egresso;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParabensAniversarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Egresso $egresso
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎂 Feliz Aniversário, ' . $this->egresso->nome_completo . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.parabens-aniversario',
            with: [
                'egresso' => $this->egresso,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}