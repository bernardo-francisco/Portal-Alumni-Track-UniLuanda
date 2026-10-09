@extends('layouts.egresso')

@section('title', 'Dashboard')

@section('page_title', '🏠 Dashboard')
@section('page_subtitle', 'Bem-vindo à área do Egresso')

@section('content')
<!-- ============================================================
         CABEÇALHO
         ============================================================ -->
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="h2 mb-1 fw-bold">Dashboard</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-chart-pie me-1"></i>
                        Visão geral do Painel do Egresso
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill">
                        <i class="fas fa-calendar-day me-1"></i>
                        {{ date('d/m/Y H:i') }}
                    </span>
                    <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

@php
    $totalEgressos     = $stats['total_egressos'] ?? 0;
    $minhasConexoes    = $stats['conexoes'] ?? 0;
    $totalOportunidades= $stats['total_oportunidades'] ?? $stats['oportunidades'] ?? 0;
    $totalEventos      = $stats['total_eventos'] ?? $stats['eventos'] ?? 0;
    $notificacoesNaoLidas = $stats['notificacoes_nao_lidas'] ?? 0;
    $conexoesTotais    = $stats['conexoes_totais'] ?? 0;
@endphp


{{-- ============================================================
     CARDS DE ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalEgressos }}</div>
                <div class="stat-label">Total de Egressos</div>
                <small class="stat-desc">Comunidade ativa</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $minhasConexoes }}</div>
                <div class="stat-label">Minhas Conexões</div>
                <small class="stat-desc">Contactos ativos</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalOportunidades }}</div>
                <div class="stat-label">Oportunidades</div>
                <small class="stat-desc">Vagas e estágios</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalEventos }}</div>
                <div class="stat-label">Eventos</div>
                <small class="stat-desc">Workshops e palestras</small>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     ATIVIDADES + MURAL
