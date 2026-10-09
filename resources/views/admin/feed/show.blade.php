@extends('layouts.admin')

@section('title', 'Detalhes da Publicação')

@section('page_title', '📄 Detalhes da Publicação')
@section('page_subtitle', 'Visualize todos os detalhes da publicação')

@section('content')

@php
    $autor = $publicacao->autor;
    $isAdmin = $autor['tipo'] === 'admin';
    $nome = $autor['nome'];
    $fotoUrl = $autor['foto'];
    $iniciais = mb_strtoupper(mb_substr($nome, 0, 1));
    $corFundo = $isAdmin
        ? 'linear-gradient(135deg, #dc3545, #b02a37)'
        : 'linear-gradient(135deg, #1a56db, #3b82f6)';

    $egresso = $publicacao->egresso;

    // Mapear tipo → ícone e cor
    $tiposMap = [
        'noticia' => ['icon' => 'newspaper', 'color' => 'primary',   'label' => 'Notícia'],
        'evento'  => ['icon' => 'calendar',  'color' => 'info',      'label' => 'Evento'],
        'edital'  => ['icon' => 'bullhorn',  'color' => 'warning',   'label' => 'Edital'],
    ];
    $tipo = $tiposMap[$publicacao->tipo] ?? $tiposMap['noticia'];
@endphp

