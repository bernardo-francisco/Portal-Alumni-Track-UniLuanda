@extends('layouts.egresso')

@section('title', 'Oportunidades')

@section('page_title', '💼 Oportunidades')
@section('page_subtitle', 'Encontre vagas, estágios e desenvolvimento profissional')

@section('content')

@php
    $totalOportunidades = isset($oportunidades) ? $oportunidades->total() : 0;
    $totalCandidaturas  = isset($candidaturas) ? count($candidaturas) : 0;

    // ✅ NOVO — incluir 'ambito' nos filtros detetados
    $temFiltros = request()->hasAny(['search', 'tipo', 'local', 'empresa', 'ambito']);

    // Mapeamento tipo → cor, ícone e label
    $tiposMap = [
        'emprego'  => ['color' => 'primary',   'icon' => 'briefcase',       'label' => 'Emprego'],
        'estagio'  => ['color' => 'info',      'icon' => 'user-graduate',   'label' => 'Estágio'],
        'estágio'  => ['color' => 'info',      'icon' => 'user-graduate',   'label' => 'Estágio'],
        'bolsa'    => ['color' => 'success',   'icon' => 'award',           'label' => 'Bolsa'],
        'curso'    => ['color' => 'warning',   'icon' => 'book',            'label' => 'Curso'],
        'evento'   => ['color' => 'secondary', 'icon' => 'calendar',        'label' => 'Evento'],
        'empresa'  => ['color' => 'primary',   'icon' => 'building',        'label' => 'Empresa'],
    ];
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Oportunidades Ativas</div>
                <div class="stat-card-value">{{ $totalOportunidades }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-circle" style="font-size: 0.4rem; color: #16a34a;"></i>
                    Disponíveis para candidatura
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
                <div class="stat-card-label">Já Candidatado</div>
                <div class="stat-card-value">
                    {{ isset($candidaturas) ? count($candidaturas) : 0 }}
                </div>
                <div class="stat-card-desc">
                    <i class="fas fa-paper-plane"></i>
                    Candidaturas enviadas
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">A Terminar Prazo</div>
                <div class="stat-card-value">
                    {{ isset($oportunidades) ? $oportunidades->filter(function($op) {
                        return $op->data_limite && \Carbon\Carbon::parse($op->data_limite)->diffInDays(now(), false) >= -3 && \Carbon\Carbon::parse($op->data_limite)->isFuture();
                    })->count() : 0 }}
                </div>
                <div class="stat-card-desc">
                    <i class="fas fa-clock"></i>
                    Últimos 3 dias
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('egresso.minhas.candidaturas') }}" class="text-decoration-none">
            <div class="stat-card stat-card-secondary">
                <div class="stat-card-icon">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Minhas Candidaturas</div>
                    <div class="stat-card-value">{{ $totalCandidaturas }}</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-right"></i>
                        Ver todas
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
                <small class="text-muted">Encontre a oportunidade perfeita para si</small>
            </div>
        </div>

        <form action="{{ route('egresso.oportunidades') }}" method="GET">
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
                               placeholder="Título, empresa ou descrição..."
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
                        <option value="emprego" {{ request('tipo') == 'emprego' ? 'selected' : '' }}>Emprego</option>
                        <option value="estagio" {{ request('tipo') == 'estagio' ? 'selected' : '' }}>Estágio</option>
                        <option value="bolsa"   {{ request('tipo') == 'bolsa'   ? 'selected' : '' }}>Bolsa</option>
                        <option value="curso"   {{ request('tipo') == 'curso'   ? 'selected' : '' }}>Curso</option>
                    </select>
                </div>

                {{-- Empresa --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Empresa
                    </label>
                    <input type="text"
                           name="empresa"
                           class="form-control"
                           placeholder="Nome da empresa..."
                           value="{{ request('empresa') }}">
                </div>

                {{-- Botões --}}
                <div class="col-lg-2 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($temFiltros)
                            <a href="{{ route('egresso.oportunidades') }}"
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
     LISTA DE OPORTUNIDADES
============================================================ --}}
@if(isset($oportunidades) && $oportunidades->count() > 0)

    <div class="row g-4">

        @foreach($oportunidades as $op)
            @php
                $tipo = strtolower($op->tipo ?? 'oportunidade');
                $tipoInfo = $tiposMap[$tipo] ?? ['color' => 'secondary', 'icon' => 'star', 'label' => ucfirst($tipo)];

                $jaCandidatou = isset($candidaturas) && in_array($op->id, $candidaturas);

                // Prazo
                $expirado = $op->data_limite && \Carbon\Carbon::parse($op->data_limite)->isPast();
                $diasAte = (!$expirado && $op->data_limite)
                    ? now()->diffInDays(\Carbon\Carbon::parse($op->data_limite), false)
                    : null;

                // Candidaturas recebidas
                $totalCandidatos = $op->candidaturas_count ?? 0;
            @endphp

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 oportunidade-card overflow-hidden oportunidade-{{ $tipoInfo['color'] }}">

                    {{-- ============================================================
                         HEADER — ÍCONE DO TIPO + BADGES
                    ============================================================ --}}
                    <div class="oportunidade-header">
                        <div class="oportunidade-type-badge type-{{ $tipoInfo['color'] }}">
                            <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                        </div>

                        <div class="oportunidade-badges">
                            <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                {{ $tipoInfo['label'] }}
                            </span>

                            {{-- ✅ NOVO — Badge de unidade --}}
                            @if($op->unidade)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="fas fa-university me-1"></i>
                                    {{ $op->unidade->sigla }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    <i class="fas fa-globe me-1"></i>
                                    Todas
                                </span>
                            @endif

                            @if($jaCandidatou)
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                    <i class="fas fa-check-circle me-1"></i> Candidatado
                                </span>
                            @elseif($expirado)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                    <i class="fas fa-lock me-1"></i> Expirado
                                </span>
                            @endif
                        </div>
                    </div>


                    {{-- ============================================================
                         BODY
                    ============================================================ --}}
                    <div class="card-body pt-2 pb-3 px-4">

                        {{-- Título --}}
                        <h6 class="oportunidade-title">
                            {{ $op->titulo }}
                        </h6>

                        {{-- Descrição --}}
                        <p class="oportunidade-description">
                            {{ $op->descricao ? Str::limit($op->descricao, 110) : 'Sem descrição.' }}
                        </p>

                        {{-- Info items --}}
                        <div class="oportunidade-info">

                            @if($op->empresa)
                                <div class="info-item">
                                    <div class="info-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Empresa</small>
                                        <strong class="small text-truncate d-block" style="max-width: 220px;">
                                            {{ $op->empresa }}
                                        </strong>
                                    </div>
                                </div>
                            @endif

                            @if($op->localizacao || $op->local)
                                <div class="info-item">
                                    <div class="info-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Localização</small>
                                        <strong class="small text-truncate d-block" style="max-width: 220px;">
                                            {{ $op->localizacao ?? $op->local }}
                                        </strong>
                                    </div>
                                </div>
                            @endif

                            @if($op->data_limite)
                                <div class="info-item">
                                    <div class="info-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Prazo de candidatura</small>
                                        <strong class="small">
                                            {{ \Carbon\Carbon::parse($op->data_limite)->format('d/m/Y') }}
                                        </strong>
                                    </div>
                                </div>
                            @endif

                        </div>


                        {{-- ============================================================
                             PROGRESSO DE PRAZO
                        ============================================================ --}}
                        @if($op->data_limite)
                            @php
                                $totalDias = 30;
                                $diasRestantes = $diasAte !== null ? max(0, $diasAte) : 0;
                                $percentual = $totalDias > 0 ? min(100, round((($totalDias - $diasRestantes) / $totalDias) * 100)) : 0;
                                $corProgresso = $diasRestantes > 10 ? 'success' : ($diasRestantes > 3 ? 'warning' : 'danger');
                            @endphp

                            <div class="oportunidade-progress mt-3 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-hourglass-half text-warning me-1"></i>
                                        @if($expirado)
                                            <strong>Prazo terminado</strong>
                                        @else
                                            <strong>{{ $diasRestantes }}</strong> dias restantes
                                        @endif
                                    </small>
                                    <small class="text-muted">
                                        Total: <strong>{{ $totalDias }} dias</strong>
                                    </small>
                                </div>

                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar bg-{{ $corProgresso }}"
                                         role="progressbar"
                                         style="width: {{ $percentual }}%; border-radius: 10px;">
                                    </div>
                                </div>

                                @if($diasAte !== null && $diasAte <= 3 && $diasAte >= 0)
                                    <div class="mt-2 text-end">
                                        <small class="text-danger fw-bold" style="font-size: 0.7rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $diasAte == 0 ? 'Último dia!' : 'Apenas ' . $diasAte . ' dias!' }}
                                        </small>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>


                    {{-- ============================================================
                         FOOTER — AÇÕES
                    ============================================================ --}}
                    <div class="card-footer bg-white border-top py-2 px-3">
                        <div class="d-flex gap-2">

                            {{-- Botão: Ver Detalhes --}}
                            <a href="{{ route('egresso.oportunidades.show', $op->id) }}"
                            class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-eye me-1"></i> Detalhes
                            </a>

                            {{-- Botões de ação --}}
                            @if($jaCandidatou)
                                <button type="button" class="btn btn-success-subtle text-success-emphasis border border-success-subtle flex-fill" disabled>
                                    <i class="fas fa-check-circle me-1"></i> Candidatado
                                </button>
                            @elseif($expirado)
                                <button type="button" class="btn btn-secondary-subtle text-secondary-emphasis border border-secondary-subtle flex-fill" disabled>
                                    <i class="fas fa-lock me-1"></i> Expirado
                                </button>
                            @else
                                <form action="{{ route('egresso.oportunidades.candidatar', ['oportunidade' => $op->id]) }}"
                                    method="POST"
                                    class="flex-fill"
                                    onsubmit="return confirmarCandidatura(this);">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 btn-candidatar">
                                        <i class="fas fa-paper-plane me-1"></i> Candidatar
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
    @if(method_exists($oportunidades, 'hasPages') && $oportunidades->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $oportunidades->appends(request()->query())->links() }}
        </div>
    @endif

@else

    {{-- ============================================================
         ESTADO VAZIO
    ============================================================ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-briefcase"></i>
            </div>
            <h5 class="fw-bold mb-2">
                @if($temFiltros)
                    Nenhuma oportunidade encontrada
                @else
                    Nenhuma oportunidade disponível
                @endif
            </h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                @if($temFiltros)
                    Nenhuma oportunidade corresponde aos filtros aplicados. Tente ajustar.
                @else
                    De momento não há oportunidades disponíveis. Volte em breve para consultar novas vagas.
                @endif
            </p>

            @if($temFiltros)
                <a href="{{ route('egresso.oportunidades') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Limpar filtros
                </a>
            @else
                <a href="{{ route('egresso.minhas.candidaturas') }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-alt me-1"></i> Ver Minhas Candidaturas
                </a>
            @endif
        </div>
    </div>

@endif

@endsection