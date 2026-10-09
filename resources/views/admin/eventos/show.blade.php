@extends('layouts.admin')

@section('title', 'Evento - ' . $evento->titulo)

@section('page_title', '🎪 Detalhes do Evento')
@section('page_subtitle', $evento->titulo)

@section('content')

@php
    // Mapeamento tipo → ícone e cor
    $tipos = [
        'presencial' => ['icon' => 'map-marker-alt', 'color' => 'primary', 'label' => 'Presencial'],
        'online'     => ['icon' => 'video',          'color' => 'success', 'label' => 'Online'],
        'hibrido'    => ['icon' => 'broadcast-tower','color' => 'warning', 'label' => 'Híbrido'],
    ];

    $tipo = $tipos[$evento->tipo] ?? $tipos['presencial'];

    // Mapeamento categoria → cor
    $categorias = [
        'workshop'   => 'info',
        'palestra'   => 'primary',
        'networking' => 'success',
        'job_fair'   => 'warning',
        'curso'      => 'secondary',
        'outro'      => 'dark',
    ];
    $categoriaCor = $categorias[$evento->categoria] ?? 'secondary';
    $categoriaLabel = [
        'job_fair' => 'Feira de Emprego',
    ][$evento->categoria] ?? ucfirst(str_replace('_', ' ', $evento->categoria ?? ''));

    // Verificar se o evento já passou
    $passado = $evento->data_inicio && $evento->data_inicio < now();

    // Verificar se está a acontecer agora
    $aDecorrer = $evento->data_inicio && $evento->data_fim
        && now()->between($evento->data_inicio, $evento->data_fim);

    // Dias até o evento
    $diasAte = null;
    if ($evento->data_inicio && !$passado) {
        $diasAte = now()->diffInDays($evento->data_inicio, false);
    }

    // Taxa de ocupação
    $taxaOcupacao = null;
    $inscritos = $evento->inscricoes_count ?? 0;
    if ($evento->max_participantes && $evento->max_participantes > 0) {
        $taxaOcupacao = round(($inscritos / $evento->max_participantes) * 100, 1);
    }
@endphp

{{-- ============================================================
     HERO CARD
============================================================ --}}
<div class="card border-0 shadow-sm hero-card mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">

            {{-- Ícone --}}
            <div class="col-auto">
                <div class="hero-icon bg-{{ $tipo['color'] }}">
                    <i class="fas fa-{{ $tipo['icon'] }}"></i>
                </div>
            </div>

            {{-- Título + badges --}}
            <div class="col">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-{{ $tipo['color'] }}-subtle text-{{ $tipo['color'] }}-emphasis border border-{{ $tipo['color'] }}-subtle">
                        <i class="fas fa-{{ $tipo['icon'] }} me-1"></i>
                        {{ $tipo['label'] }}
                    </span>

                    <span class="badge bg-{{ $categoriaCor }}-subtle text-{{ $categoriaCor }}-emphasis border border-{{ $categoriaCor }}-subtle">
                        <i class="fas fa-tag me-1"></i>
                        {{ $categoriaLabel }}
                    </span>

                    @if($aDecorrer)
                        <span class="badge bg-danger">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                            A decorrer agora
                        </span>
                    @elseif($passado)
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                            <i class="fas fa-check me-1"></i> Já realizado
                        </span>
                    @elseif($evento->is_active)
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Ativo
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                            <i class="fas fa-ban me-1"></i> Inativo
                        </span>
                    @endif

                    @if($diasAte !== null)
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                            <i class="fas fa-hourglass-half me-1"></i>
                            {{ $diasAte }} {{ $diasAte == 1 ? 'dia' : 'dias' }} restantes
                        </span>
                    @endif
                </div>

                <h2 class="fw-bold mb-1">{{ $evento->titulo }}</h2>

                <p class="text-muted mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ $evento->data_inicio?->format('d \d\e F \d\e Y, H:i') ?? 'Data não definida' }}

                    @if($evento->unidade)
                        <span class="text-muted"> · {{ $evento->unidade->sigla }}</span>
                    @endif
                </p>
            </div>

            {{-- Ações --}}
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.eventos.edit', $evento->id) }}"
                       class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>

                    <a href="{{ route('admin.eventos.inscritos', $evento->id) }}"
                       class="btn btn-info text-white">
                        <i class="fas fa-users me-1"></i> Inscrições
                    </a>

                    <form action="{{ route('admin.eventos.destroy', $evento->id) }}"
                          method="POST"
                          onsubmit="return confirm('Tem certeza que deseja eliminar este evento?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                    <a href="{{ route('admin.eventos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- ============================================================
     ESTATÍSTICAS RÁPIDAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $inscritos }}</div>
                <div class="stat-label">Inscrições</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $evento->max_participantes ?? 'Ilimitado' }}
                </div>
                <div class="stat-label">Capacidade</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $evento->local ?? ($evento->tipo == 'online' ? 'Online' : 'Não definido') }}
                </div>
                <div class="stat-label">Local</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-{{ $passado ? 'secondary' : 'warning' }} bg-opacity-10 text-{{ $passado ? 'secondary' : 'warning' }}">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $evento->data_inicio ? $evento->data_inicio->format('d/m/Y') : '-' }}
                </div>
                <div class="stat-label">{{ $passado ? 'Data Realização' : 'Data Início' }}</div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     PROGRESSO DE OCUPAÇÃO (se aplicável)
============================================================ --}}
@if($taxaOcupacao !== null)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Ocupação do Evento</h6>
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-4 text-primary">{{ $taxaOcupacao }}%</span>
                    <div class="small text-muted">
                        {{ $inscritos }} de {{ $evento->max_participantes }}
                    </div>
                </div>
            </div>

            <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-gradient bg-{{ $taxaOcupacao >= 90 ? 'danger' : ($taxaOcupacao >= 70 ? 'warning' : 'success') }}"
                     role="progressbar"
                     style="width: {{ min($taxaOcupacao, 100) }}%; border-radius: 10px;"
                     aria-valuenow="{{ $taxaOcupacao }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>

            @if($taxaOcupacao >= 100)
                <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Evento lotado! Não há mais vagas disponíveis.
                </div>
            @elseif($taxaOcupacao >= 80)
                <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Quase lotado! Apenas {{ $evento->max_participantes - $inscritos }} vagas restantes.
                </div>
            @endif
        </div>
    </div>
@endif


{{-- ============================================================
     CONTEÚDO PRINCIPAL
============================================================ --}}
<div class="row g-4">

    {{-- Coluna Esquerda --}}
    <div class="col-lg-8">

        {{-- Descrição --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Descrição do Evento</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($evento->descricao)
                    <p class="mb-0" style="white-space: pre-wrap; line-height: 1.7;">
                        {{ $evento->descricao }}
                    </p>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Nenhuma descrição fornecida.
                    </p>
                @endif
            </div>
        </div>

        {{-- Link de Reunião (se online/híbrido) --}}
        @if($evento->tipo == 'online' || $evento->tipo == 'hibrido')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-video"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Link de Reunião</h6>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($evento->link_reuniao)
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="link-icon">
                                    <i class="fas fa-link"></i>
                                </div>
                                <div class="min-width-0">
                                    <small class="text-muted d-block">Link de acesso</small>
                                    <span class="text-truncate d-block" style="max-width: 350px;">
                                        {{ $evento->link_reuniao }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ $evento->link_reuniao }}"
                               target="_blank"
                               class="btn btn-success">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Acessar Reunião
                            </a>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Nenhum link disponível.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Inscrições Recentes --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Inscrições Recentes</h6>
                    </div>
                    <a href="{{ route('admin.eventos.inscritos', $evento->id) }}"
                       class="btn btn-sm btn-outline-primary">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if(isset($evento->inscricoes) && $evento->inscricoes->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 candidaturas-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Egresso</th>
                                    <th>Data Inscrição</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evento->inscricoes->take(5) as $inscricao)
                                    @php
                                        $egresso = $inscricao->egresso;
                                        $temFoto = !empty($egresso->foto_url);
                                        $iniciais = mb_strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1));

                                        $statusCores = [
                                            'confirmada' => 'success',
                                            'pendente'   => 'warning',
                                            'cancelada'  => 'danger',
                                        ];
                                        $statusCor = $statusCores[$inscricao->status] ?? 'secondary';
                                    @endphp

                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                @if($temFoto)
                                                    <img src="{{ asset($egresso->foto_url) }}"
                                                         alt="{{ $egresso->nome_completo }}"
                                                         class="rounded-circle border"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.9rem;">
                                                        {{ $iniciais }}
                                                    </div>
                                                @endif

                                                <div class="lh-sm">
                                                    <strong class="d-block">{{ $egresso->nome_completo ?? 'Egresso' }}</strong>
                                                    @if($egresso->curso)
                                                        <small class="text-muted">{{ $egresso->curso->nome }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                {{ $inscricao->created_at?->format('d/m/Y H:i') ?? '-' }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $statusCor }}-subtle text-{{ $statusCor }}-emphasis border border-{{ $statusCor }}-subtle">
                                                {{ ucfirst($inscricao->status ?? 'pendente') }}
                                            </span>
                                        </td>

                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.egressos.show', $egresso->id) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Ver perfil">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else

                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Nenhuma inscrição</h6>
                        <p class="text-muted small mb-0">
                            Ainda não há inscrições para este evento.
                        </p>
                    </div>

                @endif
            </div>
        </div>

    </div>


    {{-- Coluna Direita (Sidebar) --}}
    <div class="col-lg-4">

        {{-- Informações do Evento --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Informações</h6>
                </div>
            </div>
            <div class="card-body p-3">

                <div class="info-row">
                    <div class="info-icon text-{{ $tipo['color'] }}">
                        <i class="fas fa-{{ $tipo['icon'] }}"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Tipo</small>
                        <strong>{{ $tipo['label'] }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-{{ $categoriaCor }}">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Categoria</small>
                        <strong>{{ $categoriaLabel }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Data Início</small>
                        <strong>{{ $evento->data_inicio?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </div>
                </div>

                @if($evento->data_fim)
                    <div class="info-row">
                        <div class="info-icon text-primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Data Fim</small>
                            <strong>{{ $evento->data_fim->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                @endif

                <div class="info-row">
                    <div class="info-icon text-info">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Local</small>
                        <strong>{{ $evento->local ?? ($evento->tipo == 'online' ? 'Online' : 'Não definido') }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-danger">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Unidade Orgânica</small>
                        <strong>{{ $evento->unidade->nome ?? 'Não definida' }}</strong>
                    </div>
                </div>

            </div>
        </div>

        {{-- Meta Informações --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-dark bg-opacity-10 text-dark">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Meta Informações</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="meta-row">
                    <span class="text-muted">
                        <i class="fas fa-plus-circle me-1"></i> Criado em
                    </span>
                    <strong>{{ $evento->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                @if($evento->updated_at && $evento->updated_at != $evento->created_at)
                    <div class="meta-row">
                        <span class="text-muted">
                            <i class="fas fa-edit me-1"></i> Atualizado em
                        </span>
                        <strong>{{ $evento->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif

                <div class="meta-row">
                    <span class="text-muted">
                        <i class="fas fa-hashtag me-1"></i> ID do Evento
                    </span>
                    <strong>#{{ $evento->id }}</strong>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection


