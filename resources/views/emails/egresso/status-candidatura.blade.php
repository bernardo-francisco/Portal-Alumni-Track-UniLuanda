@extends('emails.layouts.email')

@php
    $cfg = [
        'aprovado' => [
            'title'    => '🎉 Candidatura Aprovada',
            'subtitle' => 'Boas notícias!',
            'msg'      => 'A tua candidatura foi <strong style="color: #16a34a;">aprovada</strong>. Vais ser contactado em breve com os próximos passos.',
            'cor'      => '#16a34a',
            'cor_bg'   => '#dcfce7',
        ],
        'rejeitado' => [
            'title'    => '📋 Candidatura Não Aceite',
            'subtitle' => 'Continua a tentar',
            'msg'      => 'A tua candidatura <strong style="color: #dc2626;">não foi aceite</strong> desta vez. ' . ($candidatura->motivo_rejeicao ?? 'Continua a procurar. Boa sorte!'),
            'cor'      => '#dc2626',
            'cor_bg'   => '#fee2e2',
        ],
        'entrevista' => [
            'title'    => '📅 Entrevista Marcada',
            'subtitle' => 'Prepara-te!',
            'msg'      => 'Foi marcada uma <strong style="color: #2563eb;">entrevista</strong> para a tua candidatura. Confirma os detalhes no painel.',
            'cor'      => '#2563eb',
            'cor_bg'   => '#dbeafe',
        ],
        'aceite' => [
            'title'    => '✅ Candidatura Aceite',
            'subtitle' => 'Parabéns!',
            'msg'      => 'A tua candidatura foi <strong style="color: #16a34a;">aceite</strong> pela empresa.',
            'cor'      => '#16a34a',
            'cor_bg'   => '#dcfce7',
        ],
        'em_analise' => [
            'title'    => '🔍 Candidatura em Análise',
            'subtitle' => 'Em processamento',
            'msg'      => 'A tua candidatura está a ser <strong style="color: #d97706;">analisada</strong> pela empresa.',
            'cor'      => '#d97706',
            'cor_bg'   => '#fef3c7',
        ],
    ];

    $c = $cfg[$status] ?? $cfg['em_analise'];
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

    {{-- Detalhes --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    📋 Detalhes
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 160px; font-weight: 600;">Oportunidade:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $oportunidade->titulo }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Empresa:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $empresa->nome ?? 'Confidencial' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Estado:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <span style="display: inline-block; background: {{ $c['cor_bg'] }}; color: {{ $c['cor'] }}; padding: 2px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                {{ $candidatura->status_label }}
                            </span>
                        </td>
                    </tr>

                    @if($status === 'entrevista' && $candidatura->data_entrevista)
                        <tr>
                            <td style="color: #525252; font-size: 14px; font-weight: 600;">Data Entrevista:</td>
                            <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $candidatura->data_entrevista->format('d/m/Y H:i') }}</strong></td>
                        </tr>
                        @if($candidatura->local_entrevista)
                            <tr>
                                <td style="color: #525252; font-size: 14px; font-weight: 600;">Local:</td>
                                <td style="color: #0a0a0a; font-size: 14px;">{{ $candidatura->local_entrevista }}</td>
                            </tr>
                        @endif
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Botão --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/egresso/minhas-candidaturas') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    Ver Minhas Candidaturas
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Obrigado por usar o <strong>UniLuanda Alumni Track</strong>!
    </p>

@endsection