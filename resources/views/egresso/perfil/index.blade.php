@extends('layouts.egresso')

@section('title', 'Meu Perfil')

@section('page_title', '👤 Meu Perfil')
@section('page_subtitle', 'Visualize e gerencie as suas informações')

@section('content')

@if(isset($egresso))

    @php
        // Foto com fallback
        $temFoto = !empty($egresso->foto_url);
        $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
            ->filter()
            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode('');

        // Status
        $statusMap = [
            'active'       => ['color' => 'success',   'icon' => 'check-circle',   'label' => 'Activo'],
            'inactive'     => ['color' => 'danger',    'icon' => 'times-circle',   'label' => 'Inactivo'],
            'lost_contact' => ['color' => 'secondary', 'icon' => 'question-circle','label' => 'Sem Contacto'],
        ];
        $status = $statusMap[$egresso->status ?? 'active'] ?? $statusMap['active'];

        // Validação
        $validacaoMap = [
            'aprovado'  => ['color' => 'success',   'icon' => 'check-circle', 'label' => 'Aprovado'],
            'pendente'  => ['color' => 'warning',   'icon' => 'clock',        'label' => 'Pendente'],
            'reprovado' => ['color' => 'danger',    'icon' => 'times-circle', 'label' => 'Reprovado'],
        ];
        $validacao = $validacaoMap[$egresso->status_validacao] ?? null;

        // Completude do perfil
        $campos = [
            'foto_url'        => !empty($egresso->foto_url),
            'telefone'        => !empty($egresso->telefone),
            'data_nascimento' => !empty($egresso->data_nascimento),
            'curso_id'        => !empty($egresso->curso_id),
            'ano_formatura'   => !empty($egresso->ano_formatura),
            'localizacao'     => isset($localizacaoAtual) && $localizacaoAtual,
            'profissional'    => isset($profissionalAtual) && $profissionalAtual,
        ];
        $preenchidos = collect($campos)->filter()->count();
        $totalCampos = count($campos);
        $completude  = round(($preenchidos / $totalCampos) * 100);
    @endphp


    {{-- ============================================================
         HERO CARD
    ============================================================ --}}
    <div class="card border-0 shadow-sm hero-card mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">

                {{-- Avatar --}}
                <div class="col-md-3 col-12 text-center text-md-start">
                    <div class="hero-avatar-wrapper mx-auto mx-md-0">
                        @if($temFoto)
                            <img src="{{ asset($egresso->foto_url) }}"
                                 alt="{{ $egresso->nome_completo }}"
                                 class="hero-avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hero-avatar hero-avatar-fallback" style="display: none;">
                                {{ $iniciais }}
                            </div>
                        @else
                            <div class="hero-avatar hero-avatar-fallback">
                                {{ $iniciais }}
                            </div>
                        @endif

                        @if($egresso->verificado)
                            <span class="hero-verified" title="Conta verificada">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="col-md-9 col-12">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h2 class="h3 fw-bold mb-0 hero-name">{{ $egresso->nome_completo }}</h2>

                        <span class="badge hero-badge bg-{{ $status['color'] }}-subtle text-{{ $status['color'] }}-emphasis border border-{{ $status['color'] }}-subtle">
                            <i class="fas fa-{{ $status['icon'] }} me-1"></i>
                            {{ $status['label'] }}
                        </span>

                        @if($validacao)
                            <span class="badge hero-badge bg-{{ $validacao['color'] }}-subtle text-{{ $validacao['color'] }}-emphasis border border-{{ $validacao['color'] }}-subtle">
                                <i class="fas fa-{{ $validacao['icon'] }} me-1"></i>
                                {{ $validacao['label'] }}
                            </span>
                        @endif

                        @if($egresso->ano_formatura)
                            <span class="badge hero-badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <i class="fas fa-graduation-cap me-1"></i>
                                {{ $egresso->ano_formatura }}
                            </span>
                        @endif
                    </div>

                    @if($egresso->curso)
                        <p class="hero-course mb-3">
                            <i class="fas fa-university me-1"></i>
                            {{ $egresso->curso->nome }}
                            @if($egresso->curso->unidade)
                                <span class="text-muted ms-1">· {{ $egresso->curso->unidade->sigla }}</span>
                            @endif
                        </p>
                    @endif

                    {{-- Info Grid --}}
                    <div class="row g-2 hero-info-grid">

                        <div class="col-lg-6 col-md-12">
                            <div class="hero-info-item">
                                <div class="hero-info-icon hero-info-icon-primary">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="hero-info-content">
                                    <span class="hero-info-label">Email</span>
                                    <span class="hero-info-value">{{ $egresso->email ?? 'Não informado' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="hero-info-item">
                                <div class="hero-info-icon hero-info-icon-success">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="hero-info-content">
                                    <span class="hero-info-label">Telefone</span>
                                    <span class="hero-info-value">{{ $egresso->telefone ?? 'Não informado' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="hero-info-item">
                                <div class="hero-info-icon hero-info-icon-warning">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="hero-info-content">
                                    <span class="hero-info-label">Nº de Processo</span>
                                    <span class="hero-info-value hero-info-mono">{{ $egresso->numero_processo ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="hero-info-item">
                                <div class="hero-info-icon hero-info-icon-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="hero-info-content">
                                    <span class="hero-info-label">Registado em</span>
                                    <span class="hero-info-value">{{ $egresso->created_at?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- Ações --}}
            <div class="hero-actions mt-4 pt-4">
                <a href="{{ route('egresso.perfil.editar') }}" class="btn btn-primary hero-btn-primary">
                    <i class="fas fa-user-edit me-2"></i> Editar Perfil
                </a>
            </div>
        </div>
    </div>


    {{-- ============================================================
         COMPLETUDE DO PERFIL
    ============================================================ --}}
    @if($completude < 100)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Complete o seu perfil</h6>
                            <small class="text-muted">
                                {{ $preenchidos }} de {{ $totalCampos }} campos preenchidos
                            </small>
                        </div>
                    </div>
                    <span class="fw-bold fs-4 text-warning">{{ $completude }}%</span>
                </div>

                <div class="progress" style="height: 10px; border-radius: 10px;">
                    <div class="progress-bar bg-gradient bg-{{ $completude >= 70 ? 'success' : ($completude >= 40 ? 'warning' : 'danger') }}"
                         role="progressbar"
                         style="width: {{ $completude }}%; border-radius: 10px;"
                         aria-valuenow="{{ $completude }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- ============================================================
         DETALHES EM 2 COLUNAS
    ============================================================ --}}
    <div class="row g-4">

        {{-- DADOS PESSOAIS --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Dados Pessoais</h6>
                            <small class="text-muted">Informações básicas</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">

                    <div class="info-row">
                        <div class="info-icon text-primary">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Nº de Processo</small>
                            <strong>{{ $egresso->numero_processo ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon text-info">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Género</small>
                            <strong>{{ $egresso->genero_label ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon text-success">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Data de Nascimento</small>
                            <strong>{{ $egresso->data_nascimento?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon text-warning">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Nota Final</small>
                            <strong>{{ $egresso->nota_final ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon text-secondary">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Estado da Conta</small>
                            <span class="badge bg-{{ $status['color'] }}-subtle text-{{ $status['color'] }}-emphasis border border-{{ $status['color'] }}-subtle">
                                <i class="fas fa-{{ $status['icon'] }} me-1"></i>
                                {{ $status['label'] }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- LOCALIZAÇÃO --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Localização Atual</h6>
                                <small class="text-muted">Onde estás agora</small>
                            </div>
                        </div>

                        @if(isset($localizacaoAtual) && $localizacaoAtual)
                            <a href="{{ route('egresso.localizacao') }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Gerir localizações">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">

                    @if(isset($localizacaoAtual) && $localizacaoAtual)

                        <div class="info-row">
                            <div class="info-icon text-primary">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">País</small>
                                <strong>{{ $localizacaoAtual->pais ?? '—' }}</strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-icon text-info">
                                <i class="fas fa-map"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Província</small>
                                <strong>{{ $localizacaoAtual->provincia ?? '—' }}</strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-icon text-success">
                                <i class="fas fa-city"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Cidade</small>
                                <strong>{{ $localizacaoAtual->cidade ?? '—' }}</strong>
                            </div>
                        </div>

                        @if($localizacaoAtual->endereco)
                            <div class="info-row">
                                <div class="info-icon text-warning">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Endereço</small>
                                    <strong>{{ $localizacaoAtual->endereco }}</strong>
                                </div>
                            </div>
                        @endif

                        @if($localizacaoAtual->latitude && $localizacaoAtual->longitude)
                            <div class="info-row">
                                <div class="info-icon text-secondary">
                                    <i class="fas fa-crosshairs"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Coordenadas</small>
                                    <strong class="font-monospace small">
                                        {{ number_format($localizacaoAtual->latitude, 4) }},
                                        {{ number_format($localizacaoAtual->longitude, 4) }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Sem localização registada</h6>
                            <p class="text-muted small mb-3">
                                Adicione a sua localização para conectar-se com outros egressos.
                            </p>
                            <a href="{{ route('egresso.localizacao.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Adicionar Localização
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>


        {{-- SITUAÇÃO PROFISSIONAL --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Situação Profissional</h6>
                                <small class="text-muted">A sua carreira atual</small>
                            </div>
                        </div>

                        @if(isset($profissionalAtual) && $profissionalAtual)
                            <a href="{{ route('egresso.profissional') }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Gerir informação profissional">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">

                    @if(isset($profissionalAtual) && $profissionalAtual)

                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <small class="text-muted d-block">Cargo</small>
                                    <strong class="small">{{ $profissionalAtual->cargo ?? '—' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <small class="text-muted d-block">Empregador</small>
                                    <strong class="small">{{ $profissionalAtual->empregador ?? '—' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <small class="text-muted d-block">Tipo de Emprego</small>
                                    <strong class="small">{{ $profissionalAtual->tipo_emprego_label ?? '—' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <small class="text-muted d-block">Setor</small>
                                    <strong class="small">{{ $profissionalAtual->sector ?? '—' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-secondary bg-opacity-10 text-secondary">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <small class="text-muted d-block">Data de Início</small>
                                    <strong class="small">{{ $profissionalAtual->data_inicio?->format('d/m/Y') ?? '—' }}</strong>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="pro-card">
                                    <div class="pro-card-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fab fa-linkedin"></i>
                                    </div>
                                    <small class="text-muted d-block">LinkedIn</small>
                                    @if($profissionalAtual->linkedin_url)
                                        <a href="{{ $profissionalAtual->linkedin_url }}"
                                           target="_blank"
                                           class="text-primary text-decoration-none small fw-semibold">
                                            Ver perfil <i class="fas fa-external-link-alt ms-1" style="font-size: 0.65rem;"></i>
                                        </a>
                                    @else
                                        <strong class="small text-muted">—</strong>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @else

                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Sem informação profissional</h6>
                            <p class="text-muted small mb-3">
                                Adicione a sua situação profissional para se destacar na comunidade.
                            </p>
                            <a href="{{ route('egresso.profissional.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Adicionar Informação
                            </a>
                        </div>

                    @endif

                </div>
            </div>
        </div>

    </div>

@else

    {{-- PERFIL NÃO ENCONTRADO --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-user-slash"></i>
            </div>
            <h5 class="fw-bold mb-2">Perfil não encontrado</h5>
            <p class="text-muted mb-4">
                Complete o seu cadastro para acessar todas as funcionalidades.
            </p>
            <a href="{{ route('egresso.perfil.editar') }}" class="btn btn-primary">
                <i class="fas fa-user-edit me-1"></i> Completar Perfil
            </a>
        </div>
    </div>

@endif

@endsection

