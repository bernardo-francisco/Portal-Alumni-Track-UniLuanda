@extends('layouts.egresso')

@section('title', 'Notificações')

@section('page_title', '🔔 Notificações')
@section('page_subtitle', 'Acompanhe as atualizações e novidades do sistema')

@section('content')

@php
    $totalNotificacoes = $notificacoes->total() ?? 0;
    $totalNaoLidas = $notificacoes->where('lida', false)->count();
    $totalLidas = $notificacoes->where('lida', true)->count();
    $totalUltimaSemana = $notificacoes->where('created_at', '>=', now()->subDays(7))->count();
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total</div>
                <div class="stat-card-value">{{ $totalNotificacoes }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-database"></i>
                    Todas as notificações
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Não Lidas</div>
                <div class="stat-card-value">{{ $totalNaoLidas }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i>
                    A aguardar leitura
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
                <div class="stat-card-label">Lidas</div>
                <div class="stat-card-value">{{ $totalLidas }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-thumbs-up"></i>
                    Já visualizadas
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Última Semana</div>
                <div class="stat-card-value">{{ $totalUltimaSemana }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-calendar"></i>
                    Últimos 7 dias
                </div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     AÇÕES
============================================================ --}}
@if($totalNotificacoes > 0)
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-list text-primary me-2"></i>
                Todas as Notificações
            </h6>
            <small class="text-muted">
                {{ $totalNotificacoes }} {{ $totalNotificacoes === 1 ? 'notificação' : 'notificações' }}
            </small>
        </div>

        @if($totalNaoLidas > 0)
            <form action="{{ route('egresso.notificacoes.marcar-todas') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i> Marcar todas como lidas
                </button>
            </form>
        @endif
    </div>
@endif


{{-- ============================================================
     LISTA DE NOTIFICAÇÕES
============================================================ --}}
@if($totalNotificacoes > 0)

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            @foreach($notificacoes as $notificacao)
                @php
                    $tipoMap = [
                        'success' => ['color' => 'success', 'icon' => 'check-circle'],
                        'danger'  => ['color' => 'danger',  'icon' => 'exclamation-circle'],
                        'warning' => ['color' => 'warning', 'icon' => 'exclamation-triangle'],
                        'info'    => ['color' => 'info',    'icon' => 'info-circle'],
                        'sistema' => ['color' => 'primary', 'icon' => 'bell'],
                    ];

                    $tipo = $notificacao->tipo ?? 'sistema';
                    $tipoInfo = $tipoMap[$tipo] ?? $tipoMap['sistema'];

                    $naoLida = !$notificacao->lida;
                @endphp

                <div class="notification-item {{ $naoLida ? 'notification-unread' : '' }} {{ !$loop->last ? 'border-bottom' : '' }}"
                     data-notif-id="{{ $notificacao->id }}">

                    <div class="d-flex align-items-start gap-3">

                        {{-- Ícone --}}
                        <div class="notification-icon bg-{{ $tipoInfo['color'] }} bg-opacity-10 text-{{ $tipoInfo['color'] }}">
                            <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                        </div>

                        {{-- Conteúdo --}}
                        <div class="flex-grow-1 min-width-0">

                            {{-- Cabeçalho --}}
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1 flex-wrap">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if($naoLida)
                                        <span class="badge-dot"></span>
                                    @endif

                                    <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                        <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                        {{ ucfirst($tipo) }}
                                    </span>
                                </div>

                                <small class="text-muted flex-shrink-0 text-nowrap">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $notificacao->created_at->diffForHumans() }}
                                </small>
                            </div>

                            {{-- Título --}}
                            <h6 class="notification-title {{ $naoLida ? '' : 'text-muted fw-medium' }}">
                                {{ $notificacao->titulo }}
                            </h6>

                            {{-- Mensagem --}}
                            <p class="notification-message">
                                {{ $notificacao->mensagem }}
                            </p>

                            {{-- Ações --}}
                            <div class="d-flex gap-2 flex-wrap mt-3">

                                @if($notificacao->link)
                                    <a href="{{ $notificacao->link }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-right me-1"></i> Ver mais
                                    </a>
                                @endif

                                @if($naoLida)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-marcar-lida"
                                            onclick="marcarComoLida({{ $notificacao->id }}, this)">
                                        <i class="fas fa-check me-1"></i> Marcar como lida
                                    </button>
                                @else
                                    <span class="badge bg-light text-muted border">
                                        <i class="fas fa-check me-1"></i> Lida
                                    </span>
                                @endif

                            </div>

                            {{-- Data completa --}}
                            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                                {{ $notificacao->created_at->format('d/m/Y \à\s H:i') }}
                            </small>

                        </div>

                    </div>

                </div>
            @endforeach

        </div>
    </div>

    {{-- Paginação --}}
    @if(method_exists($notificacoes, 'hasPages') && $notificacoes->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $notificacoes->appends(request()->query())->links() }}
        </div>
    @endif

@else

    {{-- ============================================================
         ESTADO VAZIO
    ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-bell-slash"></i>
            </div>
            <h5 class="fw-bold mb-2">Nenhuma notificação</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                Você não tem notificações no momento.
                As notificações aparecerão aqui quando houver novidades.
            </p>
            <a href="{{ route('egresso.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>

@endif

@endsection
