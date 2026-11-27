@extends('layouts.egresso')

@section('title', $publicacao->titulo ?? 'Publicação')

@section('page_title', '📰 ' . Str::limit($publicacao->titulo ?? 'Publicação', 60))
@section('page_subtitle', 'Detalhe da publicação')

@section('content')

@php
    $autor      = $publicacao->autor;
    $isAdmin    = $autor['tipo'] === 'admin';
    $nomeAutor  = $autor['nome'];
    $fotoAutor  = $autor['foto'];
    $iniciais   = mb_strtoupper(mb_substr($nomeAutor, 0, 1));

    $corFundo = $isAdmin
        ? 'linear-gradient(135deg, #dc3545, #b02a37)'
        : 'linear-gradient(135deg, #1a56db, #3b82f6)';

    $tiposMap = [
        'noticia' => ['color' => 'primary', 'icon' => 'newspaper', 'label' => 'Notícia'],
        'evento'  => ['color' => 'info',    'icon' => 'calendar',  'label' => 'Evento'],
        'edital'  => ['color' => 'warning', 'icon' => 'bullhorn',  'label' => 'Edital'],
    ];
    $tipoInfo = $tiposMap[$publicacao->tipo] ?? $tiposMap['noticia'];

    $imagemUrl = $publicacao->imagem_url
        ? (filter_var($publicacao->imagem_url, FILTER_VALIDATE_URL)
            ? $publicacao->imagem_url
            : asset($publicacao->imagem_url))
        : null;
@endphp

{{-- Voltar --}}
<div class="mb-3">
    <a href="{{ route('egresso.mural.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar ao mural
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        {{-- CARTÃO PRINCIPAL --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">

            @if($imagemUrl)
                <img src="{{ $imagemUrl }}"
                     alt="{{ $publicacao->titulo }}"
                     class="img-fluid"
                     style="max-height: 480px; width: 100%; object-fit: cover;">
            @endif

            <div class="card-body p-4">

                {{-- Badges --}}
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <span class="badge bg-{{ $tipoInfo['color'] }}">
                        <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                        {{ $tipoInfo['label'] }}
                    </span>
                    @if($publicacao->destaque)
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-star me-1"></i> Destaque
                        </span>
                    @endif
                </div>

                {{-- Título --}}
                <h3 class="fw-bold mb-3">{{ $publicacao->titulo }}</h3>

                {{-- Autor + data --}}
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    @if($fotoAutor)
                        <img src="{{ asset($fotoAutor) }}"
                             alt="{{ $nomeAutor }}"
                             class="rounded-circle border"
                             style="width: 48px; height: 48px; object-fit: cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width: 48px; height: 48px; background: {{ $corFundo }}; font-size: 1.1rem;">
                            {{ $iniciais }}
                        </div>
                    @endif

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <strong>{{ $nomeAutor }}</strong>
                            <span class="badge {{ $isAdmin ? 'bg-danger' : 'bg-primary' }}" style="font-size: 0.65rem;">
                                {{ $isAdmin ? 'Admin' : 'Egresso' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>
                            {{ $publicacao->created_at->format('d/m/Y \à\s H:i') }}
                        </small>
                    </div>
                </div>

                {{-- Conteúdo --}}
                <div class="mb-4" style="white-space: pre-wrap; line-height: 1.8; font-size: 1.02rem;">
                    {{ $publicacao->conteudo }}
                </div>

                {{-- Meta (evento) --}}
                @if($publicacao->data_evento || $publicacao->local)
                    <div class="alert alert-light border mb-0">
                        @if($publicacao->data_evento)
                            <div class="mb-1">
                                <i class="fas fa-calendar-day text-primary me-2"></i>
                                <strong>Data do evento:</strong>
                                {{ $publicacao->data_evento->format('d/m/Y') }}
                            </div>
                        @endif
                        @if($publicacao->local)
                            <div>
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <strong>Local:</strong>
                                {{ $publicacao->local }}
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Footer: curtir --}}
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button"
                            class="btn btn-link text-decoration-none p-0 {{ $curtidoPeloUser ? 'text-danger' : 'text-muted' }}"
                            onclick="curtir({{ $publicacao->id }})">
                        <i class="fas fa-heart me-1"></i>
                        <span class="curtidas-count">{{ $publicacao->muralCurtidas->count() }}</span>
                    </button>

                    <small class="text-muted">
                        {{ $publicacao->muralCurtidas->count() }}
                        {{ $publicacao->muralCurtidas->count() === 1 ? 'curtida' : 'curtidas' }}
                    </small>
                </div>
            </div>

        </div>

    </div>

    {{-- SIDEBAR --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Sobre esta publicação
                </h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2">
                        <i class="fas fa-user me-2"></i>
                        {{ $nomeAutor }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-tag me-2"></i>
                        {{ $tipoInfo['label'] }}
                    </li>
                    <li class="mb-2">
                        <i class="far fa-calendar me-2"></i>
                        {{ $publicacao->created_at->format('d/m/Y') }}
                    </li>
                    @if($publicacao->local)
                        <li class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {{ $publicacao->local }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/feed.js')
@endpush