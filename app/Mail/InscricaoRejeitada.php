<?php

namespace App\Mail;

use App\Models\InscricaoEvento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoRejeitada extends Mailable
{
    use Queueable, SerializesModels;

    public InscricaoEvento $inscricao;

    public function __construct(InscricaoEvento $inscricao)
    {
        $this->inscricao = $inscricao;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Inscrição Rejeitada - ' . $this->inscricao->evento->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.inscricao-rejeitada',   // ← caminho atualizado
        );
    }
}