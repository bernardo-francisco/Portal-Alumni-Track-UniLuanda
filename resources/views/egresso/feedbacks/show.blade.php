@extends('layouts.egresso')

@section('title', 'Detalhes do Feedback')

@section('page_title', '💬 Detalhes do Feedback')
@section('page_subtitle', $feedback->titulo)

@section('content')

@php
    $statusMap = [
        'pendente'  => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
        'aprovado'  => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
        'rejeitado' => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
    ];

    $statusInfo = $statusMap[$feedback->status] ?? ($feedback->status_label ?? [
        'color' => 'secondary',
        'icon'  => 'circle',
        'label' => ucfirst($feedback->status),
    ]);

    $hasNota = !is_null($feedback->nota) && $feedback->nota > 0;
@endphp

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- ============================================================
             HERO / HEADER
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4 feedback-detail-card">
            <div class="card-body p-4">

                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

                    <div class="d-flex align-items-start gap-3 flex-grow-1 min-width-0">

                        <div class="feedback-detail-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>

                        <div class="flex-grow-1 min-width-0">
                            <h5 class="fw-bold mb-2 text-dark">{{ $feedback->titulo }}</h5>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($feedback->curso)
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        {{ $feedback->curso->nome }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        Curso não informado
                                    </span>
                                @endif

                                <span class="badge bg-{{ $statusInfo['color'] }}-subtle text-{{ $statusInfo['color'] }}-emphasis border border-{{ $statusInfo['color'] }}-subtle">
                                    <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                                    {{ $statusInfo['label'] }}
                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="text-end flex-shrink-0">
                        <small class="text-muted d-block">
                            <i class="far fa-calendar me-1"></i>
                            {{ $feedback->created_at->format('d/m/Y') }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="far fa-clock me-1"></i>
                            {{ $feedback->created_at->format('H:i') }}
                        </small>
                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             AVALIAÇÃO (ESTRELAS)
        ============================================================ --}}
        @if($hasNota)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">A sua Avaliação</h6>
                                <small class="text-muted">Classificação atribuída ao curso</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="rating-display">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $feedback->nota ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                @endfor
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                {{ $feedback->nota }}/5
                            </span>
                        </div>

                    </div>

                </div>
            </div>
        @endif


        {{-- ============================================================
             MENSAGEM
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Mensagem</h6>
                        <small class="text-muted">O conteúdo do seu feedback</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="feedback-message-box">
                    {!! nl2br(e($feedback->mensagem)) !!}
                </div>
            </div>
        </div>


        {{-- ============================================================
             NOTA DE ESTADO (se aplicável)
        ============================================================ --}}
        @if($feedback->status === 'aprovado')
            <div class="alert alert-success d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                <div class="alert-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <strong class="d-block">Feedback aprovado!</strong>
                    <small class="text-muted">
                        O seu feedback foi aprovado pela administração e pode ser publicado na comunidade.
                    </small>
                </div>
            </div>
        @elseif($feedback->status === 'rejeitado')
            <div class="alert alert-danger d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                <div class="alert-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <strong class="d-block">Feedback rejeitado</strong>
                    <small class="text-muted">
                        O seu feedback foi rejeitado pela administração.
                        @if($feedback->motivo_rejeicao ?? false)
                            <br>Motivo: {{ $feedback->motivo_rejeicao }}
                        @endif
                    </small>
                </div>
            </div>
        @elseif($feedback->status === 'pendente')
            <div class="alert alert-warning d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                <div class="alert-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <strong class="d-block">Aguarda análise</strong>
                    <small class="text-muted">
                        O seu feedback está a ser analisado pela administração.
                        Será notificado quando houver uma decisão.
                    </small>
                </div>
            </div>
        @endif


        {{-- ============================================================
             META INFORMAÇÕES
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-md-4 col-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <small class="text-muted d-block">Enviado em</small>
                            <strong class="small">
                                {{ $feedback->created_at->format('d/m/Y H:i') }}
                            </strong>
                        </div>
                    </div>

                    @if($feedback->updated_at && $feedback->updated_at != $feedback->created_at)
                        <div class="col-md-4 col-6">
                            <div class="meta-box">
                                <div class="meta-box-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <small class="text-muted d-block">Atualizado em</small>
                                <strong class="small">
                                    {{ $feedback->updated_at->format('d/m/Y H:i') }}
                                </strong>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-4 col-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <small class="text-muted d-block">ID do Feedback</small>
                            <strong class="small">#{{ $feedback->id }}</strong>
                        </div>
                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             AÇÕES
        ============================================================ --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                    <a href="{{ route('egresso.feedbacks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar aos Feedbacks
                    </a>

                    @if($feedback->status === 'rejeitado')
                        <a href="{{ route('egresso.feedbacks.create') }}" class="btn btn-primary">
                            <i class="fas fa-redo me-1"></i> Enviar Novo Feedback
                        </a>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

