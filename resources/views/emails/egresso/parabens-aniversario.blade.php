@extends('emails.layouts.email')

@section('title', '🎂 Feliz Aniversário — Alumni Track')
@section('header_title', '🎂 Feliz Aniversário!')
@section('header_subtitle', 'A UniLuanda celebra contigo')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Parabéns, {{ $egresso->nome_completo }}! 🎉
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        A equipa do <strong>UniLuanda Alumni Track</strong> deseja-te um excelente dia de aniversário.
        Continua a inspirar a nossa comunidade!
    </p>

    {{-- Caixa de mensagem --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-left: 4px solid #c9a227; border-radius: 8px; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 8px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    🎁 Uma mensagem especial para ti
                </p>
                <p style="margin: 0; color: #525252; font-size: 14px; line-height: 1.7; font-style: italic;">
                    "Cada ano que passa é uma nova página na tua história. Que este seja repleto de conquistas,
                    saúde e momentos inesquecíveis. A rede Alumni é mais rica por te ter."
                </p>
            </td>
        </tr>
    </table>

    {{-- Botão --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/egresso/dashboard') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    🎓 Aceder à Plataforma
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Com os melhores cumprimentos,<br>
        <strong style="color: #a67c00;">Equipa UniLuanda Alumni Track</strong>
    </p>

@endsection