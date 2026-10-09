@extends('layouts.admin')

@section('title', 'Meu Perfil - Administrador')

@section('content')
<!-- Cabeçalho -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Meu Perfil</h1>
        <p class="text-muted">Visualize e gerencie as suas informações pessoais</p>
    </div>
  
</div>

<div class="row g-4 justify-content-center">
    <!-- Card do Perfil (Esquerda) -->
    <div class="col-lg-4 col-md-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <!-- Banner/Capa -->
            <div class="position-relative" style="height: 120px; background: linear-gradient(135deg, #1a56db, #3b82f6);">
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-n4">
                    <!-- Avatar -->
                    <div class="position-relative d-inline-block">
                        @if($admin->foto_url)
                            <img src="{{ asset($admin->foto_url) }}" 
                                 alt="{{ $admin->nome_completo }}" 
                                 class="rounded-circle border border-4 border-white shadow-lg"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle border border-4 border-white shadow-lg d-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; font-size: 3rem; font-weight: bold;">
                                {{ obterIniciais($admin->nome_completo) }}
                            </div>
                        @endif
                        <!-- Badge de Status -->
                        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                              style="width: 18px; height: 18px;"></span>
                    </div>
                </div>
            </div>

            <!-- Conteúdo do Card -->
            <div class="card-body pt-5 mt-3 text-center">
                <h4 class="fw-bold mb-1">{{ $admin->nome_completo }}</h4>
                <p class="text-muted mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                        <i class="fas fa-crown me-1"></i> {{ $admin->cargo ?? 'Administrador' }}
                    </span>
                </p>
                
                <!-- Detalhes de Contato -->
                <div class="text-start mt-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope text-primary" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Email</div>
                            <div class="fw-semibold">{{ $admin->email }}</div>
                        </div>
                    </div>
                    
                    @if($admin->telefone)
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-phone text-success" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Telefone</div>
                            <div class="fw-semibold">{{ $admin->telefone }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($admin->unidade)
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-info bg-opacity-10 rounded-circle p-2" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building text-info" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">Unidade</div>
                            <div class="fw-semibold">{{ $admin->unidade->sigla }} - {{ $admin->unidade->nome }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Botão de Ação -->
                <div class="mt-3 pt-3 border-top">
                    <a href="{{ route('admin.perfil.edit') }}" class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-edit me-2"></i> Editar Perfil
                    </a>
                </div>

                <!-- Rodapé do Card -->
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i> 
                        Membro desde {{ $admin->created_at->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações da Conta (Direita) -->
    <div class="col-lg-8 col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-info-circle text-primary me-2"></i> Informações da Conta
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <!-- Grid de Informações -->
                <div class="row g-3">
                    <!-- Nome Completo -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-user me-1"></i> Nome Completo
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->nome_completo }}</div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-envelope me-1"></i> Email
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->email }}</div>
                        </div>
                    </div>

                    <!-- Telefone -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-phone me-1"></i> Telefone
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->telefone ?? 'Não informado' }}</div>
                        </div>
                    </div>

                    <!-- Cargo -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-briefcase me-1"></i> Cargo
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->cargo ?? 'Administrador' }}</div>
                        </div>
                    </div>

                    <!-- Unidade Orgânica -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-building me-1"></i> Unidade Orgânica
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->unidade->nome ?? 'Não informado' }}</div>
                        </div>
                    </div>

                    <!-- Tipo de Conta -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-user-tag me-1"></i> Tipo de Conta
                            </div>
                            <div>
                                <span class="badge bg-primary px-3 py-2 fs-6">Administrador</span>
                            </div>
                        </div>
                    </div>

                    <!-- Estado da Conta -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-circle me-1"></i> Estado
                            </div>
                            <div>
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    <i class="fas fa-check-circle me-1"></i> {{ $admin->ativo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Data de Registo -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100">
                            <div class="small text-muted text-uppercase fw-semibold mb-1">
                                <i class="fas fa-calendar-alt me-1"></i> Data de Registo
                            </div>
                            <div class="fw-semibold fs-5">{{ $admin->created_at->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Última Atualização -->
                <div class="mt-3 pt-3 border-top text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i> 
                        Última atualização: {{ $admin->updated_at->format('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection