<?php

namespace App\Mail;

use App\Models\Egresso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContaAprovadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Egresso $egresso;

    public function __construct(Egresso $egresso)
    {
        $this->egresso = $egresso;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Sua Conta foi Aprovada - UNILUANDA Alumni',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.egresso.conta-aprovada',
        );
    }
}