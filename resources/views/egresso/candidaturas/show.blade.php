@extends('layouts.egresso')

@section('title', 'Detalhes da Candidatura')

@section('page_title', '📄 Detalhes da Candidatura')
@section('page_subtitle', $candidatura->oportunidade->titulo ?? 'Oportunidade')

@section('content')

@php
    $oportunidade = $candidatura->oportunidade;
    $status       = strtolower($candidatura->status ?? 'pendente');

    $statusMap = [
        'pendente'   => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
        'aprovado'   => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
        'recusado'   => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Recusado'],
        'rejeitado'  => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
        'entrevista' => ['color' => 'info',    'icon' => 'user-tie',     'label' => 'Entrevista'],
        'em_analise' => ['color' => 'primary', 'icon' => 'search',       'label' => 'Em Análise'],
    ];
    $statusInfo = $statusMap[$status] ?? ['color' => 'secondary', 'icon' => 'info-circle', 'label' => ucfirst($status)];

    $temEntrevista = !empty($candidatura->data_entrevista);
@endphp


<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

     

        {{-- HEADER --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="detail-header header-{{ $statusInfo['color'] }}">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="service-big-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 fw-bold text-white">
                            {{ $oportunidade->titulo ?? 'Oportunidade' }}
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-building me-1"></i>
                            {{ $oportunidade->empresa ?? 'Empresa não especificada' }}
                        </p>
                    </div>
                    <span class="status-pill">
                        <i class="fas fa-{{ $statusInfo['icon'] }}"></i>
                        {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">

                {{-- ============================================
                     BLOCO DA ENTREVISTA
                ============================================ --}}
                @if($temEntrevista)
                    <div class="entrevista-box mb-4">
                        <div class="entrevista-box-header">
                            <div class="entrevista-box-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-white">Entrevista Marcada</h5>
                                <small class="text-white-50">Detalhes da tua entrevista</small>
                            </div>
                        </div>

                        <div class="entrevista-box-body">

                            {{-- DATA + LOCAL --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="entrevista-detail">
                                        <div class="entrevista-detail-icon">
                                            <i class="far fa-calendar"></i>
                                        </div>
                                        <div>
                                            <small>Data e Hora</small>
                                            <strong>
                                                {{ $candidatura->data_entrevista->format('d/m/Y \à\s H:i') }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="entrevista-detail">
                                        <div class="entrevista-detail-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <small>Local</small>
                                            <strong>
                                                {{ $candidatura->local_entrevista ?? 'A confirmar' }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- AÇÕES DA ENTREVISTA --}}
                            <div class="entrevista-actions mt-3">
                                <a href="{{ route('egresso.candidaturas.entrevista.ics', $candidatura->id) }}"
                                   class="btn btn-light btn-sm">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Adicionar ao Calendário
                                </a>

                                @if($candidatura->local_entrevista)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($candidatura->local_entrevista) }}"
                                       target="_blank"
                                       class="btn btn-light btn-sm">
                                        <i class="fas fa-map me-1"></i>
                                        Ver no Mapa
                                    </a>
                                @endif
                            </div>

                            {{-- ✅ CONFIRMAÇÃO DE PRESENÇA --}}
                            <div class="entrevista-confirmacao mt-3">

                                @if($candidatura->confirmado_pelo_egresso)

                                    <div class="confirmacao-box confirmacao-ok">
                                        <div class="confirmacao-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="confirmacao-body">
                                            <strong>Presença confirmada</strong>
                                            @if($candidatura->confirmado_em)
                                                <small>
                                                    Confirmaste em
                                                    {{ $candidatura->confirmado_em->format('d/m/Y \à\s H:i') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>

                                @else

                                    <div class="confirmacao-box confirmacao-pendente">
                                        <div class="confirmacao-icon">
                                            <i class="fas fa-hourglass-half"></i>
                                        </div>
                                        <div class="confirmacao-body">
                                            <strong>Ainda não confirmaste a tua presença</strong>
                                            <small>Confirma que vais comparecer à entrevista.</small>
                                        </div>

                                        <form action="{{ route('egresso.candidaturas.confirmar', $candidatura->id) }}"
                                              method="POST"
                                              class="ms-auto form-confirmar-presenca">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>
                                                Confirmar Presença
                                            </button>
                                        </form>
                                    </div>

                                @endif

                            </div>

                            {{-- NOTA se o status já não for entrevista --}}
                            @if($status !== 'entrevista')
                                <div class="mt-3 small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Esta entrevista foi marcada, mas o estado atual da candidatura é
                                    <strong>{{ $statusInfo['label'] }}</strong>.
                                </div>
                            @endif

                        </div>
                    </div>
                @endif

                {{-- ============================================
                     DADOS DA CANDIDATURA
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-info-circle"></i> Detalhes da Candidatura
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="far fa-calendar"></i></div>
                                <div class="info-tile-body">
                                    <small>Data da Candidatura</small>
                                    <strong>{{ $candidatura->created_at->format('d/m/Y \à\s H:i') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-tag"></i></div>
                                <div class="info-tile-body">
                                    <small>Tipo</small>
                                    <strong>{{ ucfirst($oportunidade->tipo ?? 'Não especificado') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- MENSAGEM MOTIVACIONAL --}}
                @if($candidatura->mensagem_motivacional)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-comment-alt"></i> Mensagem Motivacional
                        </h6>
                        <div class="description-box">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="mb-0">{!! nl2br(e($candidatura->mensagem_motivacional)) !!}</p>
                        </div>
                    </section>
                @endif

                {{-- MOTIVO DA REJEIÇÃO --}}
                @if($candidatura->motivo_rejeicao)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-info-circle"></i> Motivo da Rejeição
                        </h6>
                        <div class="alert alert-danger mb-0">
                            {!! nl2br(e($candidatura->motivo_rejeicao)) !!}
                        </div>
                    </section>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

