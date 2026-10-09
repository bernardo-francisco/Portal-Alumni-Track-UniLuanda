@extends('layouts.admin')

@section('title', 'Cursos')

@section('page_title', '📚 Gestão de Cursos')
@section('page_subtitle', 'Cursos organizados por unidade orgânica')

@section('content')

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
@php
    $totalCursos = $cursos->count();
    $totalEgressos = $cursos->sum('egressos_count');
    $totalUnidades = $cursos->pluck('unidade_id')->unique()->count();
    $totalDepartamentos = $cursos->pluck('departamento')->filter()->unique()->count();
@endphp

<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalCursos }}</div>
                <div class="stat-label">Total de Cursos</div>
                <small class="stat-desc">Registados no sistema</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalEgressos }}</div>
                <div class="stat-label">Egressos</div>
                <small class="stat-desc">Vinculados aos cursos</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalUnidades }}</div>
                <div class="stat-label">Unidades</div>
                <small class="stat-desc">Com cursos registados</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalDepartamentos }}</div>
                <div class="stat-label">Departamentos</div>
                <small class="stat-desc">Áreas distintas</small>
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
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.cursos.index') }}">

            <div class="row g-3 align-items-end">

                {{-- Pesquisa --}}
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Pesquisar
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control border-start-0"
                               placeholder="Nome, código ou departamento..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Unidade --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Unidade Orgânica
                    </label>
                    <select name="unidade_id" class="form-select">
                        <option value="">Todas as Unidades</option>
                        @foreach($unidades as $unidade)
                            <option value="{{ $unidade->id }}"
                                {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                {{ $unidade->sigla }} — {{ $unidade->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Departamento --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Departamento
                    </label>
                    <select name="departamento" class="form-select">
                        <option value="">Todos os Departamentos</option>
                        @foreach($departamentos as $dept)
                            <option value="{{ $dept }}"
                                {{ request('departamento') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-lg-2 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        @if(request()->has('search') || request()->has('unidade_id') || request()->has('departamento'))
                            <a href="{{ route('admin.cursos.index') }}"
                               class="btn btn-outline-secondary flex-fill"
                               title="Limpar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </div>

        </form>
    </div>
</div>


{{-- ============================================================
     LISTA DE CURSOS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Lista de Cursos</h6>
                    <small class="text-muted">
                        {{ $totalCursos }} {{ $totalCursos === 1 ? 'curso' : 'cursos' }} encontrados
                    </small>
                </div>
            </div>

            <a href="{{ route('admin.cursos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Novo Curso
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Unidade</th>
                        <th>Código</th>
                        <th>Curso</th>
                        <th>Departamento</th>
                        <th class="text-center">Duração</th>
                        <th class="text-center">Egressos</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cursos as $curso)
                        <tr>
                            <td class="ps-4">
                                <span class="text-muted small">{{ $loop->iteration }}</span>
                            </td>

                            <td>
                                @if($curso->unidade)
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $curso->unidade->sigla }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        Sem unidade
                                    </span>
                                @endif
                            </td>

                            <td>
                                <code class="text-dark fw-bold">{{ $curso->codigo }}</code>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="course-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $curso->nome }}</strong>
                                        @if($curso->duracao)
                                            <small class="text-muted">
                                                {{ $curso->duracao }} {{ $curso->duracao == 1 ? 'ano' : 'anos' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($curso->departamento)
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                        {{ $curso->departamento }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($curso->duracao)
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $curso->duracao }} {{ $curso->duracao == 1 ? 'ano' : 'anos' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-success rounded-pill px-3">
                                    {{ $curso->egressos_count ?? 0 }}
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.cursos.edit', $curso->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.cursos.destroy', $curso->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este curso?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Nenhum curso encontrado</h6>
                                @if(request('unidade_id') || request('search') || request('departamento'))
                                    <p class="text-muted small mb-3">
                                        Tente ajustar os filtros aplicados.
                                    </p>
                                    <a href="{{ route('admin.cursos.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpar filtros
                                    </a>
                                @else
                                    <p class="text-muted small mb-3">
                                        Comece por registar o primeiro curso.
                                    </p>
                                    <a href="{{ route('admin.cursos.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Registo de Curso
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ============================================================
     RESUMO POR UNIDADE
============================================================ --}}
@if($totalCursos > 0 && $unidades->count() > 0)

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Resumo por Unidade Orgânica</h6>
                    <small class="text-muted">Distribuição de cursos por unidade</small>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-3">
                @foreach($unidades as $unidade)
                    @php
                        $count = $cursos->where('unidade_id', $unidade->id)->count();
                        $percentual = $totalCursos > 0 ? round(($count / $totalCursos) * 100, 1) : 0;
                    @endphp

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="unit-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    {{ $unidade->sigla }}
                                </span>
                                <span class="fw-bold text-primary">{{ $count }}</span>
                            </div>

                            <small class="text-muted d-block mb-2 text-truncate" title="{{ $unidade->nome }}">
                                {{ $unidade->nome }}
                            </small>

                            <div class="progress" style="height: 6px; border-radius: 10px;">
                                <div class="progress-bar bg-primary"
                                     role="progressbar"
                                     style="width: {{ $percentual }}%; border-radius: 10px;"
                                     aria-valuenow="{{ $percentual }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>

                            <small class="text-muted" style="font-size: 0.7rem;">
                                {{ $percentual }}% dos cursos
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endif

@endsection

