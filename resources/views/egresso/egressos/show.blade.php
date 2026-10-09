@extends('layouts.egresso')

@section('title', $egresso->nome_completo)

@push('styles')


@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- Capa -->
            <div class="profile-cover">
                <div class="profile-avatar">
                    @if($egresso->foto_url)
                        <img src="{{ asset($egresso->foto_url) }}" alt="{{ $egresso->nome_completo }}">
                    @else
                        <div class="avatar-placeholder">
                            {{ strtoupper(substr($egresso->nome_completo, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informações -->
            <div class="profile-info">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h1 class="h3 mb-1">{{ $egresso->nome_completo }}</h1>
                        <p class="text-muted mb-1">
                            <i class="fas fa-graduation-cap"></i> 
                            {{ $egresso->curso->nome ?? 'Curso não informado' }}
                            @if($egresso->curso && $egresso->curso->unidade)
                                ({{ $egresso->curso->unidade->sigla ?? '' }})
                            @endif
                        </p>
                        @if($egresso->ano_formatura)
                            <p class="text-muted mb-1">
                                <i class="fas fa-calendar-alt"></i> 
                                Formado em {{ $egresso->ano_formatura }}
                            </p>
                        @endif
                        <p class="text-muted mb-0">
                            <i class="fas fa-id-card"></i> 
                            Nº Processo: <code>{{ $egresso->numero_processo }}</code>
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Status de conexão -->
                        @if($isConexao)
                            <span class="badge badge-conexao p-2">
                                <i class="fas fa-handshake"></i> Na sua rede
                            </span>
                        @elseif($pedidoPendente)
                            <span class="badge bg-warning p-2">
                                <i class="fas fa-clock"></i> Pedido pendente
                            </span>
                        @else
                            <a href="{{ route('egresso.rede.conectar', $egresso->id) }}" 
                               class="btn btn-primary btn-sm"
                               onclick="return confirm('Deseja enviar pedido de conexão para {{ $egresso->nome_completo }}?')">
                                <i class="fas fa-user-plus"></i> Conectar
                            </a>
                        @endif

                        @if($isConexao)
                            <a href="{{ route('egresso.mensagens.conversa', $egresso->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope"></i> Mensagem
                            </a>
                        @endif

                        <a href="{{ route('egresso.mapa') }}?buscar={{ $egresso->numero_processo }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-map-marker-alt"></i> Localizar
                        </a>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="row g-3 mt-3">
                    <div class="col-md-3 col-6">
                        <div class="stat-publico">
                            <div class="numero">{{ $stats['total_publicacoes'] ?? 0 }}</div>
                            <div class="rotulo">Publicações</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-publico">
                            <div class="numero">{{ $stats['total_comentarios'] ?? 0 }}</div>
                            <div class="rotulo">Comentários</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-publico">
                            <div class="numero">{{ $stats['total_conexoes'] ?? 0 }}</div>
                            <div class="rotulo">Conexões</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-publico">
                            <div class="numero">{{ $stats['tem_localizacao'] ? '📍' : '❌' }}</div>
                            <div class="rotulo">Localização</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Localização e Profissional -->
<div class="row g-4 mt-2">
    @if($localizacao)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt text-primary"></i> Localização Atual</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>País:</strong> {{ $localizacao->pais }}</p>
                @if($localizacao->cidade)
                    <p class="mb-1"><strong>Cidade:</strong> {{ $localizacao->cidade }}</p>
                @endif
                @if($localizacao->endereco)
                    <p class="mb-1"><strong>Endereço:</strong> {{ $localizacao->endereco }}</p>
                @endif
                @if($localizacao->latitude && $localizacao->longitude)
                    <p class="mb-0 text-muted small">
                        📌 {{ number_format($localizacao->latitude, 4) }}, {{ number_format($localizacao->longitude, 4) }}
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($profissional && $profissional->count() > 0)
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase text-success"></i> 
                    @if($isConexao)
                        Histórico Profissional
                    @else
                        Situação Atual
                    @endif
                </h5>
                @if(!$isConexao)
                    <small class="text-muted">(Apenas informação pública)</small>
                @endif
            </div>
            <div class="card-body">
                @foreach($profissional as $prof)
                    <div class="mb-3 @if(!$loop->last) border-bottom pb-3 @endif">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $prof->cargo ?? 'Cargo não definido' }}</strong>
                            @if($prof->is_current)
                                <span class="badge bg-success">Atual</span>
                            @endif
                        </div>
                        <p class="mb-1">{{ $prof->empregador ?? 'Empregador não informado' }}</p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-calendar"></i>
                            {{ $prof->data_inicio ? date('d/m/Y', strtotime($prof->data_inicio)) : 'N/A' }}
                            @if(!$prof->is_current && $prof->data_fim)
                                - {{ date('d/m/Y', strtotime($prof->data_fim)) }}
                            @elseif($prof->is_current)
                                - Presente
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Publicações -->
@if($publicacoes && $publicacoes->count() > 0)
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-newspaper text-primary"></i> Publicações Recentes</h5>
            </div>
            <div class="card-body p-0">
                @foreach($publicacoes as $pub)
                    <div class="publicacao-item px-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            @if($pub->egresso->foto_url)
                                <img src="{{ asset($pub->egresso->foto_url) }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; color: #fff; font-weight: bold; font-size: 0.8rem;">
                                    {{ strtoupper(substr($pub->egresso->nome_completo, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <strong>{{ $pub->egresso->nome_completo }}</strong>
                                <br>
                                <small class="text-muted">{{ $pub->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <p class="mb-2">{{ $pub->conteudo }}</p>
                        <div class="d-flex gap-3">
                            <span class="text-muted small">
                                <i class="fas fa-heart {{ $pub->curtiu ? 'text-danger' : '' }}"></i>
                                {{ $pub->total_curtidas ?? 0 }}
                            </span>
                            <span class="text-muted small">
                                <i class="fas fa-comment"></i>
                                {{ $pub->total_comentarios ?? 0 }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Voltar -->
<div class="row mt-4">
    <div class="col-12">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@endsection