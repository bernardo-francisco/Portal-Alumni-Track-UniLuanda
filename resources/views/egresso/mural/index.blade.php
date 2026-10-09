@extends('layouts.egresso')

@section('title', 'Mural de Notícias')

@section('page_title', '📰 Mural de Notícias')
@section('page_subtitle', 'Fique por dentro das novidades da Universidade')

@section('content')

@php
    $totalPublicacoes = isset($publicacoes) ? $publicacoes->total() : 0;

    // Mapear tipo → ícone e cor
    $tiposMap = [
        'noticia' => ['color' => 'primary', 'icon' => 'newspaper', 'label' => 'Notícia'],
        'evento'  => ['color' => 'info',    'icon' => 'calendar',  'label' => 'Evento'],
        'edital'  => ['color' => 'warning', 'icon' => 'bullhorn',  'label' => 'Edital'],
    ];
@endphp


{{-- ============================================================
     CABEÇALHO
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h5 class="mb-1 fw-bold">
            <i class="fas fa-newspaper text-primary me-2"></i>
            Últimas Publicações
        </h5>
        <p class="text-muted small mb-0">
            {{ $totalPublicacoes }} {{ $totalPublicacoes === 1 ? 'publicação' : 'publicações' }}
            disponíveis
        </p>
    </div>

    {{-- Filtro por tipo (opcional) --}}
    <div class="d-flex gap-1 flex-wrap">
        <a href="{{ route('egresso.mural.index') }}"
           class="btn btn-sm {{ !request('tipo') ? 'btn-primary' : 'btn-outline-primary' }}">
            Todas
        </a>
        <a href="{{ route('egresso.mural.index', ['tipo' => 'noticia']) }}"
           class="btn btn-sm {{ request('tipo') === 'noticia' ? 'btn-primary' : 'btn-outline-primary' }}">
            <i class="fas fa-newspaper me-1"></i> Notícias
        </a>
        <a href="{{ route('egresso.mural.index', ['tipo' => 'evento']) }}"
           class="btn btn-sm {{ request('tipo') === 'evento' ? 'btn-info text-white' : 'btn-outline-info' }}">
            <i class="fas fa-calendar me-1"></i> Eventos
        </a>
        <a href="{{ route('egresso.mural.index', ['tipo' => 'edital']) }}"
           class="btn btn-sm {{ request('tipo') === 'edital' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
            <i class="fas fa-bullhorn me-1"></i> Editais
        </a>
    </div>
</div>


{{-- ============================================================
     LISTA DE PUBLICAÇÕES
============================================================ --}}
@if(isset($publicacoes) && $publicacoes->count() > 0)

    <div class="row g-4">

        @foreach($publicacoes as $pub)
            @php
                $tipoInfo = $tiposMap[$pub->tipo] ?? $tiposMap['noticia'];
                $temImagem = !empty($pub->imagem_url);
                $imagemUrl = $temImagem
                    ? (filter_var($pub->imagem_url, FILTER_VALIDATE_URL) ? $pub->imagem_url : asset($pub->imagem_url))
                    : null;
            @endphp

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 news-card overflow-hidden">

                    {{-- Imagem / Header --}}
                    <div class="news-image-wrapper">
                        @if($temImagem)
                            <img src="{{ $imagemUrl }}"
                                 alt="{{ $pub->titulo }}"
                                 class="news-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="news-image-placeholder news-image-placeholder-{{ $tipoInfo['color'] }}" style="display: none;">
                                <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                            </div>
                        @else
                            <div class="news-image-placeholder news-image-placeholder-{{ $tipoInfo['color'] }}">
                                <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                            </div>
                        @endif

                        {{-- Badges flutuantes --}}
                        <div class="news-badges">
                            <span class="badge bg-{{ $tipoInfo['color'] }}">
                                <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                {{ $tipoInfo['label'] }}
                            </span>

                            @if($pub->destaque)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i> Destaque
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Corpo --}}
                    <div class="card-body d-flex flex-column">

                        {{-- Título --}}
                        <h6 class="news-title">
                            {{ $pub->titulo }}
                        </h6>

                        {{-- Preview --}}
                        <p class="news-preview">
                            {{ Str::limit(strip_tags($pub->conteudo), 110) }}
                        </p>

                        {{-- Meta info --}}
                        <div class="news-meta">
                            @if($pub->data_evento)
                                <div class="news-meta-item">
                                    <i class="fas fa-calendar-day text-primary"></i>
                                    <span>{{ $pub->data_evento->format('d/m/Y') }}</span>
                                </div>
                            @endif

                            @if($pub->local)
                                <div class="news-meta-item">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span class="text-truncate">{{ $pub->local }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Rodapé --}}
                    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>
                            {{ $pub->data_formatada ?? $pub->created_at->format('d/m/Y') }}
                        </small>

                        <span class="news-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>

                </div>
            </div>
        @endforeach

    </div>

    {{-- Paginação --}}
    @if(method_exists($publicacoes, 'links') && $publicacoes->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $publicacoes->appends(request()->query())->links() }}
        </div>
    @endif

@else

    {{-- Estado vazio --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-newspaper"></i>
            </div>
            <h5 class="fw-bold mb-2">Nenhuma publicação</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                @if(request('tipo'))
                    Não há publicações do tipo <strong>{{ request('tipo') }}</strong>.
                    Tente remover o filtro.
                @else
                    Volte em breve para novas notícias da comunidade.
                @endif
            </p>

            @if(request('tipo'))
                <a href="{{ route('egresso.mural.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Limpar filtro
                </a>
            @endif
        </div>
    </div>

@endif

@endsection

