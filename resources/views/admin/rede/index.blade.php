@extends('layouts.admin')

@section('title', 'Rede de Contactos')

@section('page_title', '🤝 Rede de Contactos')
@section('page_subtitle', 'Gerencie as conexões com os egressos')

@section('content')

@php
    $totalEgressos = $egressos->count() ?? 0;
    $totalConexoes = $conexoes->count() ?? 0;
    $meuId = optional(auth()->user()->egresso)->id;
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalEgressos }}</div>
                <div class="stat-label">Egressos Disponíveis</div>
                <small class="stat-desc">Prontos para conectar</small>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalConexoes }}</div>
                <div class="stat-label">Minhas Conexões</div>
                <small class="stat-desc">Contactos ativos</small>
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

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- ============================================================
     PESQUISA
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text"
                   id="pesquisaEgressos"
                   class="form-control border-start-0"
                   placeholder="Pesquisar egresso por nome, curso ou email..."
                   autocomplete="off">
            <button class="btn btn-primary" type="button" onclick="executarPesquisa()">
                <i class="fas fa-search me-1"></i> Pesquisar
            </button>
            <button class="btn btn-outline-secondary" type="button" onclick="limparPesquisa()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>


<div class="row g-4">

    {{-- ============================================================
         TODOS OS EGRESSOS
    ============================================================ --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm network-card h-100">

            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Egressos Disponíveis</h6>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3">
                        {{ $totalEgressos }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0 network-list" id="listaEgressos">
                @if(isset($egressos) && $egressos->count() > 0)

                    @foreach($egressos as $eg)
                        @php
                            $iniciais = collect(explode(' ', trim($eg->nome_completo ?? 'E')))
                                ->filter()
                                ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp

                        <div class="network-item d-flex align-items-center justify-content-between"
                             data-search="{{ strtolower($eg->nome_completo . ' ' . ($eg->curso->nome ?? '') . ' ' . ($eg->email ?? '')) }}">

                            <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                @if($eg->foto_url)
                                    <img src="{{ asset($eg->foto_url) }}"
                                         alt="{{ $eg->nome_completo }}"
                                         class="rounded-circle border flex-shrink-0"
                                         style="width: 44px; height: 44px; object-fit: cover;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.9rem; display: none;">
                                        {{ $iniciais }}
                                    </div>
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.9rem;">
                                        {{ $iniciais }}
                                    </div>
                                @endif

                                <div class="min-width-0">
                                    <strong class="d-block text-truncate">{{ $eg->nome_completo }}</strong>
                                    <small class="text-muted d-block text-truncate">
                                        <i class="fas fa-graduation-cap me-1" style="font-size: 0.7rem;"></i>
                                        {{ $eg->curso->nome ?? 'Curso não definido' }}
                                    </small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                                <a href="{{ route('admin.egressos.show', $eg->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Ver perfil">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('admin.rede.conectar', $eg->id) }}"
                                   class="btn btn-sm btn-primary"
                                   title="Conectar"
                                   onclick="return confirm('Deseja enviar solicitação de conexão para {{ $eg->nome_completo }}?')">
                                    <i class="fas fa-user-plus me-1"></i>
                                    <span class="d-none d-md-inline">Conectar</span>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    {{-- Estado vazio (para pesquisa) --}}
                    <div class="network-empty d-none" id="semResultados">
                        <i class="fas fa-search"></i>
                        <p class="mb-0">Nenhum resultado encontrado.</p>
                    </div>

                @else

                    <div class="network-empty">
                        <i class="fas fa-users"></i>
                        <h6 class="fw-bold mb-1">Sem egressos disponíveis</h6>
                        <p class="text-muted small mb-0">
                            Não há egressos para conectar neste momento.
                        </p>
                    </div>

                @endif
            </div>
        </div>
    </div>


    {{-- ============================================================
         MINHAS CONEXÕES
    ============================================================ --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm network-card h-100">

            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Minhas Conexões</h6>
                    </div>
                    <span class="badge bg-success rounded-pill px-3">
                        {{ $totalConexoes }}
                    </span>
                </div>
            </div>

            <div class="card-body p-0 network-list">
                @if(isset($conexoes) && $conexoes->count() > 0)

                    @foreach($conexoes as $con)
                        @php
                            $contato = ($con->solicitante_id == $meuId)
                                ? $con->destinatario
                                : $con->solicitante;

                            if (!$contato) continue;

                            $iniciais = collect(explode(' ', trim($contato->nome_completo ?? 'C')))
                                ->filter()
                                ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp

                        <div class="network-item d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                <div class="position-relative flex-shrink-0">
                                    @if($contato->foto_url)
                                        <img src="{{ asset($contato->foto_url) }}"
                                             alt="{{ $contato->nome_completo }}"
                                             class="rounded-circle border"
                                             style="width: 44px; height: 44px; object-fit: cover;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="rounded-circle align-items-center justify-content-center text-white fw-bold"
                                             style="width: 44px; height: 44px; background: linear-gradient(135deg, #16a34a, #4ade80); font-size: 0.9rem; display: none;">
                                            {{ $iniciais }}
                                        </div>
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width: 44px; height: 44px; background: linear-gradient(135deg, #16a34a, #4ade80); font-size: 0.9rem;">
                                            {{ $iniciais }}
                                        </div>
                                    @endif

                                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                                          style="width: 12px; height: 12px;"></span>
                                </div>

                                <div class="min-width-0">
                                    <strong class="d-block text-truncate">{{ $contato->nome_completo }}</strong>
                                    <small class="text-muted d-block text-truncate">
                                        <i class="fas fa-graduation-cap me-1" style="font-size: 0.7rem;"></i>
                                        {{ $contato->curso->nome ?? 'Curso não definido' }}
                                    </small>
                                </div>
                            </div>

                            <div class="flex-shrink-0 ms-2">
                                <a href="{{ route('admin.mensagens.conversa', $contato->id) }}"
                                   class="btn btn-sm btn-success"
                                   title="Enviar mensagem">
                                    <i class="fas fa-envelope me-1"></i>
                                    <span class="d-none d-md-inline">Mensagem</span>
                                </a>
                            </div>
                        </div>
                    @endforeach

                @else

                    <div class="network-empty">
                        <i class="fas fa-handshake"></i>
                        <h6 class="fw-bold mb-1">Sem conexões ativas</h6>
                        <p class="text-muted small mb-0">
                            Conecte-se com egressos para iniciar conversas.
                        </p>
                    </div>

                @endif
            </div>
        </div>
    </div>

</div>

@endsection

