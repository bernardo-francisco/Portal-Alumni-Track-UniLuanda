@extends('emails.layouts.email')

@section('title', 'Resposta - UniLuanda Alumni')
@section('header_title', 'Apoio ao Alumni')
@section('header_subtitle', 'Respondemos ao seu contacto')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $contacto->nome }}</strong>,
    </p>

    <p style="font-size: 15px; line-height: 1.7; color: #525252; margin: 0 0 24px 0;">
        Recebemos a sua mensagem e a nossa equipa preparou uma resposta para si:
    </p>

    {{-- Resposta --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-left: 4px solid #c9a227; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
        <tr>
            <td>
                <p style="margin: 0; font-size: 15px; line-height: 1.8; color: #0a0a0a; white-space: pre-wrap;">{{ $resposta }}</p>
            </td>
        </tr>
    </table>

    <p style="font-size: 15px; line-height: 1.7; color: #525252; margin: 25px 0 0;">
        Se tiver mais alguma dúvida, não hesite em responder a este email.
    </p>

    <hr style="border: none; border-top: 1px solid #e6e2d3; margin: 30px 0;">

    <p style="font-size: 13px; color: #8a8a8a; margin: 0 0 10px; font-weight: 600;">
        📩 A sua mensagem original:
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; padding: 18px 20px; border-radius: 8px;">
        <tr>
            <td style="font-size: 13px; color: #525252; line-height: 1.6;">
                <p style="margin: 0 0 8px;">
                    <strong style="color: #0a0a0a;">Assunto:</strong> {{ $contacto->assunto }}
                </p>
                <p style="margin: 0 0 8px;">
                    <strong style="color: #0a0a0a;">Enviada em:</strong> {{ $contacto->created_at->format('d/m/Y \à\s H:i') }}
                </p>
                <p style="margin: 0; white-space: pre-wrap;">{{ $contacto->mensagem }}</p>
            </td>
        </tr>
    </table>

@endsection