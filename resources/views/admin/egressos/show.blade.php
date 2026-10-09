@extends('layouts.admin')

@section('title', 'Perfil do Egresso - ' . $egresso->nome_completo)

@section('page_title', '👤 Perfil do Egresso')
@section('page_subtitle', $egresso->nome_completo)

@section('content')

@php
    // Status
    $statusColors = [
        'active'       => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Activo'],
        'inactive'     => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Inactivo'],
        'lost_contact' => ['color' => 'secondary', 'icon' => 'question-circle', 'label' => 'Sem Contacto'],
        'blocked'      => ['color' => 'dark',    'icon' => 'ban',          'label' => 'Bloqueado'],
    ];
    $status = $statusColors[$egresso->status] ?? $statusColors['inactive'];

    // Iniciais
    $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
        ->filter()
        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');

    // Verificado
    $verificado = $egresso->verificado ?? false;

    // Localização e Profissional
    $localizacao = $egresso->localizacaoAtual ?? null;
    $profissional = $egresso->profissionalAtual ?? null;
@endphp


{{-- ============================================================
     HERO CARD
============================================================ --}}
<div class="card border-0 shadow-sm hero-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

            {{-- Avatar + Info --}}
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                @if($egresso->foto_url)
                    <img src="{{ asset($egresso->foto_url) }}"
                         alt="{{ $egresso->nome_completo }}"
                         class="rounded-circle border border-3 border-white shadow-sm"
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width: 80px; height: 80px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1.8rem; display: none;">
                        {{ $iniciais }}
                    </div>
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width: 80px; height: 80px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1.8rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0">{{ $egresso->nome_completo }}</h4>

                        @if($verificado)
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                <i class="fas fa-check-circle me-1"></i> Verificado
                            </span>
                        @endif

                        <span class="badge bg-{{ $status['color'] }}-subtle text-{{ $status['color'] }}-emphasis border border-{{ $status['color'] }}-subtle">
                            <i class="fas fa-{{ $status['icon'] }} me-1"></i> {{ $status['label'] }}
                        </span>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <span class="text-muted small">
                            <i class="fas fa-hashtag me-1"></i>
                            <code class="text-dark">{{ $egresso->numero_processo }}</code>
                        </span>

                        @if($egresso->curso)
                            <span class="text-muted small">
                                <i class="fas fa-graduation-cap me-1"></i>
                                {{ $egresso->curso->nome }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.egressos.edit', $egresso->id) }}"
                   class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>

                <div class="dropdown">
                    <button type="button"
                            class="btn btn-outline-secondary dropdown-toggle"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        @if($verificado)
                            <li>
                                <form action="{{ route('admin.egressos.desverificar', $egresso->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-times-circle me-2 text-warning"></i> Desverificar
                                    </button>
                                </form>
                            </li>
                        @else
                            <li>
                                <form action="{{ route('admin.egressos.verificar', $egresso->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-check-circle me-2 text-success"></i> Verificar
                                    </button>
                                </form>
                            </li>
                        @endif
                        <li>
                            <form action="{{ route('admin.egressos.destroy', $egresso->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Tem certeza que deseja eliminar este egresso?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-trash me-2"></i> Eliminar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

                <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
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
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $egresso->curso->nome ?? 'Não definido' }}
                </div>
                <div class="stat-label">Curso</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $egresso->ano_formatura ?? 'Não definido' }}
                </div>
                <div class="stat-label">Ano de Formatura</div>
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
                    {{ $localizacao->cidade ?? 'Não definida' }}
                </div>
                <div class="stat-label">Cidade Atual</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size: 1rem;">
                    {{ $profissional->cargo ?? 'Não definido' }}
                </div>
                <div class="stat-label">Cargo Atual</div>
            </div>
        </div>
    </div>

</div>


