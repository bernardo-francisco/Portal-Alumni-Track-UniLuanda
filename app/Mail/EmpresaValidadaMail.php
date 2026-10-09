<?php

namespace App\Mail;

use App\Models\Empresa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmpresaValidadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Empresa $empresa,
        public bool $aprovada,
        public ?string $motivo = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->aprovada
                ? '✅ A sua empresa foi aprovada — UniLuanda Alumni Track'
                : '❌ A sua empresa não foi aprovada — UniLuanda Alumni Track',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.empresa.validada', 
            with: [
                'empresa'  => $this->empresa,
                'aprovada' => $this->aprovada,
                'motivo'   => $this->motivo,
            ],
        );
    }
}