<?php

namespace App\Mail;

use App\Models\InscricaoEvento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PresencaEventoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InscricaoEvento $inscricao,
        public bool $presente
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->presente ? '✅ Presença Confirmada' : '⚠️ Falta Registada')
                . ' — ' . ($this->inscricao->evento->titulo ?? 'Evento'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.presenca-evento',
            with: [
                'inscricao' => $this->inscricao,
                'evento'    => $this->inscricao->evento,
                'egresso'   => $this->inscricao->egresso,
                'presente'  => $this->presente,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}