<div class="row g-4">

    {{-- ============================================================
         COLUNA ESQUERDA
    ============================================================ --}}
    <div class="col-lg-8">

        {{-- Dados Pessoais --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Dados Pessoais</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-primary">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Email</small>
                                <strong class="small">{{ $egresso->email ?? 'Não definido' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-success">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Telefone</small>
                                <strong>{{ $egresso->telefone ?? 'Não definido' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-info">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Data de Nascimento</small>
                                <strong>
                                    {{ $egresso->data_nascimento ? $egresso->data_nascimento->format('d/m/Y') : 'Não definida' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-warning">
                                <i class="fas fa-venus-mars"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Género</small>
                                <strong>
                                    @php
                                        $generos = ['M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro'];
                                    @endphp
                                    {{ $generos[$egresso->genero] ?? 'Não definido' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Dados Académicos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Dados Académicos</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-primary">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Curso</small>
                                <strong class="small">{{ $egresso->curso->nome ?? 'Não definido' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-info">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Unidade Orgânica</small>
                                <strong class="small">
                                    {{ $egresso->curso->unidade->nome ?? 'Não definida' }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-success">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Ano de Formatura</small>
                                <strong>{{ $egresso->ano_formatura ?? 'Não definido' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-icon text-warning">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Nota Final</small>
                                <strong>{{ $egresso->nota_final ?? 'Não definida' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Localização --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Localização Atual</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($localizacao)
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-primary">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">País</small>
                                    <strong>{{ $localizacao->pais ?? 'Não definido' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-info">
                                    <i class="fas fa-map"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Província</small>
                                    <strong>{{ $localizacao->provincia ?? 'Não definida' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-success">
                                    <i class="fas fa-city"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Cidade</small>
                                    <strong>{{ $localizacao->cidade ?? 'Não definida' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <p class="text-muted mb-0">Nenhuma localização registada.</p>
                    </div>
                @endif
            </div>
        </div>


        {{-- Situação Profissional --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Situação Profissional</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($profissional)
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-primary">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Cargo</small>
                                    <strong class="small">{{ $profissional->cargo ?? 'Não definido' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-success">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Empregador</small>
                                    <strong class="small">{{ $profissional->empregador ?? 'Não definido' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <div class="info-icon text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Tipo de Emprego</small>
                                    <strong>{{ $profissional->getTipoEmpregoLabelAttribute() ?? 'Não definido' }}</strong>
                                </div>
                            </div>
                        </div>

                        @if($profissional->sector)
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-icon text-info">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">Sector</small>
                                        <strong>{{ $profissional->sector }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($profissional->linkedin_url)
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-icon text-primary">
                                        <i class="fab fa-linkedin"></i>
                                    </div>
                                    <div class="info-content">
                                        <small class="text-muted d-block">LinkedIn</small>
                                        <a href="{{ $profissional->linkedin_url }}"
                                           target="_blank"
                                           class="text-primary small">
                                            Ver perfil <i class="fas fa-external-link-alt ms-1" style="font-size: 0.7rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <p class="text-muted mb-0">Nenhuma informação profissional registada.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>


    {{-- ============================================================
         SIDEBAR
    ============================================================ --}}
    <div class="col-lg-4">

        {{-- Estado da Conta --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Estado da Conta</h6>
                </div>
            </div>
            <div class="card-body p-4">

                <div class="status-row">
                    <span class="text-muted">
                        <i class="fas fa-circle text-{{ $status['color'] }} me-1" style="font-size: 0.5rem;"></i>
                        Estado
                    </span>
                    <strong class="text-{{ $status['color'] }}">{{ $status['label'] }}</strong>
                </div>

                <div class="status-row">
                    <span class="text-muted">
                        <i class="fas fa-user-check me-1"></i>
                        Verificação
                    </span>
                    <strong class="{{ $verificado ? 'text-success' : 'text-secondary' }}">
                        {{ $verificado ? 'Verificado' : 'Não verificado' }}
                    </strong>
                </div>

                @if($egresso->data_verificacao)
                    <div class="status-row">
                        <span class="text-muted">
                            <i class="fas fa-calendar-check me-1"></i>
                            Verificado em
                        </span>
                        <strong>{{ $egresso->data_verificacao->format('d/m/Y') }}</strong>
                    </div>
                @endif

                <div class="status-row">
                    <span class="text-muted">
                        <i class="fas fa-calendar-plus me-1"></i>
                        Registo
                    </span>
                    <strong>{{ $egresso->created_at->format('d/m/Y') }}</strong>
                </div>

            </div>
        </div>


        {{-- Validação --}}
        @if($egresso->status_validacao ?? false)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-{{ $egresso->status_validacao === 'aprovado' ? 'success' : ($egresso->status_validacao === 'pendente' ? 'warning' : 'danger') }} bg-opacity-10 text-{{ $egresso->status_validacao === 'aprovado' ? 'success' : ($egresso->status_validacao === 'pendente' ? 'warning' : 'danger') }}">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Validação</h6>
                    </div>
                </div>
                <div class="card-body p-4">
                    @php
                        $validacao = [
                            'aprovado'  => ['color' => 'success', 'label' => 'Aprovado'],
                            'pendente'  => ['color' => 'warning', 'label' => 'Pendente'],
                            'reprovado' => ['color' => 'danger',  'label' => 'Reprovado'],
                        ];
                        $v = $validacao[$egresso->status_validacao] ?? ['color' => 'secondary', 'label' => 'Desconhecido'];
                    @endphp

                    <div class="status-row">
                        <span class="text-muted">Estado</span>
                        <strong class="text-{{ $v['color'] }}">{{ $v['label'] }}</strong>
                    </div>

                    @if($egresso->data_validacao)
                        <div class="status-row">
                            <span class="text-muted">Validado em</span>
                            <strong>{{ $egresso->data_validacao->format('d/m/Y') }}</strong>
                        </div>
                    @endif

                    @if($egresso->observacoes_validacao)
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted d-block mb-1">Observações:</small>
                            <small>{{ $egresso->observacoes_validacao }}</small>
                        </div>
                    @endif
                </div>
            </div>
        @endif


        {{-- Ações Rápidas --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Ações Rápidas</h6>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.egressos.edit', $egresso->id) }}"
                       class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Editar Dados
                    </a>

                    @if($verificado)
                        <form action="{{ route('admin.egressos.desverificar', $egresso->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="fas fa-times-circle me-1"></i> Desverificar Conta
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.egressos.verificar', $egresso->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check-circle me-1"></i> Verificar Conta
                            </button>
                        </form>
                    @endif

                    @if($egresso->status !== 'active')
                        <form action="{{ route('admin.egressos.reativar', $egresso->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="fas fa-redo me-1"></i> Reativar Conta
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.mensagens.conversa', $egresso->id) }}"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-1"></i> Enviar Mensagem
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection


