@extends('emails.layouts.email')

@php
    $nome = $remetente->nome_completo ?? 'Alguém';

    $cfg = [
        'texto' => [
            'title'    => '💬 Nova Mensagem',
            'subtitle' => 'Tens uma nova mensagem no chat',
            'icone'    => 'fas fa-comment-dots',
            'cor'      => '#2563eb',
            'cor_bg'   => '#dbeafe',
            'label'    => 'Mensagem',
        ],
        'audio' => [
            'title'    => '🎤 Mensagem de Voz',
            'subtitle' => 'Recebeste uma mensagem de voz',
            'icone'    => 'fas fa-microphone',
            'cor'      => '#7c3aed',
            'cor_bg'   => '#ede9fe',
            'label'    => 'Mensagem de voz',
        ],
        'ficheiro' => [
            'title'    => '📎 Novo Ficheiro',
            'subtitle' => 'Recebeste um ficheiro no chat',
            'icone'    => 'fas fa-paperclip',
            'cor'      => '#16a34a',
            'cor_bg'   => '#dcfce7',
            'label'    => 'Ficheiro',
        ],
    ];
    $c = $cfg[$tipo] ?? $cfg['texto'];

    // Link para a conversa — admin vê em /admin/mensagens, egresso em /egresso/mensagens
    $ehAdmin = isset($destinatario->user) && ($destinatario->user->tipo ?? null) === 'admin';
    $linkConversa = $ehAdmin
        ? '/admin/mensagens/' . ($remetente->id ?? '')
        : '/egresso/mensagens/' . ($remetente->id ?? '');
@endphp

@section('title', $c['title'] . ' — Alumni Track')
@section('header_title', $c['title'])
@section('header_subtitle', $c['subtitle'])

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá!
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        <strong>{{ $nome }}</strong> enviou-te uma nova
        <strong style="color: {{ $c['cor'] }};">{{ strtolower($c['label']) }}</strong>
        no chat do Alumni Track.
    </p>

    {{-- Conteúdo da Mensagem --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 14px;">
                    <span style="display: inline-block; background: {{ $c['cor_bg'] }}; color: {{ $c['cor'] }}; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">
                        <i class="{{ $c['icone'] }} me-1"></i> {{ $c['label'] }}
                    </span>
                </p>

                @if($tipo === 'texto')
                    <p style="margin: 0; color: #1a1a1a; font-size: 15px; line-height: 1.7; font-style: italic; background: #ffffff; padding: 16px 20px; border-radius: 8px; border-left: 3px solid {{ $c['cor'] }};">
                        "{{ \Illuminate\Support\Str::limit($conteudo, 300) }}"
                    </p>
                @elseif($tipo === 'audio')
                    <p style="margin: 0; color: #525252; font-size: 14px;">
                        🎤 Enviou-lhe uma <strong>mensagem de voz</strong>. Acede ao chat para ouvir.
                    </p>
                @elseif($tipo === 'ficheiro')
                    <p style="margin: 0; color: #525252; font-size: 14px;">
                        📎 Enviou o ficheiro: <strong style="color: #0a0a0a;">{{ $conteudo }}</strong>
                    </p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Botão --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url($linkConversa) }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    💬 Ver Conversa
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Este é um aviso automático. Acede ao chat para responder.
    </p>

@endsection