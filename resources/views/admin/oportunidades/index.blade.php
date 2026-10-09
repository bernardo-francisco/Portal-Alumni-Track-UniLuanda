@extends('layouts.admin')

@section('title', 'Oportunidades')

@section('page_title', '💼 Gestão de Oportunidades')
@section('page_subtitle', 'Vagas, estágios, bolsas e outras oportunidades')

@section('content')

@php
    // Estatísticas rápidas
    $total = $oportunidades->count();
    $ativas = $oportunidades->where('is_active', true)->count();
    $expiradas = $oportunidades->filter(function ($op) {
        return $op->data_limite && $op->data_limite < now();
    })->count();
    $totalCandidaturas = $oportunidades->sum('candidaturas_count');

    // Mapeamento tipo → cor e ícone
    $tiposMap = [
        'emprego' => ['color' => 'primary',   'icon' => 'briefcase'],
        'estagio' => ['color' => 'info',      'icon' => 'user-graduate'],
        'bolsa'   => ['color' => 'success',   'icon' => 'award'],
        'curso'   => ['color' => 'warning',   'icon' => 'book'],
        'evento'  => ['color' => 'secondary', 'icon' => 'calendar'],
    ];
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total de Oportunidades</div>
                <small class="stat-desc">Registadas no sistema</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $ativas }}</div>
                <div class="stat-label">Ativas</div>
                <small class="stat-desc">Disponíveis para candidatura</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon-wrap">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $expiradas }}</div>
                <div class="stat-label">Prazo Expirado</div>
                <small class="stat-desc">Já não aceitam candidaturas</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalCandidaturas }}</div>
                <div class="stat-label">Candidaturas</div>
                <small class="stat-desc">Total recebidas</small>
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
        <form method="GET" action="{{ route('admin.oportunidades.index') }}">

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
                               placeholder="Título, empresa ou descrição..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Tipo --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Tipo
                    </label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="emprego" {{ request('tipo') == 'emprego' ? 'selected' : '' }}>Emprego</option>
                        <option value="estagio" {{ request('tipo') == 'estagio' ? 'selected' : '' }}>Estágio</option>
                        <option value="bolsa"   {{ request('tipo') == 'bolsa'   ? 'selected' : '' }}>Bolsa</option>
                        <option value="curso"   {{ request('tipo') == 'curso'   ? 'selected' : '' }}>Curso</option>
                        <option value="evento"  {{ request('tipo') == 'evento'  ? 'selected' : '' }}>Evento</option>
                    </select>
                </div>

                {{-- Unidade --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Unidade
                    </label>
                    <select name="unidade_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($unidades as $unidade)
                            <option value="{{ $unidade->id }}"
                                {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                {{ $unidade->sigla }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Status
                    </label>
                    <select name="is_active" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Ativa</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inativa</option>
                    </select>
                </div>

                {{-- Prazo --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Prazo
                    </label>
                    <select name="prazo" class="form-select">
                        <option value="">Todos</option>
                        <option value="com_prazo" {{ request('prazo') == 'com_prazo' ? 'selected' : '' }}>Com Prazo</option>
                        <option value="sem_prazo" {{ request('prazo') == 'sem_prazo' ? 'selected' : '' }}>Sem Prazo</option>
                        <option value="expirado"  {{ request('prazo') == 'expirado'  ? 'selected' : '' }}>Expirado</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        @if(request()->hasAny(['search', 'tipo', 'unidade_id', 'is_active', 'prazo']))
                            <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpar filtros
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Aplicar filtros
                        </button>
                    </div>
                </div>

            </div>

        </form>
    </div>
</div>


{{-- ============================================================
     LISTA DE OPORTUNIDADES
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Lista de Oportunidades</h6>
                    <small class="text-muted">
                        {{ $total }} {{ $total === 1 ? 'oportunidade' : 'oportunidades' }}
                    </small>
                </div>
            </div>

            <a href="{{ route('admin.oportunidades.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nova Oportunidade
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Oportunidade</th>
                        <th>Tipo</th>
                        <th>Empresa</th>
                        <th class="text-center">Candidaturas</th>
                        <th>Prazo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oportunidades as $op)
                        @php
                            $tipoInfo = $tiposMap[$op->tipo] ?? ['color' => 'secondary', 'icon' => 'briefcase'];

                            $expirado = $op->data_limite && $op->data_limite < now();
                            $diasRestantes = ($op->data_limite && !$expirado)
                                ? now()->diffInDays($op->data_limite, false)
                                : null;
                        @endphp

                        <tr>
                            <td class="ps-4">
                                <span class="text-muted small">{{ $loop->iteration }}</span>
                            </td>

                            {{-- Oportunidade + ícone --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="type-icon bg-{{ $tipoInfo['color'] }} bg-opacity-10 text-{{ $tipoInfo['color'] }}">
                                        <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                                    </div>
                                    <div class="min-width-0">
                                        <strong class="d-block text-truncate" style="max-width: 320px;">
                                            {{ $op->titulo }}
                                        </strong>
                                        @if($op->localizacao)
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                <i class="fas fa-map-marker-alt me-1" style="font-size: 0.7rem;"></i>
                                                {{ $op->localizacao }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Tipo --}}
                            <td>
                                <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                    <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                    {{ ucfirst($op->tipo ?? '') }}
                                </span>
                            </td>

                            {{-- Empresa --}}
                            <td>
                                @if($op->empresa)
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="fas fa-building text-muted" style="font-size: 0.75rem;"></i>
                                        {{ $op->empresa }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Candidaturas --}}
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3">
                                    {{ $op->candidaturas_count ?? 0 }}
                                </span>
                            </td>

                            {{-- Prazo --}}
                            <td>
                                @if($op->data_limite)
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1" style="font-size: 0.7rem;"></i>
                                            {{ $op->data_limite->format('d/m/Y') }}
                                        </small>

                                        @if($expirado)
                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle" style="font-size: 0.6rem;">
                                                Expirado
                                            </span>
                                        @elseif($diasRestantes !== null && $diasRestantes <= 7)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.6rem;">
                                                {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'dia' : 'dias' }}
                                            </span>
                                        @elseif($diasRestantes !== null)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.6rem;">
                                                {{ $diasRestantes }} dias
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        Sem prazo
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($op->is_active && !$expirado)
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <i class="fas fa-circle me-1" style="font-size: 0.4rem;"></i> Ativa
                                    </span>
                                @elseif($expirado)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="fas fa-clock me-1"></i> Expirado
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        <i class="fas fa-ban me-1"></i> Inativa
                                    </span>
                                @endif
                            </td>

                            {{-- Ações --}}
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.oportunidades.show', $op->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.oportunidades.edit', $op->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.oportunidades.destroy', $op->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta oportunidade?');">
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
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Nenhuma oportunidade encontrada</h6>
                                @if(request()->hasAny(['search', 'tipo', 'unidade_id', 'is_active', 'prazo']))
                                    <p class="text-muted small mb-3">
                                        Tente ajustar os filtros aplicados.
                                    </p>
                                    <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpar filtros
                                    </a>
                                @else
                                    <p class="text-muted small mb-3">
                                        Comece por registar a primeira oportunidade.
                                    </p>
                                    <a href="{{ route('admin.oportunidades.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Nova Oportunidade
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

@endsection

