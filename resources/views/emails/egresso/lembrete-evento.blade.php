@extends('emails.layouts.email')

@php
    $cfg = [
        'lembrete_24h' => [
            'title'    => '⏰ Lembrete: Amanhã',
            'subtitle' => 'O evento é amanhã!',
            'msg'      => 'O evento <strong>' . e($evento->titulo) . '</strong> acontece <strong style="color: #a67c00;">amanhã</strong>. Não te esqueças!',
        ],
        'lembrete_1h' => [
            'title'    => '⏰ Começa em 1 Hora',
            'subtitle' => 'Está quase!',
            'msg'      => 'O evento <strong>' . e($evento->titulo) . '</strong> começa dentro de <strong style="color: #a67c00;">1 hora</strong>. Prepara-te!',
        ],
    ];
    $c = $cfg[$tipo] ?? $cfg['lembrete_24h'];
@endphp

@section('title', $c['title'] . ' — Alumni Track')
@section('header_title', $c['title'])
@section('header_subtitle', $c['subtitle'])

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, {{ $egresso->nome_completo }}
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        {!! $c['msg'] !!}
    </p>

    {{-- Detalhes do Evento --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    📅 Detalhes do Evento
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 140px; font-weight: 600;">Título:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $evento->titulo }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Data:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <strong>{{ $evento->data_inicio ? $evento->data_inicio->format('d/m/Y \à\s H:i') : 'A anunciar' }}</strong>
                        </td>
                    </tr>
                    @if($evento->local)
                        <tr>
                            <td style="color: #525252; font-size: 14px; font-weight: 600;">Local:</td>
                            <td style="color: #0a0a0a; font-size: 14px;">{{ $evento->local }}</td>
                        </tr>
                    @endif
                    @if($evento->link_reuniao && $evento->tipo !== 'presencial')
                        <tr>
                            <td style="color: #525252; font-size: 14px; font-weight: 600;">Link:</td>
                            <td style="color: #0a0a0a; font-size: 14px;">
                                <a href="{{ $evento->link_reuniao }}" style="color: #a67c00; text-decoration: none;">{{ $evento->link_reuniao }}</a>
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Botão --}}
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
        Contamos contigo! 🎓
    </p>

@endsection