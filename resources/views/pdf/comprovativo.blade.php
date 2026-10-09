@extends('layouts.pdf')

{{-- ============================================================
     METADADOS
============================================================ --}}
@section('title', 'Comprovativo de Inscrição — ' . $inscricao->evento->titulo)

@section('report_name', 'Comprovativo de Inscrição')

@section('header_meta', 'Evento: ' . $inscricao->evento->titulo)

@section('footer_right', 'UniLuanda — Comprovativo Oficial')

{{-- ============================================================
     CONTEÚDO
============================================================ --}}
@section('content')

@php
    $egresso = $inscricao->egresso;
    $evento  = $inscricao->evento;

    $dataFormatada = $evento->data_inicio
        ? $evento->data_inicio->translatedFormat('d \d\e F \d\e Y')
        : 'Data a definir';

    $hora = $evento->data_inicio ? $evento->data_inicio->format('H:i') : '';

    // Código: usa o guardado na DB
    $codigo = $inscricao->codigo_comprovativo
        ?? strtoupper(substr(md5($inscricao->id . $egresso->id . $evento->id), 0, 12));

    $urlVerificacao = url("/verificar/{$codigo}");
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($urlVerificacao);

    // Estado dinâmico
    $statusConfig = match($inscricao->status) {
        'confirmada' => [
            'class' => 'confirmed',
            'icon'  => '✓',
            'text'  => 'Inscrição Confirmada',
        ],
        'rejeitada' => [
            'class' => 'rejected',
            'icon'  => '✕',
            'text'  => 'Inscrição Rejeitada',
        ],
        'pendente' => [
            'class' => 'pending',
            'icon'  => '⏳',
            'text'  => 'Inscrição Pendente',
        ],
        default => [
            'class' => 'pending',
            'icon'  => '?',
            'text'  => ucfirst($inscricao->status),
        ],
    };
@endphp

{{-- Título do documento --}}
<div class="doc-title">Comprovativo de Inscrição</div>
<div class="doc-subtitle">
    Documento emitido automaticamente pelo sistema UniLuanda Alumni Track
</div>

{{-- Estado dinâmico --}}
<div class="status-box {{ $statusConfig['class'] }}">
    <div class="status-icon">{{ $statusConfig['icon'] }}</div>
    <div class="status-text">{{ $statusConfig['text'] }}</div>
</div>

{{-- Dados do egresso --}}
<div class="info-card">
    <div class="label">Participante</div>
    <div class="value">{{ $egresso->nome_completo }}</div>

    @if($egresso->email)
        <div class="label">Email</div>
        <div class="value">{{ $egresso->email }}</div>
    @endif

    @if($egresso->numero_processo)
        <div class="label">Nº de Processo</div>
        <div class="value">{{ $egresso->numero_processo }}</div>
    @endif
</div>

{{-- Detalhes do evento --}}
<table class="details-table">
    <tr>
        <td>Evento</td>
        <td>{{ $evento->titulo }}</td>
    </tr>
    <tr>
        <td>Tipo</td>
        <td>{{ ucfirst($evento->tipo ?? 'presencial') }}</td>
    </tr>
    @if($evento->categoria)
        <tr>
            <td>Categoria</td>
            <td>{{ ucfirst(str_replace('_', ' ', $evento->categoria)) }}</td>
        </tr>
    @endif
    <tr>
        <td>Data</td>
        <td>{{ $dataFormatada }} @if($hora) · {{ $hora }} @endif</td>
    </tr>
    @if($evento->data_fim && $evento->data_fim != $evento->data_inicio)
        <tr>
            <td>Data de Fim</td>
            <td>{{ $evento->data_fim->translatedFormat('d \d\e F \d\e Y · H:i') }}</td>
        </tr>
    @endif
    @if($evento->local)
        <tr>
            <td>Local</td>
            <td>{{ $evento->local }}</td>
        </tr>
    @endif
    @if($evento->link_reuniao && $evento->tipo !== 'presencial')
        <tr>
            <td>Link de Acesso</td>
            <td style="word-break: break-all;">{{ $evento->link_reuniao }}</td>
        </tr>
    @endif
    <tr>
        <td>Data de Inscrição</td>
        <td>{{ $inscricao->created_at->translatedFormat('d \d\e F \d\e Y \à\s H:i') }}</td>
    </tr>
    <tr>
        <td>Nº da Inscrição</td>
        <td>#{{ str_pad($inscricao->id, 6, '0', STR_PAD_LEFT) }}</td>
    </tr>
</table>

{{-- Código + QR Code (apenas se confirmada) --}}
@if($inscricao->status === 'confirmada' && $codigo)
    <div class="codigo-box">
        <table>
            <tr>
                <td class="codigo-left">
                    <div class="codigo-label">Código de Verificação</div>
                    <div class="codigo-value">{{ $codigo }}</div>
                    <div class="codigo-help">
                        Verifique em:<br>
                        <strong>{{ $urlVerificacao }}</strong>
                    </div>
                </td>
                <td class="codigo-right">
                    <img src="{{ $qrCodeUrl }}" alt="QR Code" class="qr-code">
                </td>
            </tr>
        </table>
    </div>
@endif

{{-- Aviso --}}
<div class="aviso">
    <strong>⚠️ Importante</strong>

    @if($inscricao->status === 'confirmada')
        Apresente este comprovativo no dia do evento, juntamente com um documento de identificação válido.
    @elseif($inscricao->status === 'rejeitada')
        Esta inscrição foi rejeitada. Contacte a organização para mais informações.
    @else
        Esta inscrição está pendente de aprovação. O comprovativo definitivo será emitido após confirmação.
    @endif

    Este documento é gerado automaticamente e não necessita de assinatura.
</div>

@endsection