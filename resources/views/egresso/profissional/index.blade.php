@extends('layouts.egresso')

@section('title', 'Histórico Profissional - Egresso')

@section('content')

<div class="container-fluid py-4">

    <!-- ============================================================
         HEADER
    ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-0">
                <i class="fas fa-briefcase text-primary me-2"></i>
                Histórico Profissional
            </h1>
            <p class="text-muted small mb-0">
                Gerencie suas experiências profissionais e destaque sua trajetória
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('egresso.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>
                Voltar
            </a>
            <a href="{{ route('egresso.profissional.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-2"></i>
                Adicionar Registo
            </a>
        </div>
    </div>

    <!-- ============================================================
         ALERTAS
    ============================================================ -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fas fa-check-circle text-success fs-5"></i>
                </div>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                    <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                </div>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- ============================================================
         ESTATÍSTICAS
    ============================================================ -->
    @if(isset($profissionais) && $profissionais->count() > 0)
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-2">
                            <i class="fas fa-briefcase text-primary fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-primary mb-0">{{ $profissionais->count() }}</h3>
                        <small class="text-muted">Total de Registos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-2">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-success mb-0">{{ $profissionais->where('is_current', true)->count() }}</h3>
                        <small class="text-muted">Emprego Atual</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-2">
                            <i class="fas fa-clock text-info fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-info mb-0">{{ $profissionais->where('is_current', false)->count() }}</h3>
                        <small class="text-muted">Experiências Anteriores</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-2">
                            <i class="fas fa-calendar-alt text-warning fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-warning mb-0">{{ $profissionais->where('tipo_emprego', 'full_time')->count() }}</h3>
                        <small class="text-muted">Tempo Inteiro</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================================
         LISTA DE REGISTOS
    ============================================================ -->
    @if(isset($profissionais) && $profissionais->count() > 0)

        <div class="row g-4">
            @foreach($profissionais as $prof)
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 profissional-card {{ $prof->is_current ? 'current' : '' }}">

                        <!-- Cabeçalho do Card -->
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">
                                        <i class="fas fa-briefcase text-primary me-2"></i>
                                        {{ $prof->cargo ?? 'Cargo não definido' }}
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>
                                        {{ $prof->empregador ?? 'Empregador não informado' }}
                                    </small>
                                </div>
                                @if($prof->is_current)
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Atual
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-clock me-1"></i>
                                        Anterior
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Corpo do Card -->
                        <div class="card-body px-4 pt-2 pb-3">

                            <!-- Detalhes -->
                            <div class="profissional-detalhes">

                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="profissional-icon bg-primary bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-tag text-primary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Sector</small>
                                        <span class="fw-semibold">{{ $prof->sector ?? 'Não informado' }}</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="profissional-icon bg-info bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-clock text-info"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Tipo de Emprego</small>
                                        <span class="badge bg-light text-dark rounded-pill px-3 py-1">
                                            {{ $prof->getTipoEmpregoLabelAttribute() ?? 'Desconhecido' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="profissional-icon bg-warning bg-opacity-10 rounded-circle p-2">
                                        <i class="fas fa-calendar-alt text-warning"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Período</small>
                                        <span class="fw-semibold">
                                            {{ $prof->data_inicio ? date('d/m/Y', strtotime($prof->data_inicio)) : 'N/A' }}
                                            @if(!$prof->is_current && $prof->data_fim)
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                {{ date('d/m/Y', strtotime($prof->data_fim)) }}
                                            @elseif($prof->is_current)
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                <span class="text-success">Presente</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                @if($prof->linkedin_url)
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="profissional-icon bg-linkedin rounded-circle p-2" style="background: rgba(0, 119, 181, 0.1) !important;">
                                            <i class="fab fa-linkedin text-linkedin" style="color: #0077b5;"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">LinkedIn</small>
                                            <a href="{{ $prof->linkedin_url }}" target="_blank" class="fw-semibold text-decoration-none text-linkedin">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                Ver perfil
                                            </a>
                                        </div>
                                    </div>
                                @endif

                            </div>

                        </div>

                        <!-- Footer do Card -->
                        <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                            <div class="d-flex gap-2">
                                <a href="{{ route('egresso.profissional.edit', $prof->id) }}" 
                                   class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                                <form action="{{ route('egresso.profissional.destroy', $prof->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Tem certeza que deseja remover este registo?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
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

        <!-- ============================================================
             ESTADO VAZIO
        ============================================================ -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">

                <div class="empty-state">
                    <div class="empty-icon bg-light rounded-circle d-inline-flex p-4 mb-4">
                        <i class="fas fa-briefcase fa-3x text-muted"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-2">Nenhum registo profissional</h4>

                    <p class="text-muted mb-3">
                        Adicione sua situação profissional para que outros egressos possam conhecer sua trajetória.
                        <br>
                        <span class="small text-muted">Destaque suas experiências e conecte-se com oportunidades!</span>
                    </p>

                    <a href="{{ route('egresso.profissional.create') }}" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-plus me-2"></i>
                        Adicionar Registo
                    </a>
                </div>

            </div>
        </div>

    @endif

</div>

@endsection