@extends('layouts.empresa')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- CARDS DE ESTATÍSTICAS --}}
<div class="row g-4 mb-4">

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <span class="stat-card-label">Oportunidades</span>
                    <h3 class="stat-card-value">{{ $totalOportunidades ?? 0 }}</h3>
                    <small class="stat-card-desc"><i class="fas fa-briefcase"></i> Total publicadas</small>
                </div>
                <div class="stat-card-icon"><i class="fas fa-briefcase"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <span class="stat-card-label">Candidaturas</span>
                    <h3 class="stat-card-value">{{ $totalCandidaturas ?? 0 }}</h3>
                    <small class="stat-card-desc"><i class="fas fa-file-signature"></i> Total recebidas</small>
                </div>
                <div class="stat-card-icon"><i class="fas fa-file-signature"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <span class="stat-card-label">Pendentes</span>
                    <h3 class="stat-card-value">{{ $candidaturasPendentes ?? 0 }}</h3>
                    <small class="stat-card-desc"><i class="fas fa-clock"></i> Aguardam análise</small>
                </div>
                <div class="stat-card-icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-body">
                <div class="stat-card-info">
                    <span class="stat-card-label">Aprovadas</span>
                    <h3 class="stat-card-value">{{ $candidaturasAprovadas ?? 0 }}</h3>
                    <small class="stat-card-desc"><i class="fas fa-user-check"></i> Candidatos aceites</small>
                </div>
                <div class="stat-card-icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>

</div>

{{-- GRÁFICOS --}}
<div class="row g-4 mb-4">

    <div class="col-lg-6 col-xl-4">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <div class="chart-icon chart-icon-primary">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h6 class="chart-card-title">Status das Candidaturas</h6>
                    <small class="chart-card-subtitle">Distribuição por estado</small>
                </div>
            </div>
            <div class="chart-card-body">
                <div class="chart-container">
                    <canvas id="graficoStatus"
        data-dados="{{ json_encode($dadosStatus ?? []) }}"></canvas>
                </div>
                <div class="chart-footer">
                    <span class="chart-badge">
                        <i class="fas fa-circle text-primary" style="font-size:0.5rem;"></i>
                        Total: {{ $totalCandidaturas ?? 0 }} candidaturas
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-xl-4">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <div class="chart-icon chart-icon-success">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <div>
                    <h6 class="chart-card-title">Oportunidades por Tipo</h6>
                    <small class="chart-card-subtitle">Categorização das publicações</small>
                </div>
            </div>
            <div class="chart-card-body">
                <div class="chart-container">
                    <canvas id="graficoTipos"
        data-dados="{{ json_encode($dadosTipos ?? []) }}"></canvas>
                </div>
                <div class="chart-footer">
                    <span class="chart-badge">
                        <i class="fas fa-circle text-success" style="font-size:0.5rem;"></i>
                        Total: {{ $totalOportunidades ?? 0 }} oportunidades
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12 col-xl-4">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <div class="chart-icon chart-icon-info">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div>
                    <h6 class="chart-card-title">Taxa de Aprovação</h6>
                    <small class="chart-card-subtitle">Performance das candidaturas</small>
                </div>
            </div>
            <div class="chart-card-body">
                <div class="chart-container">
                    <canvas id="graficoAprovacao"
        data-dados="{{ json_encode([
            'aprovadas' => $candidaturasAprovadas ?? 0,
            'total'     => $totalCandidaturas ?? 0,
        ]) }}"></canvas>
                </div>
                <div class="chart-footer">
                    <span class="chart-badge chart-badge-success">
                        <i class="fas fa-arrow-up"></i>
                        {{ $taxaAprovacao ?? 0 }}% de aprovação
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- TABELAS --}}
<div class="row g-4">

    {{-- CANDIDATURAS RECENTES --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clock text-primary me-2"></i> Candidaturas Recentes</h6>
                <a href="{{ route('empresa.candidaturas.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todas <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if(isset($candidaturasRecentes) && $candidaturasRecentes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Egresso</th>
                                    <th>Oportunidade</th>
                                    <th>Estado</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($candidaturasRecentes as $cand)
                                    @php
                                        $coresStatus = [
                                            'pendente'   => 'warning',
                                            'em_analise' => 'info',
                                            'entrevista' => 'primary',
                                            'aprovado'   => 'success',
                                            'aceite'     => 'success',
                                            'rejeitado'  => 'danger',
                                        ];
                                        $cor = $coresStatus[$cand->status] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-sm">
                                                    {{ strtoupper(substr($cand->egresso->nome_completo ?? 'EG', 0, 2)) }}
                                                </div>
                                                <strong style="font-size: 0.85rem;">
                                                    {{ $cand->egresso->nome_completo ?? 'Egresso' }}
                                                </strong>
                                            </div>
                                        </td>
                                        <td><span class="text-muted small">{{ $cand->oportunidade->titulo ?? '—' }}</span></td>
                                        <td>
                                            <span class="badge bg-{{ $cor }}-subtle text-{{ $cor }} border border-{{ $cor }}">
                                                {{ ucfirst(str_replace('_', ' ', $cand->status)) }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted">{{ $cand->created_at->format('d/m/Y') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3"><i class="fas fa-inbox"></i></div>
                        <p class="text-muted mb-0">Nenhuma candidatura recebida ainda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- OPORTUNIDADES RECENTES --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase text-success me-2"></i> Minhas Oportunidades</h6>
                <a href="{{ route('empresa.oportunidades.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nova
                </a>
            </div>
            <div class="card-body">
                @if(isset($oportunidadesRecentes) && $oportunidadesRecentes->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($oportunidadesRecentes as $op)
                            <div class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong class="d-block">{{ $op->titulo }}</strong>
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i> {{ ucfirst($op->tipo) }}
                                            <span class="mx-2">&bull;</span>
                                            <i class="fas fa-clock me-1"></i> {{ $op->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <a href="{{ route('empresa.oportunidades.edit', $op->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3"><i class="fas fa-briefcase"></i></div>
                        <p class="text-muted mb-3">Ainda não publicou oportunidades.</p>
                        <a href="{{ route('empresa.oportunidades.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Criar Primeira Oportunidade
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection
