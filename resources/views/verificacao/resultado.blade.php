@extends('layouts.simple')

@section('title', ($valido ? 'Documento Válido' : 'Documento Inválido') . ' — UniLuanda Alumni')

@section('content')

<div class="container">
    <div class="result-card">

        {{-- HEADER --}}
        <div class="result-header {{ $valido ? 'valid' : 'invalid' }}">
            <div class="result-icon">
                <i class="fas fa-{{ $valido ? 'check-circle' : 'times-circle' }}"></i>
            </div>
            <h1 class="result-title">
                {{ $valido ? 'Documento Válido' : 'Documento Inválido' }}
            </h1>
            <p class="result-subtitle">
                @if($valido)
                    Este documento é autêntico e foi emitido pelo sistema Alumni Track.
                @else
                    {{ $motivo ?? 'Não foi possível verificar este documento.' }}
                @endif
            </p>
        </div>


        {{-- BODY --}}
        <div class="result-body">

            {{-- Código verificado --}}
            <div class="codigo-box">
                <small>Código Verificado</small>
                <strong>{{ $codigo }}</strong>
            </div>


            @if($valido)

                {{-- Badge do tipo --}}
                <div class="text-center">
                    <span class="doc-type-badge">
                        <i class="fas fa-{{ $tipo === 'certificado' ? 'certificate' : 'file-alt' }} me-1"></i>
                        {{ $tipo === 'certificado' ? 'Certificado' : 'Comprovativo de Inscrição' }}
                    </span>
                </div>


                {{-- Info grid --}}
                <div class="info-grid">

                    <div class="info-row">
                        <div class="info-row-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-row-content">
                            <small>Participante</small>
                            <strong>{{ $egresso->nome_completo ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row-icon">
                            <i class="fas fa-calendar-star"></i>
                        </div>
                        <div class="info-row-content">
                            <small>Evento</small>
                            <strong>{{ $evento->titulo ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($evento->data_inicio ?? null)
                        <div class="info-row">
                            <div class="info-row-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="info-row-content">
                                <small>Data do Evento</small>
                                <strong>
                                    {{ $evento->data_inicio->translatedFormat('d \d\e F \d\e Y') }}
                                    @if($evento->data_inicio->format('H:i') !== '00:00')
                                        · {{ $evento->data_inicio->format('H:i') }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    @endif

                    @if($evento->local ?? null)
                        <div class="info-row">
                            <div class="info-row-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-row-content">
                                <small>Local</small>
                                <strong>{{ $evento->local }}</strong>
                            </div>
                        </div>
                    @endif

                    <div class="info-row">
                        <div class="info-row-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="info-row-content">
                            <small>Nº de Inscrição</small>
                            <strong>#{{ str_pad($inscricao->id, 6, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-row-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-row-content">
                            <small>Emitido em</small>
                            <strong>
                                {{ $inscricao->created_at->translatedFormat('d \d\e F \d\e Y \à\s H:i') }}
                            </strong>
                        </div>
                    </div>

                </div>

                <div class="footer-note">
                    Verificado em {{ now()->format('d/m/Y \à\s H:i') }}
                    <br>
                    <strong>UniLuanda Alumni Track</strong> · Sistema de Verificação
                </div>

            @else

                <div class="alert-invalid">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Motivo:</strong> {{ $motivo }}
                    <br><br>
                    Se acredita que isto é um erro, contacte a coordenação Alumni
                    e forneça o código verificado.
                </div>

            @endif

            <div class="text-center">
                <a href="{{ route('verificacao.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Verificar outro documento
                </a>
            </div>

        </div>
    </div>
</div>

@endsection