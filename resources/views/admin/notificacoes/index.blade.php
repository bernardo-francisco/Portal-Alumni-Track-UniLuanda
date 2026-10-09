@extends('layouts.admin')

@section('title', 'Notificações')

@section('page_title', '🔔 Notificações')
@section('page_subtitle', 'Acompanhe todas as atividades do sistema')

@section('content')

@php
    $naoLidas = $totalNaoLidas ?? 0;
    $total = $notificacoes->total() ?? 0;
@endphp

{{-- ============================================================
     CABEÇALHO + ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    {{-- Card principal --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm welcome-card h-100">
            <div class="card-body p-4 d-flex align-items-center gap-4">
                <div class="welcome-icon flex-shrink-0">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="fw-bold mb-1">Centro de Notificações</h4>
                    <p class="text-muted mb-0">
                        @if($naoLidas > 0)
                            Você tem <strong class="text-primary">{{ $naoLidas }}</strong>
                            {{ $naoLidas === 1 ? 'notificação não lida' : 'notificações não lidas' }}.
                        @else
                            Está tudo em dia. Não tem notificações pendentes.
                        @endif
                    </p>
                </div>

            @if($naoLidas > 0)
                <form action="{{ route('admin.notificacoes.marcar-todas') }}" method="POST" class="flex-shrink-0"
                    onsubmit="setTimeout(function(){ if (typeof atualizarBadgeNotificacoes === 'function') atualizarBadgeNotificacoes(); }, 200);">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-double me-1"></i>
                        <span class="d-none d-md-inline">Marcar todas como lidas</span>
                        <span class="d-md-none">Marcar todas</span>
                    </button>
                </form>
            @endif
            </div>
        </div>
    </div>

    {{-- Estatísticas rápidas --}}
    <div class="col-lg-4">
        <div class="row g-3 h-100">
            <div class="col-6">
                <div class="stat-mini stat-mini-primary">
                    <div class="stat-mini-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-mini-value">{{ $total }}</div>
                    <div class="stat-mini-label">Total</div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-mini stat-mini-danger">
                    <div class="stat-mini-icon">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="stat-mini-value">{{ $naoLidas }}</div>
                    <div class="stat-mini-label">Não lidas</div>
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


{{-- ============================================================
     LISTA DE NOTIFICAÇÕES
============================================================ --}}
@if($notificacoes->count() > 0)

    {{-- Filtros rápidos --}}
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" onclick="filtrar('todas', this)">
                Todas ({{ $total }})
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrar('nao-lidas', this)">
                Não lidas ({{ $naoLidas }})
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="filtrar('lidas', this)">
                Lidas ({{ $total - $naoLidas }})
            </button>
        </div>

        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Clique numa notificação para a marcar como lida
        </small>
    </div>

    <div class="card border-0 shadow-sm notifications-card">
        <div class="card-body p-0" id="notificacoes-container">
            @foreach($notificacoes as $notif)
                @php
                    // Mapear tipo → ícone e cor
                    $tipos = [
                        'sistema'       => ['icon' => 'bell',            'color' => 'secondary'],
                        'oportunidade'  => ['icon' => 'briefcase',       'color' => 'primary'],
                        'evento'        => ['icon' => 'calendar',        'color' => 'info'],
                        'conexao'       => ['icon' => 'handshake',       'color' => 'success'],
                        'curtida'       => ['icon' => 'heart',           'color' => 'danger'],
                        'comentario'    => ['icon' => 'comment',         'color' => 'primary'],
                        'mensagem'      => ['icon' => 'envelope',        'color' => 'primary'],
                        'servico'       => ['icon' => 'concierge-bell',  'color' => 'danger'],
                        'feedback'      => ['icon' => 'comment-dots',    'color' => 'info'],
                    ];

                    $tipo = $tipos[$notif->tipo] ?? $tipos['sistema'];
                    $naoLida = !$notif->lida;
                @endphp

                <div class="notification-item d-flex align-items-start gap-3 px-4 py-3
                            {{ $naoLida ? 'notification-unread' : 'notification-read' }}
                            {{ !$loop->last ? 'border-bottom' : '' }}"
                     data-notif-id="{{ $notif->id }}"
                     data-status="{{ $naoLida ? 'nao-lida' : 'lida' }}">

                    {{-- Ícone --}}
                    <div class="notification-icon bg-{{ $tipo['color'] }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="fas fa-{{ $tipo['icon'] }} text-{{ $tipo['color'] }}"></i>
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <strong class="notification-title {{ !$naoLida ? 'text-muted fw-medium' : '' }}">
                                        {{ $notif->titulo }}
                                    </strong>

                                    @if($naoLida)
                                        <span class="badge bg-danger rounded-pill"
                                              style="font-size: 0.6rem; padding: 3px 8px;">
                                            NOVA
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Marca temporal à direita --}}
                            <small class="text-muted flex-shrink-0 text-nowrap">
                                {{ $notif->created_at->diffForHumans() }}
                            </small>
                        </div>

                        <p class="text-muted small mb-2 notification-message">
                            {{ $notif->mensagem }}
                        </p>

                        {{-- Ações --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($notif->link)
                                <a href="{{ $notif->link }}"
                                   class="btn btn-sm btn-primary"
                                   onclick="marcarComoLida({{ $notif->id }}, event)">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    Ver detalhes
                                </a>
                            @endif

                            @if($naoLida)
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="marcarComoLida({{ $notif->id }})">
                                    <i class="fas fa-check me-1"></i>
                                    Marcar como lida
                                </button>
                            @else
                                <span class="badge bg-light text-muted border">
                                    <i class="fas fa-check me-1"></i> Lida
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $notificacoes->links() }}
    </div>

@else
    {{-- ============================================================
         ESTADO VAZIO
    ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-bell-slash"></i>
            </div>
            <h5 class="fw-bold mb-2">Sem notificações</h5>
            <p class="text-muted mb-4">
                Não tem notificações de momento. Volte mais tarde.
            </p>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
@endif

@endsection

