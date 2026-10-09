<?php

namespace App\Mail;

use App\Models\Egresso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContaReprovadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Egresso $egresso;
    public string $motivo;

    public function __construct(Egresso $egresso, string $motivo)
    {
        $this->egresso = $egresso;
        $this->motivo = $motivo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Seu Cadastro foi Reprovado - UNILUANDA Alumni',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.conta-reprovada',
        );
    }
}