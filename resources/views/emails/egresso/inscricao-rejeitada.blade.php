@extends('emails.layouts.email')

@section('title', '❌ Inscrição Rejeitada - Alumni Track')
@section('header_title', '❌ Inscrição Rejeitada')
@section('header_subtitle', 'Saiba o que aconteceu')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $inscricao->egresso->nome_completo }}</strong>
    </p>

    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Lamentamos informar que a sua inscrição no evento abaixo foi <strong style="color: #dc2626;">rejeitada</strong>.
    </p>

    {{-- Info do evento --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-radius: 12px; border-left: 4px solid #c9a227; padding: 20px 24px; margin-bottom: 24px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #a67c00; font-size: 14px;">
                    🎪 Evento:
                </p>
                <p style="margin: 0; font-size: 15px; color: #0a0a0a; font-weight: 700;">
                    {{ $inscricao->evento->titulo }}
                </p>
                @if($inscricao->evento->data_inicio)
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: #525252;">
                        📅 {{ $inscricao->evento->data_inicio->format('d/m/Y \à\s H:i') }}
                    </p>
                @endif
                @if($inscricao->evento->local)
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #525252;">
                        📍 {{ $inscricao->evento->local }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Motivo (só se existir) --}}
    @if($inscricao->motivo_rejeicao)
        <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef2f2; border-radius: 12px; border-left: 4px solid #dc2626; padding: 20px 24px; margin-bottom: 28px;">
            <tr>
                <td>
                    <p style="margin: 0 0 6px 0; font-weight: 600; color: #991b1b; font-size: 14px;">
                        📋 Motivo da Rejeição:
                    </p>
                    <p style="margin: 0; font-size: 14px; color: #7f1d1d; background: #fee2e2; padding: 12px 16px; border-radius: 8px;">
                        {{ $inscricao->motivo_rejeicao }}
                    </p>
                </td>
            </tr>
        </table>
    @endif

    {{-- Ajuda --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border-left: 4px solid #0a0a0a; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #0a0a0a; font-size: 14px;">
                    💡 O que fazer agora?
                </p>
                <p style="margin: 0; font-size: 14px; color: #525252;">
                    Para mais informações ou para recorrer da decisão, entre em contacto com a coordenação Alumni através do email abaixo.
                </p>
            </td>
        </tr>
    </table>

    {{-- Botão Contato --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="mailto:{{ config('mail.from.address') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    📧 Contactar Coordenação
                </button>
            </td>
        </tr>
    </table>

    <p style="font-size: 14px; color: #525252; margin: 0; text-align: center;">
        Agradecemos o seu interesse em participar. 🎓
    </p>

@endsection