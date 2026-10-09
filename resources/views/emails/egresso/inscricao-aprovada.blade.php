@extends('emails.layouts.email')

@section('title', '✅ Inscrição Aprovada - ' . $inscricao->evento->titulo)
@section('header_title', '✅ Inscrição Aprovada!')
@section('header_subtitle', 'A sua presença está confirmada')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $inscricao->egresso->nome_completo }}</strong>
    </p>

    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        🎉 <strong>Boas notícias!</strong> A sua inscrição no evento abaixo foi
        <strong style="color: #16a34a;">confirmada</strong>.
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

    {{-- Código comprovativo --}}
    @if($inscricao->codigo_comprovativo)
        <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 20px 24px; margin-bottom: 28px; text-align: center;">
            <tr>
                <td>
                    <p style="margin: 0 0 6px 0; font-size: 12px; color: #8a8a8a; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                        Código de Verificação
                    </p>
                    <p style="margin: 0; font-size: 20px; font-weight: 700; color: #a67c00; letter-spacing: 3px; font-family: 'Courier New', monospace;">
                        {{ $inscricao->codigo_comprovativo }}
                    </p>
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #525252;">
                        Apresente este código no dia do evento.
                    </p>
                </td>
            </tr>
        </table>
    @endif

    {{-- Botão Comprovativo --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ route('egresso.eventos.comprovativo', $inscricao->id) }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    📄 Descarregar Comprovativo
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 14px; color: #525252; margin: 0; text-align: center;">
        Estamos ansiosos por vê-lo(a) no evento! 🎓
    </p>

@endsection