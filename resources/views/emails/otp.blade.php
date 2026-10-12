@extends('emails.layouts.email')

@section('title', 'Código de Verificação')

@section('header_title', '🔐 Código de Verificação')

@section('header_subtitle', 'Confirme a sua identidade')

@section('content')

    <p style="font-size: 16px; color: #1a1a1a; margin: 0 0 16px 0;">
        Olá, <strong>{{ $nome }}</strong>!
    </p>

    <p style="font-size: 15px; color: #444; line-height: 1.6; margin: 0 0 24px 0;">
        Recebemos um pedido para ativar a sua conta no portal <strong>UniLuanda Alumni Track</strong>.
        Use o código abaixo para confirmar a sua identidade:
    </p>

    {{-- CÓDIGO DESTAQUE --}}
    <div style="background: #f9f6ec; border: 2px dashed #c9a227; border-radius: 12px; padding: 28px 24px; text-align: center; margin: 24px 0;">
        <div style="color: #a67c00; font-size: 12px; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 10px; font-weight: 700;">
            Código de Verificação
        </div>
        <div style="color: #0a0a0a; font-size: 40px; font-weight: 800; letter-spacing: 0.35em; font-family: 'Courier New', monospace;">
            {{ $codigo }}
        </div>
    </div>

    <p style="font-size: 14px; color: #666; line-height: 1.6; margin: 24px 0 0 0;">
        ⏱️ Este código expira em <strong>10 minutos</strong>.
    </p>

    <p style="font-size: 14px; color: #666; line-height: 1.6; margin: 8px 0 0 0;">
        Se não pediu este código, ignore este email. A sua conta permanecerá segura.
    </p>

    <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
        <p style="font-size: 13px; color: #999; margin: 0;">
            <strong style="color: #c9a227;">Equipa Alumni Track</strong><br>
            Universidade de Luanda
        </p>
    </div>

@endsection