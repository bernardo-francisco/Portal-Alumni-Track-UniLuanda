@extends('layouts.egresso')

@section('title', $evento->titulo ?? 'Detalhes do Evento')

@section('page_title', '🎪 Detalhes do Evento')
@section('page_subtitle', $evento->titulo ?? '')

@section('content')

@php
    $tiposMap = [
        'presencial' => ['color' => 'success', 'icon' => 'building', 'label' => 'Presencial'],
        'online'     => ['color' => 'primary', 'icon' => 'wifi',     'label' => 'Online'],
        'hibrido'    => ['color' => 'warning', 'icon' => 'sync',     'label' => 'Híbrido'],
    ];

    $categoriasMap = [
        'workshop'   => ['color' => 'warning',   'icon' => 'tools',        'label' => 'Workshop'],
        'palestra'   => ['color' => 'primary',   'icon' => 'microphone',   'label' => 'Palestra'],
        'networking' => ['color' => 'success',   'icon' => 'handshake',    'label' => 'Networking'],
        'job_fair'   => ['color' => 'info',      'icon' => 'briefcase',    'label' => 'Feira de Emprego'],
        'curso'      => ['color' => 'secondary', 'icon' => 'book',         'label' => 'Curso'],
        'outro'      => ['color' => 'dark',      'icon' => 'star',         'label' => 'Outro'],
    ];

    $tipoInfo = $tiposMap[$evento->tipo] ?? ['color' => 'secondary', 'icon' => 'calendar', 'label' => ucfirst($evento->tipo)];
    $catInfo  = $categoriasMap[$evento->categoria] ?? ['color' => 'secondary', 'icon' => 'star', 'label' => ucfirst($evento->categoria ?? 'Evento')];

    $corProgresso = $percentual < 50 ? 'success' : ($percentual < 80 ? 'warning' : 'danger');
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">


        {{-- ============================================================
             HEADER
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="detail-header header-{{ $tipoInfo['color'] }}">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="service-big-icon">
                        <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 fw-bold text-white">
                            {{ $evento->titulo }}
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-{{ $catInfo['icon'] }} me-1"></i>
                            {{ $catInfo['label'] }}
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <span class="status-pill">
                            <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                            {{ $tipoInfo['label'] }}
                        </span>

                        {{-- ✅ NOVO — Badge de unidade --}}
                        @if($evento->unidade)
                            <span class="status-pill">
                                <i class="fas fa-university"></i>
                                {{ $evento->unidade->sigla }}
                            </span>
                        @else
                            <span class="status-pill">
                                <i class="fas fa-globe"></i>
                                Todas as Unidades
                            </span>
                        @endif

                        @if($jaInscrito)
                            <span class="status-pill status-pill-success">
                                <i class="fas fa-check-circle"></i> Inscrito
                            </span>
                        @elseif($passado)
                            <span class="status-pill status-pill-danger">
                                <i class="fas fa-clock"></i> Finalizado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-4 p-lg-5">

                {{-- ============================================
                     INFORMAÇÕES RÁPIDAS
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-info-circle"></i> Informações
                    </h6>

                    <div class="row g-3">

                        @if($evento->data_inicio)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-calendar-day"></i></div>
                                    <div class="info-tile-body">
                                        <small>Data e Hora</small>
                                        <strong>{{ $evento->data_inicio->format('d/m/Y \à\s H:i') }}</strong>
                                        @if($diasAte !== null && $diasAte >= 0)
                                            <small class="text-{{ $diasAte <= 3 ? 'danger' : 'muted' }} d-block mt-1">
                                                {{ $diasAte == 0 ? 'É hoje!' : 'Faltam ' . $diasAte . ' dias' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evento->data_fim)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-calendar-check"></i></div>
                                    <div class="info-tile-body">
                                        <small>Data de Término</small>
                                        <strong>{{ $evento->data_fim->format('d/m/Y \à\s H:i') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evento->local)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="info-tile-body">
                                        <small>Local</small>
                                        <strong>{{ $evento->local }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evento->link_reuniao)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-video"></i></div>
                                    <div class="info-tile-body">
                                        <small>Link de Acesso</small>
                                        <strong class="text-truncate d-block">
                                            {{ $evento->link_reuniao }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evento->unidade)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-university"></i></div>
                                    <div class="info-tile-body">
                                        <small>Unidade Orgânica</small>
                                        <strong>{{ $evento->unidade->sigla ?? $evento->unidade->nome }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($evento->data_limite_inscricao)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-hourglass-end"></i></div>
                                    <div class="info-tile-body">
                                        <small>Prazo de Inscrição</small>
                                        <strong>{{ $evento->data_limite_inscricao->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </section>

                {{-- ============================================
                     DESCRIÇÃO COMPLETA
                ============================================ --}}
                @if($evento->descricao)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-align-left"></i> Descrição
                        </h6>
                        <div class="description-box">
                            {!! nl2br(e($evento->descricao)) !!}
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     VAGAS / PROGRESSO
                ============================================ --}}
                @if($evento->vagas_totais)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-users"></i> Participação
                        </h6>

                        <div class="vagas-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong class="text-dark">
                                        {{ $totalInscritos }} / {{ $evento->vagas_totais }}
                                    </strong>
                                    <small class="text-muted ms-1">inscritos</small>
                                </div>
                                @if($lotado)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-times-circle me-1"></i> Lotado
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                        {{ $evento->vagas_totais - $totalInscritos }} vagas restantes
                                    </span>
                                @endif
                            </div>

                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div class="progress-bar bg-{{ $corProgresso }}"
                                     style="width: {{ min($percentual, 100) }}%; border-radius: 10px;">
                                </div>
                            </div>

                            <small class="text-muted d-block mt-2">
                                {{ $percentual }}% preenchido
                            </small>
                        </div>
                    </section>
                @else
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-users"></i> Participação
                        </h6>

                        <div class="vagas-box">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark">{{ $totalInscritos }}</strong>
                                    <small class="text-muted ms-1">
                                        {{ $totalInscritos == 1 ? 'inscrito' : 'inscritos' }}
                                    </small>
                                </div>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="fas fa-infinity me-1"></i> Vagas ilimitadas
                                </span>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     AÇÃO — INSCREVER-SE / CANCELAR
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-ticket-alt"></i> Inscrição
                    </h6>

                    @if($passado)

                        <div class="alert alert-secondary d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-clock fa-2x"></i>
                            <div>
                                <strong>Evento finalizado</strong>
                                <p class="mb-0 small">Este evento já aconteceu.</p>
                            </div>
                        </div>

                    @elseif($jaInscrito)

                        <div class="alert alert-success d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <div class="flex-grow-1">
                                <strong>Estás inscrito neste evento!</strong>
                                <p class="mb-0 small">
                                    Código do comprovativo:
                                    <code class="fw-bold">{{ $inscricao->codigo_comprovativo }}</code>
                                </p>
                            </div>
                            <a href="{{ route('egresso.eventos.comprovativo', $inscricao->id) }}"
                               class="btn btn-success">
                                <i class="fas fa-file-pdf me-1"></i> Comprovativo
                            </a>
                        </div>

                        <form action="{{ route('egresso.minhas.inscricoes.cancelar', $inscricao->id) }}"
                              method="POST"
                              class="mt-3"
                              onsubmit="return confirm('Tens a certeza que queres cancelar a inscrição?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-times me-1"></i> Cancelar inscrição
                            </button>
                        </form>

                    @elseif($lotado)

                        <div class="alert alert-danger d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-times-circle fa-2x"></i>
                            <div>
                                <strong>Vagas esgotadas</strong>
                                <p class="mb-0 small">Infelizmente, este evento já está lotado.</p>
                            </div>
                        </div>

                    @elseif($prazoFim)

                        <div class="alert alert-warning d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-hourglass-end fa-2x"></i>
                            <div>
                                <strong>Prazo de inscrição encerrado</strong>
                                <p class="mb-0 small">Já não é possível inscrever-se neste evento.</p>
                            </div>
                        </div>

                    @else

                        <form action="{{ route('egresso.eventos.inscrever', $evento->id) }}"
                              method="POST"
                              onsubmit="return confirmarInscricao(this);">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-plus-circle me-2"></i>
                                Inscrever-me agora
                                @if($diasAte !== null && $diasAte <= 3 && $diasAte >= 0)
                                    <span class="badge bg-white text-primary ms-2">
                                        {{ $diasAte == 0 ? 'Hoje' : $diasAte . 'd' }}
                                    </span>
                                @endif
                            </button>
                        </form>

                    @endif
                </section>

            </div>
        </div>

    </div>
</div>

@endsection

