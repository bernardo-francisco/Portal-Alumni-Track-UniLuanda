@extends('layouts.admin')

@section('title', 'Oportunidade - ' . $oportunidade->titulo)

@section('page_title', '💼 Detalhes da Oportunidade')
@section('page_subtitle', $oportunidade->titulo)

@section('content')

@php
    // Mapeamento de tipo → ícone e cor
    $tipos = [
        'emprego' => ['icon' => 'briefcase',      'color' => 'primary',   'label' => 'Emprego'],
        'estagio' => ['icon' => 'user-graduate',  'color' => 'info',      'label' => 'Estágio'],
        'bolsa'   => ['icon' => 'award',          'color' => 'success',   'label' => 'Bolsa'],
        'curso'   => ['icon' => 'book',           'color' => 'warning',   'label' => 'Curso'],
        'evento'  => ['icon' => 'calendar',       'color' => 'secondary', 'label' => 'Evento'],
    ];

    $tipo = $tipos[$oportunidade->tipo] ?? $tipos['emprego'];

    // Verificar se o prazo expirou
    $expirada = $oportunidade->data_limite && $oportunidade->data_limite < now();

    // Dias restantes
    $diasRestantes = null;
    if ($oportunidade->data_limite && !$expirada) {
        $diasRestantes = now()->diffInDays($oportunidade->data_limite, false);
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

                    @if($oportunidade->is_active && !$expirada)
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Ativa
                        </span>
                    @elseif($expirada)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            <i class="fas fa-clock me-1"></i> Prazo Expirado
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                            <i class="fas fa-ban me-1"></i> Inativa
                        </span>
                    @endif

                    @if($diasRestantes !== null)
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                            <i class="fas fa-hourglass-half me-1"></i>
                            {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'dia' : 'dias' }} restantes
                        </span>
                    @endif
                </div>

                <h2 class="fw-bold mb-1">{{ $oportunidade->titulo }}</h2>

                @if($oportunidade->empresa)
                    <p class="text-muted mb-0">
                        <i class="fas fa-building me-1"></i>
                        {{ $oportunidade->empresa }}
                        @if($oportunidade->unidade)
                            <span class="text-muted">· {{ $oportunidade->unidade->sigla }}</span>
                        @endif
                    </p>
                @endif
            </div>

            {{-- Ações --}}
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.oportunidades.edit', $oportunidade->id) }}"
                       class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>

                    <form action="{{ route('admin.oportunidades.destroy', $oportunidade->id) }}"
                          method="POST"
                          onsubmit="return confirm('Tem certeza que deseja eliminar esta oportunidade?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>

                    <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">
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
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $oportunidade->candidaturas_count ?? 0 }}</div>
                <div class="stat-label">Candidaturas</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $oportunidade->salario ?? 'A combinar' }}
                </div>
                <div class="stat-label">Salário</div>
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
                    {{ $oportunidade->localizacao ?? 'Não definida' }}
                </div>
                <div class="stat-label">Localização</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-{{ $expirada ? 'danger' : 'warning' }} bg-opacity-10 text-{{ $expirada ? 'danger' : 'warning' }}">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $oportunidade->data_limite ? $oportunidade->data_limite->format('d/m/Y') : 'Sem prazo' }}
                </div>
                <div class="stat-label">Data Limite</div>
            </div>
        </div>
    </div>

</div>


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
                    <h6 class="mb-0 fw-bold">Descrição</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($oportunidade->descricao)
                    <p class="mb-0" style="white-space: pre-wrap; line-height: 1.7;">
                        {{ $oportunidade->descricao }}
                    </p>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Nenhuma descrição fornecida.
                    </p>
                @endif
            </div>
        </div>

        {{-- Requisitos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Requisitos</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($oportunidade->requisitos)
                    <p class="mb-0" style="white-space: pre-wrap; line-height: 1.7;">
                        {{ $oportunidade->requisitos }}
                    </p>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Nenhum requisito especificado.
                    </p>
                @endif
            </div>
        </div>

        {{-- Candidaturas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Candidaturas</h6>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3">
                        {{ $oportunidade->candidaturas_count ?? 0 }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                @if(isset($oportunidade->candidaturas) && $oportunidade->candidaturas->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 candidaturas-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Egresso</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oportunidade->candidaturas as $candidatura)
                                    @php
                                        $egresso = $candidatura->egresso;
                                        $temFoto = !empty($egresso->foto_url);
                                        $iniciais = mb_strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1));

                                        $statusCores = [
                                            'pendente' => 'warning',
                                            'aprovado' => 'success',
                                            'aprovada' => 'success',
                                            'rejeitado'=> 'danger',
                                            'rejeitada'=> 'danger',
                                        ];
                                        $statusCor = $statusCores[$candidatura->status] ?? 'secondary';
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
                                                {{ $candidatura->created_at ? $candidatura->created_at->format('d/m/Y H:i') : '-' }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $statusCor }}-subtle text-{{ $statusCor }}-emphasis border border-{{ $statusCor }}-subtle">
                                                {{ ucfirst($candidatura->status ?? 'pendente') }}
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
                            <i class="fas fa-users-slash"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Nenhuma candidatura</h6>
                        <p class="text-muted small mb-0">
                            Ainda não há candidaturas para esta oportunidade.
                        </p>
                    </div>

                @endif
            </div>
        </div>

    </div>


    {{-- Coluna Direita (Sidebar) --}}
    <div class="col-lg-4">

        {{-- Informações --}}
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
                    <div class="info-icon text-primary">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Empresa</small>
                        <strong>{{ $oportunidade->empresa ?? 'Não definida' }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-info">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Localização</small>
                        <strong>{{ $oportunidade->localizacao ?? 'Não definida' }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Salário</small>
                        <strong>{{ $oportunidade->salario ?? 'A combinar' }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Data Limite</small>
                        <strong>
                            {{ $oportunidade->data_limite ? $oportunidade->data_limite->format('d/m/Y') : 'Sem prazo' }}
                        </strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-danger">
                        <i class="fas fa-university"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Unidade Orgânica</small>
                        <strong>{{ $oportunidade->unidade->nome ?? 'Não definida' }}</strong>
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
                        <i class="fas fa-plus-circle me-1"></i> Criada em
                    </span>
                    <strong>{{ $oportunidade->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                @if($oportunidade->updated_at && $oportunidade->updated_at != $oportunidade->created_at)
                    <div class="meta-row">
                        <span class="text-muted">
                            <i class="fas fa-edit me-1"></i> Atualizada em
                        </span>
                        <strong>{{ $oportunidade->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif

                <div class="meta-row">
                    <span class="text-muted">
                        <i class="fas fa-hashtag me-1"></i> ID
                    </span>
                    <strong>#{{ $oportunidade->id }}</strong>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

