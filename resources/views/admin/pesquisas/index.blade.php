@extends('layouts.admin')

@section('title', 'Pesquisas com Egressos')

@section('page_title', '📊 Pesquisas com Egressos')
@section('page_subtitle', 'Crie e acompanhe pesquisas com os ex-estudantes')

@section('content')

@php
    $total = $pesquisas->count();
    $hoje = now()->format('Y-m-d');

    $ativas = $pesquisas->filter(function ($p) use ($hoje) {
        return $p->data_inicio <= $hoje && $p->data_fim >= $hoje;
    })->count();

    $agendadas = $pesquisas->filter(fn($p) => $p->data_inicio > $hoje)->count();
    $encerradas = $pesquisas->filter(fn($p) => $p->data_fim < $hoje)->count();

    $totalRespostas = $pesquisas->sum('respostas_count');
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-poll"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total de Pesquisas</div>
                <small class="stat-desc">Criadas até agora</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-circle-play"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $ativas }}</div>
                <div class="stat-label">Pesquisas Ativas</div>
                <small class="stat-desc">A decorrer agora</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalRespostas }}</div>
                <div class="stat-label">Total de Respostas</div>
                <small class="stat-desc">Recolhidas dos egressos</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $agendadas }}</div>
                <div class="stat-label">Agendadas</div>
                <small class="stat-desc">A começar em breve</small>
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
     LISTA DE PESQUISAS
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Lista de Pesquisas</h6>
                    <small class="text-muted">
                        {{ $total }} {{ $total === 1 ? 'pesquisa' : 'pesquisas' }}
                    </small>
                </div>
            </div>

            <a href="{{ route('admin.pesquisas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nova Pesquisa
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        @if($pesquisas->count() > 0)

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Pesquisa</th>
                            <th>Período</th>
                            <th class="text-center">Perguntas</th>
                            <th class="text-center">Respostas</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesquisas as $pesquisa)
                            @php
                                $estado = 'agendada';
                                if ($pesquisa->data_inicio <= $hoje && $pesquisa->data_fim >= $hoje) {
                                    $estado = 'ativa';
                                } elseif ($pesquisa->data_fim < $hoje) {
                                    $estado = 'encerrada';
                                }

                                $estadosMap = [
                                    'ativa'     => ['color' => 'success',   'icon' => 'circle-play',  'label' => 'Ativa'],
                                    'agendada'  => ['color' => 'warning',   'icon' => 'clock',        'label' => 'Agendada'],
                                    'encerrada' => ['color' => 'secondary', 'icon' => 'circle-check', 'label' => 'Encerrada'],
                                ];
                                $estadoInfo = $estadosMap[$estado];
                            @endphp

                            <tr>
                                {{-- Pesquisa --}}
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="type-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-poll"></i>
                                        </div>
                                        <div class="min-width-0">
                                            <strong class="d-block text-truncate" style="max-width: 320px;">
                                                {{ $pesquisa->titulo }}
                                            </strong>
                                            @if($pesquisa->descricao)
                                                <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                    {{ Str::limit($pesquisa->descricao, 60) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Período --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">
                                            <i class="fas fa-play-circle text-success me-1" style="font-size: 0.7rem;"></i>
                                            {{ $pesquisa->data_inicio?->format('d/m/Y') ?? '-' }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-stop-circle text-danger me-1" style="font-size: 0.7rem;"></i>
                                            {{ $pesquisa->data_fim?->format('d/m/Y') ?? '-' }}
                                        </small>
                                    </div>
                                </td>

                                {{-- Perguntas --}}
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3">
                                        <i class="fas fa-question me-1"></i>
                                        {{ $pesquisa->perguntas_count ?? 0 }}
                                    </span>
                                </td>

                                {{-- Respostas --}}
                                <td class="text-center">
                                    <span class="badge bg-success rounded-pill px-3">
                                        <i class="fas fa-comments me-1"></i>
                                        {{ $pesquisa->respostas_count ?? 0 }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td class="text-center">
                                    <span class="badge bg-{{ $estadoInfo['color'] }}-subtle text-{{ $estadoInfo['color'] }}-emphasis border border-{{ $estadoInfo['color'] }}-subtle">
                                        <i class="fas fa-{{ $estadoInfo['icon'] }} me-1"></i>
                                        {{ $estadoInfo['label'] }}
                                    </span>
                                </td>

                                {{-- Ações --}}
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.pesquisas.perguntas', $pesquisa->id) }}"
                                           class="btn btn-outline-primary"
                                           title="Gerir Perguntas">
                                            <i class="fas fa-list"></i>
                                        </a>

                                        <a href="{{ route('admin.pesquisas.resultados', $pesquisa->id) }}"
                                           class="btn btn-outline-success"
                                           title="Ver Resultados">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>

                                        <a href="{{ route('admin.pesquisas.edit', $pesquisa->id) }}"
                                           class="btn btn-outline-primary"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.pesquisas.destroy', $pesquisa->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja eliminar esta pesquisa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else

            {{-- Estado vazio --}}
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-poll"></i>
                </div>
                <h6 class="fw-bold mb-1">Nenhuma pesquisa criada</h6>
                <p class="text-muted small mb-4">
                    Crie a sua primeira pesquisa para ouvir os egressos.
                </p>
                <a href="{{ route('admin.pesquisas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Criar Pesquisa
                </a>
            </div>

        @endif
    </div>

</div>

@endsection


