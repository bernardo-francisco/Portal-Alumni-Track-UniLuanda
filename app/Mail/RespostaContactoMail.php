<?php

namespace App\Mail;

use App\Models\ContactoAlumni;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RespostaContactoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contacto;
    public $resposta;

    public function __construct(ContactoAlumni $contacto, string $resposta)
    {
        $this->contacto = $contacto;
        $this->resposta = $resposta;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: ' . $this->contacto->assunto . ' - UniLuanda Alumni',

            // ✅ FORMA CORRETA
            replyTo: [
                new Address(
                    config('mail.from.address'),
                    config('mail.from.name')
                ),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.resposta-contacto',
        );
    }
}