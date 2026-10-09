<?php

namespace App\Mail;

use App\Models\InscricaoEvento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LembreteEventoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InscricaoEvento $inscricao,
        public string $tipo = 'lembrete_24h'
    ) {}

    public function envelope(): Envelope
    {
        $evento = $this->inscricao->evento;
        $titulo = $evento->titulo ?? 'Evento';

        $subject = match($this->tipo) {
            'lembrete_1h'  => '⏰ Começa em 1 hora — ' . $titulo,
            'lembrete_24h' => '⏰ Lembrete: Amanhã — ' . $titulo,
            default        => '✅ Inscrição Confirmada — ' . $titulo,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.lembrete-evento',
            with: [
                'inscricao' => $this->inscricao,
                'evento'    => $this->inscricao->evento,
                'egresso'   => $this->inscricao->egresso,
                'tipo'      => $this->tipo,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}