<div class="row g-4">

    {{-- ============================================================
         COLUNA PRINCIPAL
    ============================================================ --}}
    <div class="col-lg-8">

        {{-- Hero Card --}}
        <div class="card border-0 shadow-sm detail-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        @if($fotoUrl)
                            <img src="{{ asset($fotoUrl) }}"
                                 alt="{{ $nome }}"
                                 class="rounded-circle border border-2 border-white shadow-sm"
                                 style="width: 56px; height: 56px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                 style="width: 56px; height: 56px; background: {{ $corFundo }}; font-size: 1.3rem;">
                                {{ $iniciais }}
                            </div>
                        @endif

                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <strong class="fs-6">{{ $nome }}</strong>

                                @if($isAdmin)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-user-shield me-1"></i> Admin
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                        <i class="fas fa-user-graduate me-1"></i> Egresso
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $publicacao->created_at->format('d/m/Y \à\s H:i') }}
                            </small>
                        </div>
                    </div>

                    <span class="badge bg-{{ $tipo['color'] }}-subtle text-{{ $tipo['color'] }}-emphasis border border-{{ $tipo['color'] }}-subtle">
                        <i class="fas fa-{{ $tipo['icon'] }} me-1"></i> {{ $tipo['label'] }}
                    </span>
                </div>
            </div>
        </div>


        {{-- Conteúdo --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Conteúdo</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($publicacao->titulo)
                    <h5 class="fw-bold mb-3">{{ $publicacao->titulo }}</h5>
                @endif

                <div class="content-box">
                    {{ $publicacao->conteudo }}
                </div>

                @if($publicacao->imagem_url)
                    <h6 class="text-uppercase text-muted fw-bold small mb-2 mt-4">Imagem</h6>
                    <div>
                        <img src="{{ asset($publicacao->imagem_url) }}"
                             alt="Imagem"
                             class="img-fluid rounded shadow-sm"
                             style="max-height: 500px;">
                    </div>
                @endif
            </div>
        </div>


        {{-- Estatísticas --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card-mini stat-card-danger">
                    <div class="stat-mini-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-mini-value">{{ $publicacao->curtidas_count ?? 0 }}</div>
                    <div class="stat-mini-label">Curtidas</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-mini stat-card-primary">
                    <div class="stat-mini-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="stat-mini-value">{{ $publicacao->comentarios_count ?? 0 }}</div>
                    <div class="stat-mini-label">Comentários</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card-mini stat-card-success">
                    <div class="stat-mini-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-mini-value" style="font-size: 1rem;">
                        {{ $publicacao->created_at->format('d/m/Y') }}
                    </div>
                    <div class="stat-mini-label">Data</div>
                </div>
            </div>
        </div>


        {{-- Comentários --}}
        @if($publicacao->muralComentarios && $publicacao->muralComentarios->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Comentários</h6>
                        </div>
                        <span class="badge bg-info rounded-pill px-3">
                            {{ $publicacao->muralComentarios->count() }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    @foreach($publicacao->muralComentarios as $comentario)
                        @php
                            $comentador = $comentario->egresso;
                            $nomeComentador = $comentador->nome_completo ?? 'Egresso';
                            $fotoComentador = $comentador->foto_url ?? null;
                            $iniciaisComentador = mb_strtoupper(mb_substr($nomeComentador, 0, 1));
                        @endphp

                        <div class="d-flex align-items-start gap-3 {{ !$loop->last ? 'pb-3 mb-3 border-bottom' : '' }}">
                            @if($fotoComentador)
                                <img src="{{ asset($fotoComentador) }}"
                                     alt="{{ $nomeComentador }}"
                                     class="rounded-circle border"
                                     style="width: 42px; height: 42px; object-fit: cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width: 42px; height: 42px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.9rem;">
                                    {{ $iniciaisComentador }}
                                </div>
                            @endif

                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ $nomeComentador }}</strong>
                                    <small class="text-muted">{{ $comentario->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 text-secondary">{{ $comentario->conteudo }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif


        {{-- Ações --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">
                    <a href="{{ route('admin.feed.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>

                    <form action="{{ route('admin.feed.destroy', $publicacao->id) }}"
                          method="POST"
                          onsubmit="return confirm('Tem certeza que deseja eliminar esta publicação permanentemente?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> Eliminar Publicação
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
         SIDEBAR
    ============================================================ --}}
    <div class="col-lg-4">

        {{-- Sobre o Autor --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Sobre o Autor</h6>
                </div>
            </div>
            <div class="card-body p-4 text-center">
                @if($fotoUrl)
                    <img src="{{ asset($fotoUrl) }}"
                         alt="{{ $nome }}"
                         class="rounded-circle border border-3 border-white shadow-sm mb-3"
                         style="width: 90px; height: 90px; object-fit: cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow-sm"
                         style="width: 90px; height: 90px; background: {{ $corFundo }}; font-size: 2rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <h6 class="fw-bold mb-1">{{ $nome }}</h6>

                <span class="badge bg-{{ $isAdmin ? 'danger' : 'primary' }}-subtle text-{{ $isAdmin ? 'danger' : 'primary' }}-emphasis border border-{{ $isAdmin ? 'danger' : 'primary' }}-subtle mb-3">
                    <i class="fas fa-{{ $isAdmin ? 'user-shield' : 'user-graduate' }} me-1"></i>
                    {{ $isAdmin ? 'Administrador' : 'Egresso' }}
                </span>

                @if($egresso && $egresso->email)
                    <small class="text-muted d-block mb-3">{{ $egresso->email }}</small>
                @endif

                @if($egresso)
                    <div class="text-start">
                        @if($egresso->curso)
                            <div class="info-row">
                                <div class="info-icon text-primary">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Curso</small>
                                    <strong class="small">{{ $egresso->curso->nome }}</strong>
                                </div>
                            </div>
                        @endif

                        @if($egresso->ano_formatura)
                            <div class="info-row">
                                <div class="info-icon text-success">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Ano de Formatura</small>
                                    <strong>{{ $egresso->ano_formatura }}</strong>
                                </div>
                            </div>
                        @endif

                        <div class="info-row">
                            <div class="info-icon text-info">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="info-content">
                                <small class="text-muted d-block">Total de Publicações</small>
                                <strong>{{ $egresso->publicacoes_count ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>


        {{-- Detalhes da Publicação --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Detalhes</h6>
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
                    <div class="info-icon text-success">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Curtidas</small>
                        <strong>{{ $publicacao->curtidas_count ?? 0 }}</strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon text-primary">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Comentários</small>
                        <strong>{{ $publicacao->comentarios_count ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>


        {{-- Meta Informações --}}
        <div class="card border-0 shadow-sm mb-4">
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
                    <strong>{{ $publicacao->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>

                @if($publicacao->updated_at && $publicacao->updated_at != $publicacao->created_at)
                    <div class="meta-row">
                        <span class="text-muted">
                            <i class="fas fa-edit me-1"></i> Atualizada em
                        </span>
                        <strong>{{ $publicacao->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif

                <div class="meta-row">
                    <span class="text-muted">
                        <i class="fas fa-hashtag me-1"></i> ID
                    </span>
                    <strong>#{{ $publicacao->id }}</strong>
                </div>
            </div>
        </div>


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
                    @if($egresso)
                        <a href="{{ route('admin.egressos.show', $egresso->id) }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i> Ver Perfil do Egresso
                        </a>

                        <a href="{{ route('admin.feed.index') }}?search={{ urlencode($nome) }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-search me-2"></i> Outras publicações deste autor
                        </a>
                    @else
                        <div class="text-muted small text-center py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Publicação do sistema (Administrador)
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

