@extends('layouts.egresso')

@section('title', $oportunidade->titulo ?? 'Detalhes da Oportunidade')

@section('page_title', '💼 Detalhes da Oportunidade')
@section('page_subtitle', $oportunidade->empresa ?? '')

@section('content')

@php
    $tipo = strtolower($oportunidade->tipo ?? 'oportunidade');

    $tiposMap = [
        'emprego'  => ['color' => 'primary',   'icon' => 'briefcase',       'label' => 'Emprego'],
        'estagio'  => ['color' => 'info',      'icon' => 'user-graduate',   'label' => 'Estágio'],
        'estágio'  => ['color' => 'info',      'icon' => 'user-graduate',   'label' => 'Estágio'],
        'bolsa'    => ['color' => 'success',   'icon' => 'award',           'label' => 'Bolsa'],
        'curso'    => ['color' => 'warning',   'icon' => 'book',            'label' => 'Curso'],
        'evento'   => ['color' => 'secondary', 'icon' => 'calendar',        'label' => 'Evento'],
        'empresa'  => ['color' => 'primary',   'icon' => 'building',        'label' => 'Empresa'],
    ];
    $tipoInfo = $tiposMap[$tipo] ?? ['color' => 'secondary', 'icon' => 'star', 'label' => ucfirst($tipo)];
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
                            {{ $oportunidade->titulo }}
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-building me-1"></i>
                            {{ $oportunidade->empresa ?? 'Empresa não especificada' }}
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <span class="status-pill">
                            <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                            {{ $tipoInfo['label'] }}
                        </span>

                        {{-- ✅ NOVO — Badge de unidade --}}
                        @if($oportunidade->unidade)
                            <span class="status-pill">
                                <i class="fas fa-university"></i>
                                {{ $oportunidade->unidade->sigla }}
                            </span>
                        @else
                            <span class="status-pill">
                                <i class="fas fa-globe"></i>
                                Todas as Unidades
                            </span>
                        @endif

                        @if($jaCandidatou)
                            <span class="status-pill status-pill-success">
                                <i class="fas fa-check-circle"></i> Candidatado
                            </span>
                        @elseif($expirado)
                            <span class="status-pill status-pill-danger">
                                <i class="fas fa-lock"></i> Expirado
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
                        @if($oportunidade->empresa)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-building"></i></div>
                                    <div class="info-tile-body">
                                        <small>Empresa</small>
                                        <strong>{{ $oportunidade->empresa }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($oportunidade->localizacao || $oportunidade->local)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="info-tile-body">
                                        <small>Localização</small>
                                        <strong>{{ $oportunidade->localizacao ?? $oportunidade->local }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($oportunidade->data_limite)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="info-tile-body">
                                        <small>Prazo de Candidatura</small>
                                        <strong>
                                            {{ \Carbon\Carbon::parse($oportunidade->data_limite)->format('d/m/Y') }}
                                        </strong>
                                        @if($diasAte !== null && $diasAte >= 0)
                                            <small class="text-{{ $diasAte <= 3 ? 'danger' : 'muted' }} d-block mt-1">
                                                {{ $diasAte == 0 ? 'Último dia!' : $diasAte . ' dias restantes' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($oportunidade->unidade)
                            <div class="col-md-6">
                                <div class="info-tile">
                                    <div class="info-tile-icon"><i class="fas fa-university"></i></div>
                                    <div class="info-tile-body">
                                        <small>Unidade Orgânica</small>
                                        <strong>{{ $oportunidade->unidade->sigla ?? $oportunidade->unidade->nome }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-users"></i></div>
                                <div class="info-tile-body">
                                    <small>Candidaturas</small>
                                    <strong>{{ $totalCandidaturas }} {{ $totalCandidaturas == 1 ? 'pessoa' : 'pessoas' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-tile-icon"><i class="fas fa-clock"></i></div>
                                <div class="info-tile-body">
                                    <small>Publicado</small>
                                    <strong>{{ $oportunidade->created_at->diffForHumans() }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ============================================
                     DESCRIÇÃO COMPLETA
                ============================================ --}}
                @if($oportunidade->descricao)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-align-left"></i> Descrição
                        </h6>
                        <div class="description-box">
                            {!! nl2br(e($oportunidade->descricao)) !!}
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     REQUISITOS (se existir)
                ============================================ --}}
                @if(!empty($oportunidade->requisitos))
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-list-check"></i> Requisitos
                        </h6>
                        <div class="description-box">
                            {!! nl2br(e($oportunidade->requisitos)) !!}
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     BENEFÍCIOS (se existir)
                ============================================ --}}
                @if(!empty($oportunidade->beneficios))
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-gift"></i> Benefícios
                        </h6>
                        <div class="description-box">
                            {!! nl2br(e($oportunidade->beneficios)) !!}
                        </div>
                    </section>
                @endif

                {{-- ============================================
                     AÇÃO — CANDIDATAR
                ============================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-paper-plane"></i> Candidatura
                    </h6>

                    @if($jaCandidatou)
                        <div class="alert alert-success d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <div>
                                <strong>Já te candidataste a esta oportunidade!</strong>
                                <p class="mb-0 small">
                                    Acompanha o estado da tua candidatura em
                                    <a href="{{ route('egresso.minhas.candidaturas') }}" class="fw-bold">
                                        Minhas Candidaturas
                                    </a>.
                                </p>
                            </div>
                        </div>

                    @elseif($expirado)
                        <div class="alert alert-secondary d-flex align-items-center gap-3 mb-0">
                            <i class="fas fa-lock fa-2x"></i>
                            <div>
                                <strong>Prazo expirado</strong>
                                <p class="mb-0 small">As candidaturas para esta oportunidade já encerraram.</p>
                            </div>
                        </div>

                    @else
                        <form action="{{ route('egresso.oportunidades.candidatar', ['oportunidade' => $oportunidade->id]) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              onsubmit="return confirmarCandidatura(this);">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Mensagem Motivacional
                                    <small class="text-muted">(opcional)</small>
                                </label>
                                <textarea name="mensagem_motivacional"
                                          class="form-control"
                                          rows="4"
                                          maxlength="1000"
                                          placeholder="Explica porque te candidatas a esta oportunidade...">{{ old('mensagem_motivacional') }}</textarea>
                                <small class="text-muted">Máx. 1000 caracteres.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Currículo
                                    <small class="text-muted">(PDF, DOC ou DOCX · máx. 5MB)</small>
                                </label>
                                <input type="file"
                                       name="cv_anexo"
                                       class="form-control"
                                       accept=".pdf,.doc,.docx">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>
                                Candidatar-se agora
                            </button>
                        </form>
                    @endif
                </section>

            </div>
        </div>

    </div>
</div>

@endsection