============================================================ --}}
<div class="row g-4 mb-4">

    {{-- Atividades Recentes --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Atividades Recentes</h6>
                            <small class="text-muted">Últimos 7 dias</small>
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                        {{ count($atividades ?? []) }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                @if(empty($atividades) || count($atividades) == 0)
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Sem atividades recentes</h6>
                        <p class="text-muted small mb-0">
                            Comece a interagir com a comunidade!
                        </p>
                    </div>
                @else
                    <div class="activity-timeline">
                        @foreach($atividades as $atividade)
                            <div class="activity-item activity-{{ $atividade['tipo'] ?? 'info' }}">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="activity-content">
                                        {!! $atividade['descricao'] !!}
                                    </div>
                                    <span class="activity-time flex-shrink-0">
                                        {{ $atividade['data'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>


{{-- Mural de Recados --}}
<div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Mural de Recados</h6>
                        <small class="text-muted">Novidades da comunidade</small>
                    </div>
                </div>

                <a href="{{ route('egresso.feed') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Publicar
                </a>
            </div>
        </div>

        <div class="card-body p-4">

            @if(empty($mural) || count($mural) == 0)

                <div class="text-center py-4">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Ainda não há recados</h6>
                    <p class="text-muted small mb-3">
                        Seja o primeiro a compartilhar uma mensagem.
                    </p>
                    <a href="{{ route('egresso.feed') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-pen me-1"></i> Escrever primeiro recado
                    </a>
                </div>

            @else

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">
                        <i class="fas fa-layer-group me-1"></i>
                        {{ count($mural) }} {{ count($mural) == 1 ? 'recado' : 'recados' }}
                    </span>
                    <a href="{{ route('egresso.feed') }}" class="text-decoration-none small fw-semibold">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="mural-list">
                    @foreach($mural as $recado)
                        <div class="mural-item">
                            <div class="d-flex align-items-start gap-3">

                                {{-- ✅ Avatar com foto do utilizador (fallback para iniciais) --}}
                                <div class="mural-avatar {{ $recado['autor_tipo'] == 'admin' ? 'avatar-admin' : 'avatar-egresso' }}">
                                    @if(!empty($recado['autor_foto']))
                                        <img
                                            src="{{ asset($recado['autor_foto']) }}"
                                            alt="{{ $recado['autor'] }}"
                                            class="mural-avatar-img"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                        <span class="mural-avatar-initials" style="display: none;">
                                            {{ strtoupper(substr($recado['autor'], 0, 1)) }}
                                        </span>
                                    @else
                                        <span class="mural-avatar-initials">
                                            {{ strtoupper(substr($recado['autor'], 0, 1)) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Conteúdo --}}
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <strong class="mural-author-name">
                                                {{ $recado['autor'] }}
                                            </strong>

                                            @if($recado['autor_tipo'] == 'admin')
                                                <span class="mural-badge badge-admin">
                                                    <i class="fas fa-shield-alt me-1"></i> Admin
                                                </span>
                                            @elseif($recado['autor_tipo'] == 'egresso')
                                                <span class="mural-badge badge-egresso">
                                                    Egresso
                                                </span>
                                            @endif
                                        </div>

                                        <small class="text-muted mural-date flex-shrink-0">
                                            {{ $recado['data'] }}
                                        </small>
                                    </div>

                                    <div class="mural-text">
                                        {{ $recado['texto'] }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

            @endif

        </div>
    </div>
</div>

</div>


{{-- ============================================================
     DISTRIBUIÇÃO DA COMUNIDADE
============================================================ --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-globe-africa"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Distribuição da Comunidade</h6>
                            <small class="text-muted">Onde estão os nossos egressos</small>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        <i class="fas fa-users me-1"></i> {{ $totalEgressos }} membros
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">

                    {{-- Países --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="community-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="community-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Países Alcançados</h6>
                                    <small class="text-muted">Onde estão os egressos</small>
                                </div>
                            </div>

                            @if(isset($paises) && $paises->count() > 0)
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach($paises->take(5) as $pais)
                                        @php
                                            $bandeira = $bandeiras[$pais->pais] ?? '🌍';
                                        @endphp
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle d-flex align-items-center gap-1">
                                            <span>{{ $bandeira }}</span>
                                            {{ $pais->pais }}
                                            <strong class="ms-1">{{ $pais->total }}</strong>
                                        </span>
                                    @endforeach

                                    @if($paises->count() > 5)
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                            <i class="fas fa-plus me-1"></i> {{ $paises->count() - 5 }} países
                                        </span>
                                    @endif
                                </div>

                                <div class="pt-3 border-top small text-muted">
                                    <i class="fas fa-map-pin me-1"></i>
                                    <strong class="text-dark">{{ $totalEgressos }}</strong> egressos em
                                    <strong class="text-dark">{{ $totalPaises ?? 0 }}</strong> países
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-map-pin fa-2x text-muted mb-2"></i>
                                    <p class="text-muted small mb-0">Nenhum país registado ainda.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Engajamento --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="community-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="community-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Engajamento</h6>
                                    <small class="text-muted">Atividade da comunidade</small>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Egressos Conectados</span>
                                        <span class="fw-bold text-success">{{ min($conexoesTotais, 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar bg-success" style="width: {{ min($conexoesTotais, 100) }}%; border-radius: 10px;"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Minhas Conexões</span>
                                        <span class="fw-bold text-primary">{{ min($minhasConexoes, 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar bg-primary" style="width: {{ min($minhasConexoes, 100) }}%; border-radius: 10px;"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">Participação em Eventos</span>
                                        <span class="fw-bold text-info">{{ min($totalEventos, 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar bg-info" style="width: {{ min($totalEventos, 100) }}%; border-radius: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Últimas Novidades --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="community-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="community-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Últimas Novidades</h6>
                                    <small class="text-muted">Atualizações recentes</small>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('egresso.oportunidades') }}" class="novidade-item">
                                    <span class="novidade-badge bg-success">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <span class="text-muted small">
                                        <strong class="text-dark">{{ $totalOportunidades }}</strong> oportunidades
                                    </span>
                                </a>

                                <a href="{{ route('egresso.eventos') }}" class="novidade-item">
                                    <span class="novidade-badge bg-info">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                    <span class="text-muted small">
                                        <strong class="text-dark">{{ $totalEventos }}</strong> eventos próximos
                                    </span>
                                </a>

                                <a href="{{ route('egresso.rede') }}" class="novidade-item">
                                    <span class="novidade-badge bg-primary">
                                        <i class="fas fa-handshake"></i>
                                    </span>
                                    <span class="text-muted small">
                                        <strong class="text-dark">{{ $conexoesTotais }}</strong> conexões totais
                                    </span>
                                </a>

                                <a href="{{ route('egresso.notificacoes.index') }}" class="novidade-item">
                                    <span class="novidade-badge bg-warning">
                                        <i class="fas fa-bell"></i>
                                    </span>
                                    <span class="text-muted small">
                                        <strong class="text-dark">{{ $notificacoesNaoLidas }}</strong> notificações não lidas
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     AÇÕES RÁPIDAS
============================================================ --}}
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Ações Rápidas</h6>
                        <small class="text-muted">Acesso rápido às funcionalidades</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.pesquisas.index') }}" class="quick-action">
                            <div class="quick-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-poll"></i>
                            </div>
                            <span>Pesquisas</span>
                        </a>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.mural.index') }}" class="quick-action">
                            <div class="quick-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <span>Mural</span>
                        </a>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.feedbacks.create') }}" class="quick-action">
                            <div class="quick-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <span>Feedback</span>
                        </a>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.servicos.solicitar') }}" class="quick-action">
                            <div class="quick-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <span>Serviços</span>
                        </a>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.perfil.editar') }}" class="quick-action">
                            <div class="quick-icon bg-purple bg-opacity-10 text-purple">
                                <i class="fas fa-user"></i>
                            </div>
                            <span>Perfil</span>
                        </a>
                    </div>

                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="{{ route('egresso.rede') }}" class="quick-action">
                            <div class="quick-icon bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-users"></i>
                            </div>
                            <span>Rede</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

