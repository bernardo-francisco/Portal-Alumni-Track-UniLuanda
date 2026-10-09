@extends('layouts.egresso')

@section('title', 'Meus Feedbacks')

@section('page_title', '💬 Meus Feedbacks')
@section('page_subtitle', 'Acompanhe o status dos seus feedbacks')

@section('content')

@php
    $totalFeedbacks = $feedbacks->total() ?? 0;

    // Contadores por estado
    $totalPendentes = 0;
    $totalAprovados = 0;
    $totalRejeitados = 0;

    foreach ($feedbacks as $fb) {
        if ($fb->status === 'pendente')  $totalPendentes++;
        if ($fb->status === 'aprovado')  $totalAprovados++;
        if ($fb->status === 'rejeitado') $totalRejeitados++;
    }
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total de Feedbacks</div>
                <div class="stat-card-value">{{ $totalFeedbacks }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-database"></i>
                    Enviados por si
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Pendentes</div>
                <div class="stat-card-value">{{ $totalPendentes }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-hourglass-half"></i>
                    A aguardar análise
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Aprovados</div>
                <div class="stat-card-value">{{ $totalAprovados }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-thumbs-up"></i>
                    Publicados na comunidade
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Rejeitados</div>
                <div class="stat-card-value">{{ $totalRejeitados }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-ban"></i>
                    Não publicados
                </div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     ALERTAS
============================================================ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- ============================================================
     AÇÕES
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-list text-primary me-2"></i>
            Histórico de Feedbacks
        </h6>
        <small class="text-muted">
            {{ $totalFeedbacks }} {{ $totalFeedbacks === 1 ? 'feedback enviado' : 'feedbacks enviados' }}
        </small>
    </div>

    <a href="{{ route('egresso.feedbacks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Novo Feedback
    </a>
</div>


{{-- ============================================================
     LISTA DE FEEDBACKS
============================================================ --}}
@if($feedbacks->isEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-comment-slash"></i>
            </div>
            <h5 class="fw-bold mb-2">Nenhum feedback enviado</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                Ainda não enviou nenhum feedback. Partilhe a sua opinião
                sobre o curso e ajude a melhorar a comunidade.
            </p>
            <a href="{{ route('egresso.feedbacks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Enviar Primeiro Feedback
            </a>
        </div>
    </div>

@else

    <div class="row g-3">

        @foreach($feedbacks as $feedback)
            @php
                $statusMap = [
                    'pendente'  => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
                    'aprovado'  => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
                    'rejeitado' => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
                ];
                $statusInfo = $statusMap[$feedback->status] ?? [
                    'color' => 'secondary',
                    'icon'  => 'circle',
                    'label' => ucfirst($feedback->status),
                ];

                $hasNota = !is_null($feedback->nota) && $feedback->nota > 0;
            @endphp

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 feedback-list-card">

                    {{-- ============================================================
                         HEADER
                    ============================================================ --}}
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-{{ $statusInfo['color'] }}-subtle text-{{ $statusInfo['color'] }}-emphasis border border-{{ $statusInfo['color'] }}-subtle">
                                    <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                                    {{ $statusInfo['label'] }}
                                </span>

                                @if($hasNota)
                                    <div class="rating-mini">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $feedback->nota ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                                        @endfor
                                        <span class="ms-1 text-muted small fw-bold">{{ $feedback->nota }}/5</span>
                                    </div>
                                @endif
                            </div>

                            <small class="text-muted flex-shrink-0">
                                <i class="far fa-clock me-1"></i>
                                {{ $feedback->created_at->diffForHumans() }}
                            </small>

                        </div>
                    </div>

                    {{-- ============================================================
                         BODY
                    ============================================================ --}}
                    <div class="card-body p-4">

                        {{-- Curso --}}
                        @if($feedback->curso)
                            <span class="badge bg-light text-dark border mb-2">
                                <i class="fas fa-graduation-cap me-1"></i>
                                {{ $feedback->curso->nome }}
                            </span>
                        @endif

                        {{-- Título --}}
                        <h6 class="fw-bold text-dark mb-2 feedback-title">
                            {{ $feedback->titulo }}
                        </h6>

                        {{-- Preview da mensagem --}}
                        <p class="text-muted small mb-0 feedback-preview">
                            {{ Str::limit($feedback->mensagem, 180) }}
                        </p>

                    </div>

                    {{-- ============================================================
                         FOOTER
                    ============================================================ --}}
                    <div class="card-footer bg-white border-top py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">

                            <small class="text-muted">
                                <i class="far fa-calendar me-1" style="font-size: 0.7rem;"></i>
                                {{ $feedback->created_at->format('d/m/Y') }}
                            </small>

                            <a href="{{ route('egresso.feedbacks.show', $feedback->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

    {{-- ============================================================
         PAGINAÇÃO
    ============================================================ --}}
    @if(method_exists($feedbacks, 'hasPages') && $feedbacks->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $feedbacks->appends(request()->query())->links() }}
        </div>
    @endif

@endif

@endsection

