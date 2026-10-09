<?php

namespace App\Mail;

use App\Models\Egresso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable as MailableContract; // 🔥 ADICIONAR
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovoPendenteMail extends Mailable // Isso já é suficiente!
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
            subject: '📋 Novo Egresso Aguardando Validação - UNILUANDA Alumni',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.novo-pendente',
        );
    }
}