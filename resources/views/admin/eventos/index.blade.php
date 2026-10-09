@extends('layouts.admin')

@section('title', 'Eventos')

@section('page_title', '🎪 Gestão de Eventos')
@section('page_subtitle', 'Eventos, workshops e palestras da comunidade')

@section('content')

@php
    $total = $eventos->count();
    $ativos = $eventos->where('is_active', true)->count();
    $futuros = $eventos->filter(fn($e) => $e->data_inicio && $e->data_inicio >= now())->count();
    $totalInscricoes = $eventos->sum('inscricoes_count');

    $tiposMap = [
        'presencial' => ['color' => 'primary', 'icon' => 'map-marker-alt',   'label' => 'Presencial'],
        'online'     => ['color' => 'success', 'icon' => 'video',            'label' => 'Online'],
        'hibrido'    => ['color' => 'warning', 'icon' => 'broadcast-tower',  'label' => 'Híbrido'],
    ];

    $categoriasMap = [
        'workshop'   => ['color' => 'info',      'icon' => 'tools',           'label' => 'Workshop'],
        'palestra'   => ['color' => 'primary',   'icon' => 'microphone',      'label' => 'Palestra'],
        'networking' => ['color' => 'success',   'icon' => 'handshake',       'label' => 'Networking'],
        'job_fair'   => ['color' => 'warning',   'icon' => 'briefcase',       'label' => 'Feira de Emprego'],
        'curso'      => ['color' => 'secondary', 'icon' => 'book',            'label' => 'Curso'],
        'outro'      => ['color' => 'dark',      'icon' => 'star',            'label' => 'Outro'],
    ];
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total de Eventos</div>
                <small class="stat-desc">Registados no sistema</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $ativos }}</div>
                <div class="stat-label">Ativos</div>
                <small class="stat-desc">Disponíveis para inscrição</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $futuros }}</div>
                <div class="stat-label">Eventos Futuros</div>
                <small class="stat-desc">Por realizar</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalInscricoes }}</div>
                <div class="stat-label">Inscrições</div>
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
        <form method="GET" action="{{ route('admin.eventos.index') }}">

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
                               placeholder="Título, local ou descrição..."
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
                        <option value="">Todas</option>
                        <option value="workshop"   {{ request('categoria') == 'workshop'   ? 'selected' : '' }}>Workshop</option>
                        <option value="palestra"   {{ request('categoria') == 'palestra'   ? 'selected' : '' }}>Palestra</option>
                        <option value="networking" {{ request('categoria') == 'networking' ? 'selected' : '' }}>Networking</option>
                        <option value="job_fair"   {{ request('categoria') == 'job_fair'   ? 'selected' : '' }}>Feira de Emprego</option>
                        <option value="curso"      {{ request('categoria') == 'curso'      ? 'selected' : '' }}>Curso</option>
                        <option value="outro"      {{ request('categoria') == 'outro'      ? 'selected' : '' }}>Outro</option>
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

                {{-- Período --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Período
                    </label>
                    <select name="periodo" class="form-select">
                        <option value="">Todos</option>
                        <option value="futuros"  {{ request('periodo') == 'futuros'  ? 'selected' : '' }}>Futuros</option>
                        <option value="passados" {{ request('periodo') == 'passados' ? 'selected' : '' }}>Passados</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        @if(request()->hasAny(['search', 'tipo', 'categoria', 'unidade_id', 'periodo', 'is_active']))
                            <a href="{{ route('admin.eventos.index') }}" class="btn btn-outline-secondary">
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
     LISTA DE EVENTOS
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Lista de Eventos</h6>
                    <small class="text-muted">
                        {{ $total }} {{ $total === 1 ? 'evento' : 'eventos' }}
                    </small>
                </div>
            </div>

            <a href="{{ route('admin.eventos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Novo Evento
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Evento</th>
                        <th>Tipo</th>
                        <th>Categoria</th>
                        <th>Data</th>
                        <th class="text-center">Inscrições</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eventos as $evento)
                        @php
                            $tipoInfo = $tiposMap[$evento->tipo] ?? ['color' => 'secondary', 'icon' => 'calendar', 'label' => $evento->tipo];
                            $catInfo  = $categoriasMap[$evento->categoria] ?? ['color' => 'secondary', 'icon' => 'star', 'label' => $evento->categoria];

                            $passado = $evento->data_inicio && $evento->data_inicio < now();
                            $diasAte = (!$passado && $evento->data_inicio)
                                ? (int) now()->startOfDay()->diffInDays($evento->data_inicio->startOfDay(), false)
                                : null;
                        @endphp

                        <tr>
                            <td class="ps-4">
                                <span class="text-muted small">{{ $loop->iteration }}</span>
                            </td>

                            {{-- Evento + Info --}}
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="type-icon bg-{{ $tipoInfo['color'] }} bg-opacity-10 text-{{ $tipoInfo['color'] }}">
                                        <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                                    </div>
                                    <div class="min-width-0">
                                        <strong class="d-block text-truncate" style="max-width: 320px;">
                                            {{ $evento->titulo }}
                                        </strong>
                                        @if($evento->local)
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                <i class="fas fa-map-marker-alt me-1" style="font-size: 0.7rem;"></i>
                                                {{ $evento->local }}
                                            </small>
                                        @elseif($evento->link_reuniao)
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                <i class="fas fa-video me-1" style="font-size: 0.7rem;"></i>
                                                Evento online
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Tipo --}}
                            <td>
                                <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle">
                                    <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                    {{ $tipoInfo['label'] }}
                                </span>
                            </td>

                            {{-- Categoria --}}
                            <td>
                                <span class="badge bg-{{ $catInfo['color'] }}-subtle text-{{ $catInfo['color'] }}-emphasis border border-{{ $catInfo['color'] }}-subtle">
                                    <i class="fas fa-{{ $catInfo['icon'] }} me-1"></i>
                                    {{ $catInfo['label'] }}
                                </span>
                            </td>

                            {{-- Data --}}
                            <td>
                                @if($evento->data_inicio)
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1" style="font-size: 0.7rem;"></i>
                                            {{ $evento->data_inicio->format('d/m/Y') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                            {{ $evento->data_inicio->format('H:i') }}
                                        </small>

                                        @if($passado)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle mt-1"
                                                  style="font-size: 0.6rem;">
                                                Realizado
                                            </span>
                                        @elseif($diasAte !== null && $diasAte <= 7)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mt-1"
                                                  style="font-size: 0.6rem;">
                                                {{ $diasAte }} {{ $diasAte == 1 ? 'dia' : 'dias' }}
                                            </span>
                                        @elseif($diasAte !== null)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-1"
                                                  style="font-size: 0.6rem;">
                                                {{ $diasAte }} dias
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Inscrições --}}
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3">
                                    {{ $evento->inscricoes_count ?? 0 }}
                                </span>
                            </td>

                            {{-- Estado --}}
                            <td class="text-center">
                                @if($passado)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        <i class="fas fa-check me-1"></i> Realizado
                                    </span>
                                @elseif($evento->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <i class="fas fa-circle me-1" style="font-size: 0.4rem;"></i> Ativo
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-ban me-1"></i> Inativo
                                    </span>
                                @endif
                            </td>

                            {{-- ✅ AÇÕES (AQUI ESTÁ A MUDANÇA) --}}
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    {{-- ✅ BOTÃO INSCRITOS --}}
                                    <a href="{{ route('admin.eventos.inscritos', $evento->id) }}"
                                       class="btn btn-outline-info"
                                       title="Ver inscritos">
                                        <i class="fas fa-users"></i>
                                    </a>

                                    <a href="{{ route('admin.eventos.show', $evento->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.eventos.edit', $evento->id) }}"
                                       class="btn btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.eventos.destroy', $evento->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir este evento?');">
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
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Nenhum evento encontrado</h6>
                                @if(request()->hasAny(['search', 'tipo', 'categoria', 'unidade_id', 'periodo', 'is_active']))
                                    <p class="text-muted small mb-3">
                                        Tente ajustar os filtros aplicados.
                                    </p>
                                    <a href="{{ route('admin.eventos.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpar filtros
                                    </a>
                                @else
                                    <p class="text-muted small mb-3">
                                        Comece por registar o primeiro evento.
                                    </p>
                                    <a href="{{ route('admin.eventos.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i> Novo Evento
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


@push('styles')
<style>
    /* ============================================================
       STAT CARDS
    ============================================================ */
    .stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 16px;
        color: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    .stat-card-primary { background: linear-gradient(135deg, #1a56db, #3b82f6); }
    .stat-card-success { background: linear-gradient(135deg, #16a34a, #4ade80); }
    .stat-card-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .stat-card-info    { background: linear-gradient(135deg, #06b6d4, #0ea5e9); }

    .stat-icon-wrap {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .stat-body {
        flex-grow: 1;
        min-width: 0;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
        color: #ffffff;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #ffffff;
        opacity: 0.95;
    }

    .stat-desc {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.75);
    }

    /* ============================================================
       SECTION ICON
    ============================================================ */
    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* ============================================================
       REPORT TABLE
    ============================================================ */
    .report-table thead th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 700;
        border-bottom: 1px solid #e2e8f0;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .report-table tbody tr {
        transition: background 0.15s ease;
    }

    .report-table tbody tr:hover {
        background: #f8fafc;
    }

    .type-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    /* ============================================================
       EMPTY STATE
    ============================================================ */
    .empty-state-icon {
        font-size: 3.5rem;
        color: #cbd5e1;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */
    @media (max-width: 768px) {
        .stat-card {
            padding: 1rem 1.15rem;
        }

        .stat-icon-wrap {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>
@endpush