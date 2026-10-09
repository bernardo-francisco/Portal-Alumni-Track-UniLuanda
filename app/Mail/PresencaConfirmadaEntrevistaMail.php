<?php

namespace App\Mail;

use App\Models\Candidatura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PresencaConfirmadaEntrevistaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Candidatura $candidatura
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Presença Confirmada na Entrevista — Alumni Track',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.presenca-confirmada-entrevista',
            with: [
                'candidatura'  => $this->candidatura,
                'oportunidade' => $this->candidatura->oportunidade,
                'egresso'      => $this->candidatura->egresso,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}