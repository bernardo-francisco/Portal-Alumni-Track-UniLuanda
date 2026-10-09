@extends('layouts.pdf')

{{-- ============================================================
     METADADOS
============================================================ --}}
@section('title', 'Certificado — ' . $evento->titulo)

@section('report_name', 'Certificado de Participação')

@section('header_meta', 'Evento: ' . $evento->titulo)

@section('footer_right', 'UniLuanda — Certificado Oficial')

{{-- ============================================================
     CONTEÚDO
============================================================ --}}
@section('content')

@php
    $urlVerificacao = url("/verificar/{$codigo}");
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($urlVerificacao);
@endphp

{{-- QR Code + código de verificação --}}
<div class="qr-code">
    <img src="{{ $qrCodeUrl }}" alt="QR Code">
    <div class="qr-code-label">Código de verificação</div>
    <strong class="qr-code-value">{{ $codigo }}</strong>
    <span class="qr-code-url">{{ $urlVerificacao }}</span>
</div>

{{-- Corpo do certificado --}}
<div class="certificado-wrapper">

    <div class="certificado-selo">
        <i class="fas fa-certificate"></i>
    </div>

    <p class="certificado-intro">A Universidade de Luanda certifica que</p>

    <div class="certificado-nome">
        {{ $inscricao->egresso->nome_completo }}
    </div>

    <div class="certificado-corpo">
        participou com êxito no evento

        <div class="certificado-evento">
            <div class="certificado-evento-titulo">
                {{ $evento->titulo }}
            </div>
        </div>

        @if($evento->descricao)
            <div style="font-size:11px;color:#525252;padding:0 60px;margin-top:15px;">
                {{ \Illuminate\Support\Str::limit($evento->descricao, 200) }}
            </div>
        @endif

        <div class="certificado-info">
            <strong>Data:</strong>
            @if($evento->data_inicio)
                {{ $evento->data_inicio->translatedFormat('d \d\e F \d\e Y') }}
                @if($evento->data_fim && $evento->data_fim != $evento->data_inicio)
                    · até {{ $evento->data_fim->translatedFormat('d \d\e F \d\e Y') }}
                @endif
            @endif

            @if($evento->local)
                <br><strong>Local:</strong> {{ $evento->local }}
            @endif

            @if($evento->categoria)
                · <strong>Categoria:</strong> {{ ucfirst(str_replace('_', ' ', $evento->categoria)) }}
            @endif
        </div>
    </div>

    {{-- Assinaturas --}}
    <div class="certificado-assinaturas">
        <div class="certificado-assinatura">
            <div class="certificado-assinatura-linha">
                <div class="certificado-assinatura-nome">Coordenação Alumni</div>
                <div class="certificado-assinatura-cargo">Universidade de Luanda</div>
            </div>
        </div>

        <div class="certificado-assinatura">
            <div class="certificado-assinatura-linha">
                <div class="certificado-assinatura-nome">{{ now()->format('d/m/Y') }}</div>
                <div class="certificado-assinatura-cargo">Data de Emissão</div>
            </div>
        </div>
    </div>

    {{-- Código de verificação (rodapé do certificado) --}}
    <div class="certificado-codigo">
        <span>Código de verificação:</span>
        <strong>{{ $codigo }}</strong>
    </div>

</div>

@endsection