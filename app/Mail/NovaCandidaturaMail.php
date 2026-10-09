<?php

namespace App\Mail;

use App\Models\Candidatura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaCandidaturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Candidatura $candidatura
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎯 Nova Candidatura — ' . ($this->candidatura->oportunidade->titulo ?? 'Oportunidade'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.empresa.nova-candidatura',
            with: [
                'candidatura'  => $this->candidatura,
                'oportunidade' => $this->candidatura->oportunidade,
                'egresso'      => $this->candidatura->egresso,
                'empresa'      => $this->candidatura->oportunidade->empresa ?? null,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}