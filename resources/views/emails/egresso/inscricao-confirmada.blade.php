@extends('emails.layouts.email')

@section('title', '✅ Inscrição Confirmada — Alumni Track')
@section('header_title', '✅ Inscrição Confirmada!')
@section('header_subtitle', 'A tua inscrição foi registada com sucesso')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $egresso->nome_completo }}</strong>
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        A tua inscrição no evento abaixo foi <strong style="color: #16a34a;">registada com sucesso</strong>.
        Vais receber um email quando for aprovada pela organização.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-radius: 12px; border-left: 4px solid #c9a227; padding: 20px 24px; margin-bottom: 24px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #a67c00; font-size: 14px;">
                    🎪 Evento:
                </p>
                <p style="margin: 0; font-size: 15px; color: #0a0a0a; font-weight: 700;">
                    {{ $evento->titulo }}
                </p>
                @if($evento->data_inicio)
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: #525252;">
                        📅 {{ $evento->data_inicio->format('d/m/Y \à\s H:i') }}
                    </p>
                @endif
                @if($evento->local)
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #525252;">
                        📍 {{ $evento->local }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef3c7; border-radius: 12px; border-left: 4px solid #d97706; padding: 16px 20px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0; font-size: 14px; color: #92400e;">
                    ⏳ <strong>Estado atual:</strong> Pendente de aprovação.
                    Assim que for aprovada, receberás o teu código de comprovativo.
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/egresso/minhas-inscricoes') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    📋 Ver Minhas Inscrições
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Obrigado por participares! 🎓
    </p>

@endsection