@extends('layouts.admin')

@section('title', 'Dashboard - Administrador')

@section('content')


<div class="container-fluid px-0">

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
                        Visão geral do sistema de controlo e localização de ex-estudantes
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

    <!-- ============================================================ -->
<!-- CARDS DE ESTATÍSTICAS (MANTIDOS ORIGINAIS) -->
<!-- ============================================================ -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card bg-gradient-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total de Egressos</h6>
                        <h2 class="mb-0 text-white">{{ $stats['total_egressos'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-white-20 p-3 rounded-circle">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card bg-gradient-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Egressos Activos</h6>
                        <h2 class="mb-0 text-white">{{ $stats['egressos_activos'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-white-20 p-3 rounded-circle">
                        <i class="fas fa-user-check fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card bg-gradient-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Oportunidades</h6>
                        <h2 class="mb-0 text-white">{{ $stats['total_oportunidades'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-white-20 p-3 rounded-circle">
                        <i class="fas fa-handshake fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card bg-gradient-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Eventos</h6>
                        <h2 class="mb-0 text-white">{{ $stats['total_eventos'] ?? 0 }}</h2>
                    </div>
                    <div class="bg-white-20 p-3 rounded-circle">
                        <i class="fas fa-calendar-alt fa-2x text-danger"></i>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- ============================================================
         GRÁFICOS PRINCIPAIS
         ============================================================ -->
    <div class="row g-4 mb-4">
        <!-- GRÁFICO DE BARRAS - Egressos por Unidade -->
        <div class="col-xl-8 fade-in">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>
                        <i class="fas fa-building text-primary me-2"></i>
                        Egressos por Unidade Orgânica
                    </h5>
                    <div>
                        <span class="badge bg-soft-primary rounded-pill">
                            <i class="fas fa-arrow-up me-1"></i> Ordenado por quantidade
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                      <canvas id="egressosUnidadeChart"
        data-unidades="{{ json_encode($stats_unidades ?? []) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICO CIRCULAR - Distribuição por Tipo -->
        <div class="col-xl-4 fade-in">
            <div class="card chart-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>
                        <i class="fas fa-chart-pie text-success me-2"></i>
                        Distribuição
                    </h5>
                    <span class="badge bg-soft-success rounded-pill">Status</span>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-container chart-container-sm">
                        <canvas id="distribuicaoChart"
        data-stats="{{ json_encode([
            'egressos_activos'      => $stats['egressos_activos'] ?? 0,
            'egressos_inativos'     => $stats['egressos_inativos'] ?? 0,
            'egressos_pendentes'    => $stats['egressos_pendentes'] ?? 0,
            'egressos_lost_contact' => $stats['egressos_lost_contact'] ?? 0,
            'total_egressos'        => $stats['total_egressos'] ?? 0,
        ]) }}"></canvas>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:#2563eb;"></span>
                        <small class="text-muted">Ativos ({{ $stats['egressos_activos'] ?? 0 }})</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:#f59e0b;"></span>
                        <small class="text-muted">Inativos ({{ $stats['egressos_inativos'] ?? 0 }})</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:#ef4444;"></span>
                        <small class="text-muted">Pendentes ({{ $stats['egressos_pendentes'] ?? 0 }})</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:#6b7280;"></span>
                        <small class="text-muted">Sem Contacto ({{ $stats['egressos_lost_contact'] ?? 0 }})</small>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
     LINHA DO TEMPO - ÚLTIMOS EGRESSOS + OPORTUNIDADES + EVENTOS
     ============================================================ -->
<div class="row g-4">

    <!-- ============================================================
         ÚLTIMOS EGRESSOS
    ============================================================ -->
    <div class="col-xl-4 fade-in">
        <div class="card chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="fas fa-user-graduate text-primary me-2"></i>
                    Últimos Egressos
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" style="font-size: 10px;">
                        <i class="fas fa-calendar-week me-1"></i> 7 dias
                    </span>
                    <a href="{{ route('admin.egressos.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if(isset($ultimos_egressos) && $ultimos_egressos->count() > 0)
                    @foreach($ultimos_egressos as $egresso)
                        @php
                            $fotoUrl = $egresso->foto_url ?? $egresso->foto ?? $egresso->foto_perfil ?? null;
                            $avatarFinal = null;
                            if ($fotoUrl) {
                                if (\Illuminate\Support\Str::startsWith($fotoUrl, ['http://', 'https://'])) {
                                    $avatarFinal = $fotoUrl;
                                } else {
                                    $avatarFinal = asset($fotoUrl);
                                }
                            }
                        @endphp
                        <div class="egresso-item-pro">
                            <div class="d-flex align-items-center gap-3">
                                <div class="egresso-avatar-wrapper">
                                    @if($avatarFinal)
                                        <img src="{{ $avatarFinal }}" 
                                             alt="{{ $egresso->nome_completo }}" 
                                             class="egresso-avatar-img" 
                                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="egresso-avatar-fallback" style="display:none;">
                                            {{ strtoupper(substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="egresso-avatar-fallback">
                                            {{ strtoupper(substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="egresso-status-dot bg-success"></span>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="egresso-nome">{{ $egresso->nome_completo }}</div>
                                    <div class="egresso-curso">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        {{ $egresso->curso->nome ?? 'Curso não definido' }}
                                    </div>
                                    <div class="egresso-tempo">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $egresso->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                <a href="{{ route('admin.egressos.show', $egresso->id) }}" 
                                   class="btn-egresso-action" 
                                   title="Ver detalhes">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="empty-icon-pro bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fas fa-user-graduate fa-2x text-primary"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Nenhum egresso recente</h6>
                        <p class="text-muted small mb-0">Nenhum egresso cadastrado nos últimos 7 dias.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ============================================================
         OPORTUNIDADES RECENTES
    ============================================================ -->
    <div class="col-xl-4 fade-in">
        <div class="card chart-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="fas fa-briefcase text-warning me-2"></i>
                    Oportunidades Recentes
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill" style="font-size: 10px;">
                        <i class="fas fa-calendar-week me-1"></i> 7 dias
                    </span>
                    <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if(isset($oportunidades_recentes) && $oportunidades_recentes->count() > 0)
                    @foreach($oportunidades_recentes as $op)
                        <div class="item-list-pro">
                            <div class="d-flex align-items-start gap-3">
                                <div class="item-icon-pro bg-warning bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-briefcase text-warning"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="item-title-pro">{{ $op->titulo }}</div>
                                    <div class="item-sub-pro">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $op->empresa ?? 'Empresa não informada' }}
                                    </div>
                                    <div class="item-meta-pro">
                                        <span class="badge badge-soft-{{ $op->tipo == 'emprego' ? 'primary' : ($op->tipo == 'estagio' ? 'info' : 'secondary') }} rounded-pill">
                                            {{ ucfirst($op->tipo ?? 'Geral') }}
                                        </span>
                                        <span class="item-date-pro">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $op->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="empty-icon-pro bg-warning bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fas fa-briefcase fa-2x text-warning"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Nenhuma oportunidade recente</h6>
                        <p class="text-muted small mb-0">Nenhuma oportunidade criada nos últimos 7 dias.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

{{-- ============================================================
     PRÓXIMOS EVENTOS
============================================================ --}}
<div class="col-xl-4 fade-in">
    <div class="card chart-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>
                <i class="fas fa-calendar-check text-danger me-2"></i>
                Próximos Eventos
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill" style="font-size: 10px;">
                    <i class="fas fa-calendar-week me-1"></i> Próximos
                </span>
                <a href="{{ route('admin.eventos.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">
                    Ver todos <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if(isset($eventos_proximos) && $eventos_proximos->count() > 0)
                @foreach($eventos_proximos as $evento)
                    <div class="evento-item-pro">
                        <div class="d-flex align-items-start gap-3">
                            <div class="evento-date-pro">
                                <span class="evento-dia">
                                    {{ $evento->data_inicio ? date('d', strtotime($evento->data_inicio)) : '--' }}
                                </span>
                                <span class="evento-mes">
                                    {{ $evento->data_inicio ? strtoupper(date('M', strtotime($evento->data_inicio))) : '---' }}
                                </span>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="item-title-pro">{{ $evento->titulo }}</div>
                                <div class="item-sub-pro">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $evento->local ?? 'Online' }}
                                </div>
                                <div class="item-meta-pro">
                                    <span class="badge badge-soft-{{ $evento->tipo == 'presencial' ? 'primary' : ($evento->tipo == 'online' ? 'success' : 'warning') }} rounded-pill">
                                        {{ ucfirst($evento->tipo ?? 'Geral') }}
                                    </span>
                                    <span class="item-date-pro">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $evento->data_inicio ? date('H:i', strtotime($evento->data_inicio)) : '--:--' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <div class="empty-icon-pro bg-danger bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fas fa-calendar-alt fa-2x text-danger"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Nenhum evento próximo</h6>
                    <p class="text-muted small mb-0">Nenhum evento agendado.</p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
    <!-- ============================================================
         RODAPÉ DO DASHBOARD
         ============================================================ -->
    <div class="row mt-4 fade-in">
        <div class="col-12">
            <div class="card chart-card bg-light">
                <div class="card-body py-3 text-center text-muted">
                    <small>
                        <i class="fas fa-database me-1"></i> 
                        Total de registos: 
                        <strong>{{ $stats['total_egressos'] ?? 0 }}</strong> egressos, 
                        <strong>{{ $stats['total_oportunidades'] ?? 0 }}</strong> oportunidades, 
                        <strong>{{ $stats['total_eventos'] ?? 0 }}</strong> eventos
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i> Última atualização: {{ date('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

</div>


@endsection