@extends('layouts.admin')

@section('title', $publicacao->titulo)

@section('page_title', '📄 Detalhes da Publicação')
@section('page_subtitle', $publicacao->titulo)

@section('content')

@php
    $isDoEgresso = !is_null($publicacao->egresso_id);
    $autor = $publicacao->autor;

    // Determinar tipo de autor
    $isAdmin = $autor['tipo'] === 'admin';
    $nomeAutor = $autor['nome'];
    $fotoAutor = $autor['foto'];
    $iniciaisAutor = mb_strtoupper(mb_substr($nomeAutor, 0, 1));

    $corFundo = $isAdmin
        ? 'linear-gradient(135deg, #dc3545, #b02a37)'
        : 'linear-gradient(135deg, #1a56db, #3b82f6)';

    // Mapear tipo → ícone e cor
    $tiposMap = [
        'noticia' => ['icon' => 'newspaper', 'color' => 'primary',   'label' => 'Notícia'],
        'evento'  => ['icon' => 'calendar',  'color' => 'info',      'label' => 'Evento'],
        'edital'  => ['icon' => 'bullhorn',  'color' => 'warning',   'label' => 'Edital'],
    ];
    $tipo = $tiposMap[$publicacao->tipo] ?? $tiposMap['noticia'];

    // Estado
    $estadoPublicacao = $publicacao->publicado ? 'Publicado' : 'Não Publicado';
    $estadoCor = $publicacao->publicado ? 'success' : 'secondary';
@endphp

