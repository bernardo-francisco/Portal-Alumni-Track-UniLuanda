@extends('layouts.admin')

@section('title', 'Feedbacks dos Egressos')

@section('page_title', '💬 Feedbacks dos Egressos')
@section('page_subtitle', 'Gerencie os feedbacks enviados pela comunidade')

@section('content')

@php
    $totalPendentes = $pendentes ?? 0;
    $totalAprovados = $aprovados ?? 0;
    $totalRejeitados = $rejeitados ?? 0;
    $totalGeral = $totalPendentes + $totalAprovados + $totalRejeitados;
    $taxaAprovacao = ($totalAprovados + $totalRejeitados) > 0
        ? round(($totalAprovados / ($totalAprovados + $totalRejeitados)) * 100, 1)
        : 0;
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.feedbacks.index', ['status' => 'pendente']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-warning {{ $status === 'pendente' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalPendentes }}</div>
                    <div class="stat-label">Pendentes</div>
                    <small class="stat-desc">A aguardar análise</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.feedbacks.index', ['status' => 'aprovado']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-success {{ $status === 'aprovado' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalAprovados }}</div>
                    <div class="stat-label">Aprovados</div>
                    <small class="stat-desc">Publicados</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.feedbacks.index', ['status' => 'rejeitado']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-danger {{ $status === 'rejeitado' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalRejeitados }}</div>
                    <div class="stat-label">Rejeitados</div>
                    <small class="stat-desc">Não publicados</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $taxaAprovacao }}%</div>
                <div class="stat-label">Taxa de Aprovação</div>
                <small class="stat-desc">Baseado nas decisões</small>
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
     FILTROS RÁPIDOS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span class="text-muted small me-2">
                <i class="fas fa-filter me-1"></i> Filtrar:
            </span>

            <a href="{{ route('admin.feedbacks.index', ['status' => 'todos']) }}"
               class="btn btn-sm {{ $status === 'todos' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                <i class="fas fa-list me-1"></i> Todos
                @if($totalGeral > 0)
                    <span class="badge bg-light text-dark ms-1">{{ $totalGeral }}</span>
                @endif
            </a>

            <a href="{{ route('admin.feedbacks.index', ['status' => 'pendente']) }}"
               class="btn btn-sm {{ $status === 'pendente' ? 'btn-warning' : 'btn-outline-warning' }}">
                <i class="fas fa-clock me-1"></i> Pendentes
                @if($totalPendentes > 0)
                    <span class="badge bg-dark ms-1">{{ $totalPendentes }}</span>
                @endif
            </a>

            <a href="{{ route('admin.feedbacks.index', ['status' => 'aprovado']) }}"
               class="btn btn-sm {{ $status === 'aprovado' ? 'btn-success' : 'btn-outline-success' }}">
                <i class="fas fa-check-circle me-1"></i> Aprovados
            </a>

            <a href="{{ route('admin.feedbacks.index', ['status' => 'rejeitado']) }}"
               class="btn btn-sm {{ $status === 'rejeitado' ? 'btn-danger' : 'btn-outline-danger' }}">
                <i class="fas fa-times-circle me-1"></i> Rejeitados
            </a>
        </div>
    </div>
</div>


