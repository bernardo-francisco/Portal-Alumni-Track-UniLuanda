@extends('layouts.egresso')

@section('title', 'Eventos')

@section('page_title', 'Eventos')
@section('page_subtitle', 'Participe dos eventos organizados pela universidade')

@section('content')

@php
    $totalEventos = $totalEventos ?? 0;
    $totalFuturos = $totalFuturos ?? 0;
    $totalPassados = $totalPassados ?? 0;
    $totalInscricoes = $totalInscricoes ?? 0;

    // ✅ NOVO — incluir 'ambito' nos filtros detetados
    $temFiltros = request()->hasAny(['search', 'tipo', 'categoria', 'ambito']);

    $tiposMap = [
        'presencial' => ['color' => 'success',   'icon' => 'building',   'label' => 'Presencial'],
        'online'     => ['color' => 'primary',   'icon' => 'wifi',       'label' => 'Online'],
        'hibrido'    => ['color' => 'warning',   'icon' => 'sync',       'label' => 'Híbrido'],
    ];

    $categoriasMap = [
        'workshop'   => ['color' => 'warning',   'icon' => 'tools',        'label' => 'Workshop'],
        'palestra'   => ['color' => 'primary',   'icon' => 'microphone',   'label' => 'Palestra'],
        'networking' => ['color' => 'success',   'icon' => 'handshake',    'label' => 'Networking'],
        'job_fair'   => ['color' => 'info',      'icon' => 'briefcase',    'label' => 'Feira de Emprego'],
        'curso'      => ['color' => 'secondary', 'icon' => 'book',         'label' => 'Curso'],
        'outro'      => ['color' => 'dark',      'icon' => 'star',         'label' => 'Outro'],
    ];
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total de Eventos</div>
                <div class="stat-card-value">{{ $totalEventos }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-database"></i>
                    Registados no sistema
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Eventos Futuros</div>
                <div class="stat-card-value">{{ $totalFuturos }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-circle" style="font-size: 0.4rem; color: #16a34a;"></i>
                    Disponíveis para inscrição
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Eventos Passados</div>
                <div class="stat-card-value">{{ $totalPassados }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-check"></i>
                    Já realizados
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('egresso.minhas.inscricoes') }}" class="text-decoration-none">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Minhas Inscrições</div>
                    <div class="stat-card-value">{{ $totalInscricoes }}</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-right"></i>
                        Ver todas as inscrições
                    </div>
                </div>
            </div>
        </a>
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

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


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
                <small class="text-muted">Encontre o evento perfeito para si</small>
            </div>
        </div>

        <form action="{{ route('egresso.eventos') }}" method="GET">
            <div class="row g-3 align-items-end">

                {{-- Busca --}}
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
                               placeholder="Título, descrição ou local..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- ✅ NOVO — Âmbito (só se o egresso tiver unidade) --}}
                @if(isset($unidadeEgressoId) && $unidadeEgressoId)
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-semibold small text-muted text-uppercase">
                            Âmbito
                        </label>
                        <select name="ambito" class="form-select">
                            <option value="">Todos</option>
                            <option value="minha"  {{ request('ambito') == 'minha'  ? 'selected' : '' }}>Da minha unidade</option>
                            <option value="global" {{ request('ambito') == 'global' ? 'selected' : '' }}>Globais</option>
                        </select>
                    </div>
                @endif

                {{-- Tipo --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Tipo
                    </label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos os tipos</option>
                        <option value="presencial" {{ request('tipo') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="online"     {{ request('tipo') == 'online'     ? 'selected' : '' }}>Online</option>
                        <option value="hibrido"    {{ request('tipo') == 'hibrido'    ? 'selected' : '' }}>Híbrido</option>
                    </select>
                </div>

                {{-- Categoria --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Categoria
                    </label>
                    <select name="categoria" class="form-select">
                        <option value="">Todas as categorias</option>
                        <option value="workshop"   {{ request('categoria') == 'workshop'   ? 'selected' : '' }}>Workshop</option>
                        <option value="palestra"   {{ request('categoria') == 'palestra'   ? 'selected' : '' }}>Palestra</option>
                        <option value="networking" {{ request('categoria') == 'networking' ? 'selected' : '' }}>Networking</option>
                        <option value="job_fair"   {{ request('categoria') == 'job_fair'   ? 'selected' : '' }}>Feira de Emprego</option>
                        <option value="curso"      {{ request('categoria') == 'curso'      ? 'selected' : '' }}>Curso</option>
                        <option value="outro"      {{ request('categoria') == 'outro'      ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-lg-2 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($temFiltros)
                            <a href="{{ route('egresso.eventos') }}"
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
     LISTA DE EVENTOS
============================================================ --}}
@if(isset($eventos) && $eventos->count() > 0)

    <div class="row g-4">

        @foreach($eventos as $evento)
            @php
                $tipoInfo = $tiposMap[$evento->tipo] ?? ['color' => 'secondary', 'icon' => 'calendar', 'label' => ucfirst($evento->tipo)];
                $catInfo  = $categoriasMap[$evento->categoria] ?? ['color' => 'secondary', 'icon' => 'star', 'label' => ucfirst($evento->categoria)];

                $passado = $evento->data_inicio && $evento->data_inicio < now();
                $jaInscrito = isset($eventosInscritos) && in_array($evento->id, $eventosInscritos);

                $inscritos = $evento->inscricoes_count ?? 0;
                $maximo = $evento->max_participantes;
                $lotado = $maximo && $inscritos >= $maximo;
                $percentual = $maximo ? round(($inscritos / $maximo) * 100) : 0;

                $corProgresso = $percentual < 50 ? 'success' : ($percentual < 80 ? 'warning' : 'danger');

                $diasAte = (!$passado && $evento->data_inicio)
                ? (int) now()->startOfDay()->diffInDays($evento->data_inicio->startOfDay(), false)
                : null;
            @endphp

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 evento-card overflow-hidden">

                    {{-- HEADER COM BADGES --}}
                    <div class="evento-header">
                        <div class="evento-date-badge">
                            <div class="evento-date-day">
                                {{ $evento->data_inicio?->format('d') ?? '--' }}
                            </div>
                            <div class="evento-date-month">
                                {{ $evento->data_inicio?->format('M') ?? '---' }}
                            </div>
                        </div>

                        <div class="evento-badges">
                            <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                {{ $tipoInfo['label'] }}
                            </span>

                            {{-- ✅ NOVO — Badge de unidade --}}
                            @if($evento->unidade)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="fas fa-university me-1"></i>
                                    {{ $evento->unidade->sigla }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    <i class="fas fa-globe me-1"></i>
                                    Todas
                                </span>
                            @endif
                        </div>
                    </div>


                    {{-- BODY --}}
                    <div class="card-body pt-2 pb-3 px-4">

                        {{-- Categoria --}}
                        <span class="badge bg-{{ $catInfo['color'] }}-subtle text-{{ $catInfo['color'] }}-emphasis border border-{{ $catInfo['color'] }}-subtle mb-2">
                            <i class="fas fa-{{ $catInfo['icon'] }} me-1"></i>
                            {{ $catInfo['label'] }}
                        </span>

                        {{-- Título --}}
                        <h6 class="evento-title">
                            {{ $evento->titulo }}
                        </h6>

                        {{-- Descrição --}}
                        <p class="evento-description">
                            {{ $evento->descricao ? Str::limit($evento->descricao, 110) : 'Sem descrição.' }}
                        </p>

                        {{-- Info items --}}
                        <div class="evento-info">

                            @if($evento->data_inicio)
                                <div class="info-item">
                                    <div class="info-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Data e Hora</small>
                                        <strong class="small">
                                            {{ $evento->data_inicio->format('d/m/Y \à\s H:i') }}
                                        </strong>
                                    </div>
                                </div>
                            @endif

                            @if($evento->local)
                                <div class="info-item">
                                    <div class="info-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Local</small>
                                        <strong class="small text-truncate d-block" style="max-width: 220px;">
                                            {{ $evento->local }}
                                        </strong>
                                    </div>
                                </div>
                            @elseif($evento->link_reuniao)
                                <div class="info-item">
                                    <div class="info-icon bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-video"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Formato</small>
                                        <strong class="small">Online</strong>
                                    </div>
                                </div>
                            @endif

                        </div>


                        {{-- PROGRESSO DE VAGAS --}}
                        @if($maximo)
                            <div class="evento-progress mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-users text-primary me-1"></i>
                                        <strong>{{ $inscritos }}</strong> inscritos
                                    </small>
                                    <small class="text-muted">
                                        Máx: <strong>{{ $maximo }}</strong>
                                    </small>
                                </div>

                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar bg-{{ $corProgresso }}"
                                         role="progressbar"
                                         style="width: {{ min($percentual, 100) }}%; border-radius: 10px;">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        {{ $percentual }}% preenchido
                                    </small>
                                    @if($lotado)
                                        <small class="text-danger fw-bold" style="font-size: 0.7rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i> Lotado
                                        </small>
                                    @else
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            {{ $maximo - $inscritos }} vagas restantes
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="evento-progress mt-3 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="fas fa-users text-primary me-1"></i>
                                    <strong>{{ $inscritos }}</strong> inscritos
                                    <span class="ms-2">·</span>
                                    <span class="ms-2">Vagas ilimitadas</span>
                                </small>
                            </div>
                        @endif

                    </div>


                    {{-- FOOTER — AÇÕES --}}
                    <div class="card-footer bg-white border-top py-2 px-3">

                        <div class="d-flex gap-2">

                            {{-- ✅ Botão: Ver Detalhes (SEMPRE presente) --}}
                            <a href="{{ route('egresso.eventos.show', $evento->id) }}"
                               class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-eye me-1"></i> Detalhes
                            </a>

                            {{-- Botões de ação --}}
                            @if($passado)

                                <button type="button"
                                        class="btn btn-secondary-subtle text-secondary-emphasis border border-secondary-subtle flex-fill"
                                        disabled>
                                    <i class="fas fa-clock me-1"></i> Finalizado
                                </button>

                            @elseif($jaInscrito)

                                <a href="{{ route('egresso.minhas.inscricoes') }}"
                                   class="btn btn-success-subtle text-success-emphasis border border-success-subtle flex-fill"
                                   title="Ver comprovativo">
                                    <i class="fas fa-qrcode me-1"></i> Inscrito
                                </a>

                            @elseif($lotado)

                                <button type="button"
                                        class="btn btn-danger-subtle text-danger-emphasis border border-danger-subtle flex-fill"
                                        disabled>
                                    <i class="fas fa-times-circle me-1"></i> Lotado
                                </button>

                            @else

                                <form action="{{ route('egresso.eventos.inscrever', $evento->id) }}"
                                      method="POST"
                                      class="flex-fill"
                                      onsubmit="return confirmarInscricao(this);">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 btn-inscrever">
                                        <i class="fas fa-plus-circle me-1"></i> Inscrever
                                    </button>
                                </form>

                            @endif

                        </div>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

    {{-- Paginação --}}
    @if(method_exists($eventos, 'hasPages') && $eventos->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $eventos->appends(request()->query())->links() }}
        </div>
    @endif

@else

    {{-- ============================================================
         ESTADO VAZIO
    ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h5 class="fw-bold mb-2">
                @if($temFiltros)
                    Nenhum evento encontrado
                @else
                    Nenhum evento disponível
                @endif
            </h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                @if($temFiltros)
                    Nenhum evento corresponde aos filtros aplicados. Tente ajustar.
                @else
                    Volte em breve para novos eventos organizados pela universidade.
                @endif
            </p>

            @if($temFiltros)
                <a href="{{ route('egresso.eventos') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Limpar filtros
                </a>
            @endif
        </div>
    </div>

@endif

@endsection