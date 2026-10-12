@extends('emails.layouts.email')

@section('title', 'Confirme o seu Email')

@section('header_title', '✉️ Confirme o seu Email')

@section('header_subtitle', 'Falta só um passo para concluir')

@section('content')

    <p style="font-size: 16px; color: #1a1a1a; margin: 0 0 16px 0;">
        Olá, <strong>{{ $nome }}</strong>!
    </p>

    <p style="font-size: 15px; color: #444; line-height: 1.6; margin: 0 0 24px 0;">
        Recebemos o seu registo no portal <strong>UniLuanda Alumni Track</strong>.
        Para concluir, precisamos que confirme que este email é seu.
    </p>

    <p style="font-size: 15px; color: #444; line-height: 1.6; margin: 0 0 24px 0;">
        Clique no botão abaixo para verificar:
    </p>

    {{-- BOTÃO --}}
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $link }}"
           style="display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; text-decoration: none; border-radius: 12px; font-size: 15px; font-weight: 800; letter-spacing: 0.3px; box-shadow: 0 8px 24px rgba(201, 162, 39, 0.4);">
            ✅ Confirmar Email
        </a>
    </div>

    <p style="font-size: 13px; color: #888; line-height: 1.6; margin: 24px 0 0 0;">
        Ou copie e cole este link no seu navegador:
    </p>

    <p style="font-size: 12px; color: #a67c00; word-break: break-all; line-height: 1.5; margin: 8px 0 0 0;">
        {{ $link }}
    </p>

    <p style="font-size: 13px; color: #888; line-height: 1.6; margin: 24px 0 0 0;">
        ⏱️ Este link expira em <strong>24 horas</strong>.
    </p>

    <p style="font-size: 13px; color: #888; line-height: 1.6; margin: 8px 0 0 0;">
        Se não fez este registo, ignore este email.
    </p>

    <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
        <p style="font-size: 13px; color: #999; margin: 0;">
            <strong style="color: #c9a227;">Equipa Alumni Track</strong><br>
            Universidade de Luanda
        </p>
    </div>

@endsection