<div class="row g-4">

    {{-- ============================================================
         COLUNA PRINCIPAL
    ============================================================ --}}
    <div class="col-lg-8">

        {{-- Aviso quando é publicação de egresso --}}
        @if($isDoEgresso)
            <div class="alert alert-info d-flex align-items-start gap-3 border-0 shadow-sm">
                <div class="alert-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <strong class="d-block">Publicação de Egresso</strong>
                    <small class="text-muted">
                        Não pode editar publicações criadas por egressos. Pode apenas visualizar ou eliminar (moderação).
                    </small>
                </div>
            </div>
        @endif

        {{-- Hero Card --}}
        <div class="card border-0 shadow-sm hero-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        {{-- Avatar do Autor --}}
                        @if($fotoAutor)
                            <img src="{{ asset($fotoAutor) }}"
                                 alt="{{ $nomeAutor }}"
                                 class="rounded-circle border border-2 border-white shadow-sm"
                                 style="width: 52px; height: 52px; object-fit: cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                 style="width: 52px; height: 52px; background: {{ $corFundo }}; font-size: 1.1rem;">
                                {{ $iniciaisAutor }}
                            </div>
                        @endif

                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <strong class="fs-6">{{ $nomeAutor }}</strong>

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
                                {{ $publicacao->data_formatada }}
                            </small>
                        </div>
                    </div>

                    {{-- Badges de estado --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-{{ $tipo['color'] }}-subtle text-{{ $tipo['color'] }}-emphasis border border-{{ $tipo['color'] }}-subtle">
                            <i class="fas fa-{{ $tipo['icon'] }} me-1"></i> {{ $tipo['label'] }}
                        </span>
                        <span class="badge bg-{{ $estadoCor }}-subtle text-{{ $estadoCor }}-emphasis border border-{{ $estadoCor }}-subtle">
                            <i class="fas fa-{{ $publicacao->publicado ? 'check-circle' : 'eye-slash' }} me-1"></i>
                            {{ $estadoPublicacao }}
                        </span>
                        @if($publicacao->destaque)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="fas fa-star me-1"></i> Destaque
                            </span>
                        @endif
                    </div>
                </div>

                <h2 class="fw-bold mt-4 mb-3">{{ $publicacao->titulo }}</h2>

                {{-- Info rápida --}}
                <div class="d-flex flex-wrap gap-3 text-muted small">
                    @if($publicacao->data_evento)
                        <span>
                            <i class="fas fa-calendar text-primary me-1"></i>
                            <strong>{{ $publicacao->data_evento->format('d/m/Y') }}</strong>
                        </span>
                    @endif

                    @if($publicacao->local)
                        <span>
                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                            <strong>{{ $publicacao->local }}</strong>
                        </span>
                    @endif

                    <span>
                        <i class="fas fa-hashtag text-secondary me-1"></i>
                        <strong>#{{ $publicacao->id }}</strong>
                    </span>
                </div>
            </div>
        </div>


        {{-- Imagem de Capa --}}
        @if($publicacao->imagem_url)
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <img src="{{ asset($publicacao->imagem_url) }}"
                     alt="{{ $publicacao->titulo }}"
                     class="card-img-top"
                     style="max-height: 500px; object-fit: cover;">
            </div>
        @endif


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
                @if($publicacao->conteudo)
                    <div class="content-body">
                        {!! nl2br(e($publicacao->conteudo)) !!}
                    </div>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Sem conteúdo.
                    </p>
                @endif
            </div>
        </div>


        {{-- Ações --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$isDoEgresso)
                            <a href="{{ route('admin.mural.edit', $publicacao->id) }}"
                               class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Editar Publicação
                            </a>
                        @else
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    disabled
                                    title="Não pode editar publicações de egressos">
                                <i class="fas fa-lock me-1"></i> Edição bloqueada
                            </button>
                        @endif

                        <a href="{{ route('admin.mural.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>

                    <form action="{{ route('admin.mural.destroy', $publicacao->id) }}"
                          method="POST"
                          onsubmit="return confirm('Tem certeza que deseja eliminar esta publicação?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> Eliminar
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
                @if($fotoAutor)
                    <img src="{{ asset($fotoAutor) }}"
                         alt="{{ $nomeAutor }}"
                         class="rounded-circle border border-3 border-white shadow-sm mb-3"
                         style="width: 90px; height: 90px; object-fit: cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow-sm"
                         style="width: 90px; height: 90px; background: {{ $corFundo }}; font-size: 2rem;">
                        {{ $iniciaisAutor }}
                    </div>
                @endif

                <h6 class="fw-bold mb-1">{{ $nomeAutor }}</h6>

                <span class="badge bg-{{ $isAdmin ? 'danger' : 'primary' }}-subtle text-{{ $isAdmin ? 'danger' : 'primary' }}-emphasis border border-{{ $isAdmin ? 'danger' : 'primary' }}-subtle mb-3">
                    <i class="fas fa-{{ $isAdmin ? 'user-shield' : 'user-graduate' }} me-1"></i>
                    {{ $isAdmin ? 'Administrador' : 'Egresso' }}
                </span>

                @if($publicacao->egresso && $publicacao->egresso->curso)
                    <div class="info-row text-start">
                        <div class="info-icon text-primary">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Curso</small>
                            <strong class="small">{{ $publicacao->egresso->curso->nome }}</strong>
                        </div>
                    </div>
                @endif

                @if(!$isAdmin && $publicacao->egresso)
                    <div class="mt-3">
                        <a href="{{ route('admin.egressos.show', $publicacao->egresso->id) }}"
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-user me-1"></i> Ver Perfil do Egresso
                        </a>
                    </div>
                @endif
            </div>
        </div>


        {{-- Informações da Publicação --}}
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
                    <div class="info-icon text-{{ $estadoCor }}">
                        <i class="fas fa-{{ $publicacao->publicado ? 'check-circle' : 'eye-slash' }}"></i>
                    </div>
                    <div class="info-content">
                        <small class="text-muted d-block">Estado</small>
                        <strong>{{ $estadoPublicacao }}</strong>
                    </div>
                </div>

                @if($publicacao->destaque)
                    <div class="info-row">
                        <div class="info-icon text-warning">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Visibilidade</small>
                            <strong>Em Destaque</strong>
                        </div>
                    </div>
                @endif

                @if($publicacao->data_evento)
                    <div class="info-row">
                        <div class="info-icon text-primary">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Data do Evento</small>
                            <strong>{{ $publicacao->data_evento->format('d/m/Y') }}</strong>
                        </div>
                    </div>
                @endif

                @if($publicacao->local)
                    <div class="info-row">
                        <div class="info-icon text-danger">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <small class="text-muted d-block">Local</small>
                            <strong>{{ $publicacao->local }}</strong>
                        </div>
                    </div>
                @endif

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

    </div>

</div>

@endsection