{{-- ============================================================
     LISTA DE FEEDBACKS
============================================================ --}}
@if($feedbacks->isEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="fas fa-inbox"></i>
            </div>
            <h5 class="fw-bold mb-1">Nenhum feedback encontrado</h5>
            <p class="text-muted small mb-3">
                @if($status === 'todos')
                    Ainda não há feedbacks no sistema.
                @else
                    Não há feedbacks com o estado "<strong>{{ ucfirst($status) }}</strong>".
                @endif
            </p>
            @if($status !== 'todos')
                <a href="{{ route('admin.feedbacks.index', ['status' => 'todos']) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-list me-1"></i> Ver todos
                </a>
            @endif
        </div>
    </div>

@else

    <div class="row g-3">

        @foreach($feedbacks as $feedback)
            @php
                $egresso = $feedback->egresso;
                $user = $egresso?->user;

                // Foto com fallback
                $fotoRaw = $egresso?->foto_url ?? $user?->photo_url ?? null;
                $fotoUrl = null;
                if ($fotoRaw) {
                    $fotoUrl = filter_var($fotoRaw, FILTER_VALIDATE_URL) ? $fotoRaw : asset($fotoRaw);
                }

                $nomeEgresso = $egresso?->nome_completo ?? $user?->name ?? 'Egresso';
                $iniciais = collect(explode(' ', trim($nomeEgresso)))
                    ->filter()
                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                    ->take(2)
                    ->implode('');

                // Estado
                $estados = [
                    'pendente'  => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
                    'aprovado'  => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
                    'rejeitado' => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
                ];
                $estado = $estados[$feedback->status] ?? $estados['pendente'];
            @endphp

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 feedback-card">

                    {{-- ============================================================
                         HEADER DO CARD — Autor + Estado + Nota
                    ============================================================ --}}
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-3">

                            {{-- Avatar --}}
                            @if($fotoUrl)
                                <img src="{{ $fotoUrl }}"
                                     alt="{{ $nomeEgresso }}"
                                     class="rounded-circle border flex-shrink-0"
                                     style="width: 48px; height: 48px; object-fit: cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1rem; display: none;">
                                    {{ $iniciais }}
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width: 48px; height: 48px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1rem;">
                                    {{ $iniciais }}
                                </div>
                            @endif

                            {{-- Info --}}
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <strong class="text-truncate">{{ $nomeEgresso }}</strong>

                                    <span class="badge bg-{{ $estado['color'] }}-subtle text-{{ $estado['color'] }}-emphasis border border-{{ $estado['color'] }}-subtle">
                                        <i class="fas fa-{{ $estado['icon'] }} me-1"></i> {{ $estado['label'] }}
                                    </span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                    {{ $feedback->created_at->diffForHumans() }}
                                </small>
                            </div>

                            {{-- Nota --}}
                            @if($feedback->nota)
                                <div class="text-end flex-shrink-0">
                                    <div class="d-flex align-items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $feedback->nota ? 'text-warning' : 'text-muted opacity-25' }}"
                                               style="font-size: 0.75rem;"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ $feedback->nota }}/5</small>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- ============================================================
                         BODY — Conteúdo
                    ============================================================ --}}
                    <div class="card-body p-4">

                        {{-- Título --}}
                        <h6 class="fw-bold mb-2 text-dark">
                            {{ $feedback->titulo }}
                        </h6>

                        {{-- Badge de Curso --}}
                        @if($feedback->curso)
                            <div class="mb-3">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    {{ $feedback->curso->nome }}
                                </span>
                            </div>
                        @endif

                        {{-- Mensagem --}}
                        <p class="text-muted mb-0 feedback-message">
                            {{ Str::limit($feedback->mensagem ?? $feedback->conteudo, 180) }}
                        </p>

                    </div>

                    {{-- ============================================================
                         FOOTER — Ações
                    ============================================================ --}}
                    <div class="card-footer bg-white border-top py-2 px-3">
                        <div class="d-flex gap-2 justify-content-between align-items-center">

                            <a href="{{ route('admin.feedbacks.show', $feedback->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> Ver detalhes
                            </a>

                            <div class="d-flex gap-2">
                                @if($feedback->status === 'pendente')

                                    <form action="{{ route('admin.feedbacks.rejeitar', $feedback->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Rejeitar este feedback?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times me-1"></i> Rejeitar
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.feedbacks.aprovar', $feedback->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Aprovar este feedback?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i> Aprovar
                                        </button>
                                    </form>

                                @else
                                    <span class="badge bg-{{ $estado['color'] }}-subtle text-{{ $estado['color'] }}-emphasis border border-{{ $estado['color'] }}-subtle">
                                        <i class="fas fa-{{ $estado['icon'] }} me-1"></i> {{ $estado['label'] }}
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

    {{-- Paginação --}}
    @if(method_exists($feedbacks, 'hasPages') && $feedbacks->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $feedbacks->appends(request()->query())->links() }}
        </div>
    @endif

@endif

@endsection

