<?php

namespace App\Mail;

use App\Models\Candidatura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusCandidaturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Candidatura $candidatura,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        $titulo = $this->candidatura->oportunidade->titulo ?? 'Oportunidade';

        $subject = match($this->status) {
            'aprovado'   => '🎉 Candidatura Aprovada — ' . $titulo,
            'rejeitado'  => '❌ Candidatura Não Aceite — ' . $titulo,
            'entrevista' => '📅 Entrevista Marcada — ' . $titulo,
            'em_analise' => '🔍 Candidatura em Análise — ' . $titulo,
            'aceite'     => '✅ Candidatura Aceite — ' . $titulo,
            default      => '📋 Candidatura Atualizada — ' . $titulo,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.status-candidatura',
            with: [
                'candidatura'  => $this->candidatura,
                'oportunidade' => $this->candidatura->oportunidade,
                'egresso'      => $this->candidatura->egresso,
                'empresa'      => $this->candidatura->oportunidade->empresa ?? null,
                'status'       => $this->status,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}