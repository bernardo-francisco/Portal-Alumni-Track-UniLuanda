@extends('layouts.admin')

@section('title', 'Mural de Notícias')

@section('page_title', '📰 Mural de Notícias')
@section('page_subtitle', 'Gerencie as notícias e eventos para os egressos')

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
            Publicações do Mural
        </h5>
        <p class="text-muted small mb-0">
            {{ $totalPublicacoes }} {{ $totalPublicacoes === 1 ? 'publicação' : 'publicações' }}
            registadas
        </p>
    </div>

    <a href="{{ route('admin.mural.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nova Publicação
    </a>
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


{{-- ============================================================
     LISTA DE PUBLICAÇÕES
============================================================ --}}
@if(isset($publicacoes) && $publicacoes->count() > 0)

    <div class="row g-4">

        @foreach($publicacoes as $pub)
            @php
                $isDoEgresso = !is_null($pub->egresso_id);

                $tipoInfo = $tiposMap[$pub->tipo] ?? $tiposMap['noticia'];

                $temImagem = !empty($pub->imagem_url);
                $imagemUrl = $temImagem
                    ? (filter_var($pub->imagem_url, FILTER_VALIDATE_URL) ? $pub->imagem_url : asset($pub->imagem_url))
                    : null;
            @endphp

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 news-card overflow-hidden">

                    {{-- Imagem / Placeholder --}}
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
                            <div class="d-flex gap-1 flex-wrap">
                                <span class="badge bg-{{ $tipoInfo['color'] }}">
                                    <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                    {{ $tipoInfo['label'] }}
                                </span>

                                @if($isDoEgresso)
                                    <span class="badge bg-info">
                                        <i class="fas fa-user-graduate me-1"></i> Egresso
                                    </span>
                                @else
                                    <span class="badge bg-dark">
                                        <i class="fas fa-user-shield me-1"></i> Admin
                                    </span>
                                @endif
                            </div>

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
                        <h6 class="news-title">{{ $pub->titulo }}</h6>

                        {{-- Preview --}}
                        <p class="news-preview">
                            {{ Str::limit(strip_tags($pub->conteudo), 110) }}
                        </p>

                        {{-- Meta info --}}
                        @if($pub->data_evento || $pub->local)
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
                        @endif
                    </div>

                    {{-- Rodapé --}}
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $pub->data_formatada ?? $pub->created_at->format('d/m/Y') }}
                            </small>
                        </div>

                        <div class="d-flex gap-2">

                            {{-- Editar (só para Admin) --}}
                            @if(!$isDoEgresso)
                                <a href="{{ route('admin.mural.edit', $pub->id) }}"
                                   class="btn btn-sm btn-outline-primary flex-fill"
                                   title="Editar publicação">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                            @else
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary flex-fill"
                                        disabled
                                        title="Publicações de egressos não podem ser editadas">
                                    <i class="fas fa-lock me-1"></i> Bloqueada
                                </button>
                            @endif

                            {{-- Eliminar (moderação) --}}
                            <form action="{{ route('admin.mural.destroy', $pub->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Tem certeza que deseja eliminar esta publicação?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar publicação">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </div>
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
                Ainda não há publicações no mural. Crie a primeira para partilhar com os egressos.
            </p>
            <a href="{{ route('admin.mural.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nova Publicação
            </a>
        </div>
    </div>

@endif

@endsection

