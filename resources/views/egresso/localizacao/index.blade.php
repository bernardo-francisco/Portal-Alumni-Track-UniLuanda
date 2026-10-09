@extends('layouts.egresso')

@section('title', 'Minha Localização')

@section('page_title', '📍 Minha Localização')
@section('page_subtitle', 'Gerencie as suas localizações e conecte-se com a comunidade')

@section('content')

@php
    $totalLocalizacoes = $localizacoes->count() ?? 0;
    $totalAtuais = $localizacoes->where('is_current', true)->count();
    $totalAnteriores = $totalLocalizacoes - $totalAtuais;

    $temFiltros = request()->hasAny(['pais', 'cidade', 'status', 'periodo']);
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-map-pin"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total Registadas</div>
                <div class="stat-card-value">{{ $totalLocalizacoes }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-database"></i>
                    Localizações no sistema
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Localização Atual</div>
                <div class="stat-card-value">{{ $totalAtuais }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-map-marker-alt"></i>
                    Onde está agora
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Localizações Anteriores</div>
                <div class="stat-card-value">{{ $totalAnteriores }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-clock"></i>
                    Histórico de locais
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
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h6 class="mb-1 fw-bold">
            <i class="fas fa-list text-primary me-2"></i>
            Localizações
        </h6>
        <small class="text-muted">
            {{ $totalLocalizacoes }} {{ $totalLocalizacoes === 1 ? 'registo' : 'registos' }}
            @if($temFiltros)
                · <span class="text-primary">filtros aplicados</span>
            @endif
        </small>
    </div>

    <a href="{{ route('egresso.localizacao.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Adicionar Localização
    </a>
</div>


{{-- ============================================================
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-filter"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">Filtros</h6>
                <small class="text-muted">Refine a lista de localizações</small>
            </div>
        </div>

        <form method="GET" action="{{ route('egresso.localizacao') }}">
            <div class="row g-3 align-items-end">

                {{-- País --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        País
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-globe text-muted"></i>
                        </span>
                        <input type="text"
                               name="pais"
                               class="form-control border-start-0"
                               placeholder="Filtrar por país..."
                               value="{{ request('pais') }}">
                    </div>
                </div>

                {{-- Cidade --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Cidade
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-city text-muted"></i>
                        </span>
                        <input type="text"
                               name="cidade"
                               class="form-control border-start-0"
                               placeholder="Filtrar por cidade..."
                               value="{{ request('cidade') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Estado
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="current" {{ request('status') == 'current' ? 'selected' : '' }}>Atual</option>
                        <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Anterior</option>
                    </select>
                </div>

                {{-- Período --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Período
                    </label>
                    <select name="periodo" class="form-select">
                        <option value="">Todos</option>
                        <option value="7d"   {{ request('periodo') == '7d'   ? 'selected' : '' }}>Últimos 7 dias</option>
                        <option value="30d"  {{ request('periodo') == '30d'  ? 'selected' : '' }}>Últimos 30 dias</option>
                        <option value="90d"  {{ request('periodo') == '90d'  ? 'selected' : '' }}>Últimos 90 dias</option>
                        <option value="2024" {{ request('periodo') == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2025" {{ request('periodo') == '2025' ? 'selected' : '' }}>2025</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-lg-2 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($temFiltros)
                            <a href="{{ route('egresso.localizacao') }}"
                               class="btn btn-outline-secondary flex-fill"
                               title="Limpar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>

        {{-- Badges de filtros ativos --}}
        @if($temFiltros)
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <small class="text-muted">
                        <i class="fas fa-filter me-1"></i>
                        Filtros ativos:
                    </small>

                    @if(request('pais'))
                        <span class="filter-badge">
                            <i class="fas fa-globe me-1"></i>
                            {{ request('pais') }}
                            <a href="{{ route('egresso.localizacao', array_merge(request()->except('pais'))) }}" class="filter-badge-close">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif

                    @if(request('cidade'))
                        <span class="filter-badge">
                            <i class="fas fa-city me-1"></i>
                            {{ request('cidade') }}
                            <a href="{{ route('egresso.localizacao', array_merge(request()->except('cidade'))) }}" class="filter-badge-close">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif

                    @if(request('status'))
                        <span class="filter-badge">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                            {{ request('status') == 'current' ? 'Atual' : 'Anterior' }}
                            <a href="{{ route('egresso.localizacao', array_merge(request()->except('status'))) }}" class="filter-badge-close">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif

                    @if(request('periodo'))
                        <span class="filter-badge">
                            <i class="fas fa-calendar me-1"></i>
                            {{ request('periodo') }}
                            <a href="{{ route('egresso.localizacao', array_merge(request()->except('periodo'))) }}" class="filter-badge-close">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif

                    <a href="{{ route('egresso.localizacao') }}"
                       class="btn btn-sm btn-outline-danger ms-auto">
                        <i class="fas fa-undo me-1"></i> Limpar todos
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>


{{-- ============================================================
     LISTA DE LOCALIZAÇÕES
============================================================ --}}
@if($totalLocalizacoes > 0)

    <div class="row g-4">
        @foreach($localizacoes as $loc)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100 location-card {{ $loc->is_current ? 'location-current' : 'location-past' }}">

                    {{-- ============================================================
                         HEADER
                    ============================================================ --}}
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex justify-content-between align-items-start gap-2">

                            <div class="d-flex align-items-center gap-2 min-width-0">
                                <div class="location-marker {{ $loc->is_current ? 'marker-current' : 'marker-past' }}">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>

                                <div class="min-width-0">
                                    <strong class="d-block text-truncate">
                                        {{ $loc->cidade ?? $loc->provincia ?? $loc->pais }}
                                    </strong>
                                    <small class="text-muted d-block text-truncate">
                                        <i class="fas fa-globe me-1" style="font-size: 0.7rem;"></i>
                                        {{ $loc->pais }}
                                    </small>
                                </div>
                            </div>

                            @if($loc->is_current)
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle flex-shrink-0">
                                    <i class="fas fa-check-circle me-1"></i> Atual
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle flex-shrink-0">
                                    <i class="fas fa-clock me-1"></i> Anterior
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- ============================================================
                         BODY
                    ============================================================ --}}
                    <div class="card-body p-4">

                        @if($loc->provincia)
                            <div class="location-info-row">
                                <div class="location-info-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-map"></i>
                                </div>
                                <div class="location-info-content">
                                    <small class="text-muted d-block">Província</small>
                                    <strong>{{ $loc->provincia }}</strong>
                                </div>
                            </div>
                        @endif

                        @if($loc->cidade)
                            <div class="location-info-row">
                                <div class="location-info-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-city"></i>
                                </div>
                                <div class="location-info-content">
                                    <small class="text-muted d-block">Cidade</small>
                                    <strong>{{ $loc->cidade }}</strong>
                                </div>
                            </div>
                        @endif

                        @if($loc->endereco)
                            <div class="location-info-row">
                                <div class="location-info-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="location-info-content">
                                    <small class="text-muted d-block">Endereço</small>
                                    <strong class="text-truncate d-block" style="max-width: 200px;">
                                        {{ $loc->endereco }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        @if($loc->latitude && $loc->longitude)
                            <div class="location-info-row">
                                <div class="location-info-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-crosshairs"></i>
                                </div>
                                <div class="location-info-content">
                                    <small class="text-muted d-block">Coordenadas</small>
                                    <strong class="font-monospace small">
                                        {{ number_format($loc->latitude, 4) }},
                                        {{ number_format($loc->longitude, 4) }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        @if($loc->data_desde)
                            <div class="location-info-row">
                                <div class="location-info-icon bg-secondary bg-opacity-10 text-secondary">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="location-info-content">
                                    <small class="text-muted d-block">Desde</small>
                                    <strong>{{ $loc->data_desde->format('d/m/Y') }}</strong>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- ============================================================
                         FOOTER
                    ============================================================ --}}
                    <div class="card-footer bg-white border-top py-2 px-3">
                        <div class="d-flex gap-2">
                            <a href="{{ route('egresso.localizacao.edit', $loc->id) }}"
                               class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>

                            <form action="{{ route('egresso.localizacao.destroy', $loc->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Tem certeza que deseja remover esta localização?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

@else

    {{-- ============================================================
         ESTADO VAZIO
    ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-map-marked-alt"></i>
            </div>

            <h5 class="fw-bold mb-2">
                @if($temFiltros)
                    Nenhuma localização encontrada
                @else
                    Nenhuma localização registada
                @endif
            </h5>

            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                @if($temFiltros)
                    Nenhuma localização corresponde aos filtros aplicados.
                @else
                    Adicione a sua localização atual para que outros egressos o possam encontrar.
                @endif
            </p>

            @if($temFiltros)
                <a href="{{ route('egresso.localizacao') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Limpar filtros
                </a>
            @else
                <a href="{{ route('egresso.localizacao.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Adicionar Localização
                </a>
            @endif
        </div>
    </div>

@endif

@endsection

