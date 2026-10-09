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

    public Candidatura $candidatura;
    public $oportunidade;
    public $egresso;

    public function __construct(Candidatura $candidatura)
    {
        $this->candidatura  = $candidatura;
        $this->oportunidade = $candidatura->oportunidade;
        $this->egresso      = $candidatura->egresso;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Presença Confirmada na Entrevista — Alumni Track',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.presenca-confirmada-entrevista',
        );
    }
}