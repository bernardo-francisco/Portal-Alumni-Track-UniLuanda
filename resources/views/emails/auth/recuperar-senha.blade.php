@extends('emails.layouts.email')

@section('title', '🔑 Recuperação de Palavra-passe — Alumni Track')
@section('header_title', '🔑 Recuperação de Palavra-passe')
@section('header_subtitle', 'Recebemos o teu pedido')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $nome }}</strong>
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Recebemos um pedido para redefinir a tua palavra-passe no
        <strong>UniLuanda Alumni Track</strong>.
        Clica no botão abaixo para criares uma nova palavra-passe.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-radius: 12px; border-left: 4px solid #c9a227; padding: 16px 20px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    ⏰ <strong>Esta ligação expira em 60 minutos.</strong>
                    Se não pediste esta alteração, ignora este email — a tua palavra-passe continua segura.
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ $resetUrl }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    🔑 Redefinir Palavra-passe
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 12px; color: #8a8a8a; margin: 30px 0 0; text-align: center;">
        Se o botão não funcionar, copia e cola esta ligação no navegador:
    </p>
    <p style="font-size: 11px; color: #a67c00; margin: 8px 0 0; text-align: center; word-break: break-all;">
        {{ $resetUrl }}
    </p>

    <hr style="border: none; border-top: 1px solid #e6e2d3; margin: 30px 0;">

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Com os melhores cumprimentos,<br>
        <strong style="color: #a67c00;">Equipa UniLuanda Alumni Track</strong>
    </p>

@endsection