@extends('layouts.admin')

@section('title', 'Feed de Publicações')

@section('page_title', '📰 Feed de Publicações')
@section('page_subtitle', 'Gerencie todas as publicações dos egressos')

@section('content')

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 stats-card stats-card-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Publicações</div>
                        <div class="stats-value">{{ $totalPublicacoes ?? 0 }}</div>
                        <small class="stats-footer">Total</small>
                    </div>
                    <div class="stats-icon"><i class="fas fa-newspaper"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 stats-card stats-card-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Curtidas</div>
                        <div class="stats-value">{{ $totalCurtidas ?? 0 }}</div>
                        <small class="stats-footer">Total</small>
                    </div>
                    <div class="stats-icon"><i class="fas fa-heart"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 stats-card stats-card-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Comentários</div>
                        <div class="stats-value">{{ $totalComentarios ?? 0 }}</div>
                        <small class="stats-footer">Total</small>
                    </div>
                    <div class="stats-icon"><i class="fas fa-comments"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 stats-card stats-card-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stats-label">Autores</div>
                        <div class="stats-value">{{ $totalEgressos ?? 0 }}</div>
                        <small class="stats-footer">Egressos</small>
                    </div>
                    <div class="stats-icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     ALERTAS
============================================================ --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- ============================================================
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.feed.index') }}" class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted text-uppercase">Tipo</label>
                <select name="tipo" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos os tipos</option>
                    <option value="noticia" {{ request('tipo') == 'noticia' ? 'selected' : '' }}>📰 Notícia</option>
                    <option value="evento"  {{ request('tipo') == 'evento'  ? 'selected' : '' }}>🎪 Evento</option>
                    <option value="edital"  {{ request('tipo') == 'edital'  ? 'selected' : '' }}>📢 Edital</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold small text-muted text-uppercase">Pesquisar</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text"
                           name="search"
                           class="form-control border-start-0"
                           placeholder="Buscar por conteúdo ou autor..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search"></i> Buscar
                </button>
                @if(request()->has('search') || request()->has('tipo'))
                    <a href="{{ route('admin.feed.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>


{{-- ============================================================
     LISTA
============================================================ --}}
@if($publicacoes->isEmpty())

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-3"><i class="fas fa-inbox"></i></div>
            <h5 class="fw-bold">Nenhuma publicação encontrada</h5>
            <p class="text-muted mb-0">Não há publicações para exibir no momento.</p>
        </div>
    </div>

@else

    @foreach($publicacoes as $publicacao)
        @php
            $autor = $publicacao->autor;
            $isAdmin = $autor['tipo'] === 'admin';
            $nome = $autor['nome'];
            $fotoUrl = $autor['foto'];
            $iniciais = mb_strtoupper(mb_substr($nome, 0, 1));
            $corFundo = $isAdmin
                ? 'linear-gradient(135deg, #dc3545, #b02a37)'
                : 'linear-gradient(135deg, #1a56db, #3b82f6)';
        @endphp

        <div class="card border-0 shadow-sm mb-4 post-card">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-3">
                        @if($fotoUrl)
                            <img src="{{ asset($fotoUrl) }}"
                                 alt="{{ $nome }}"
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
                                <strong>{{ $nome }}</strong>
                                <span class="badge {{ $isAdmin ? 'bg-danger' : 'bg-primary' }}" style="font-size: 0.65rem;">
                                    {{ $isAdmin ? 'Admin' : 'Egresso' }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ $publicacao->tempo_decorrido }}
                            </small>
                        </div>
                    </div>

                    <span class="badge bg-info text-dark">
                        {!! $publicacao->tipo_label !!}
                    </span>
                </div>
            </div>

            <div class="card-body pt-2">
                @if($publicacao->titulo)
                    <h6 class="fw-bold mb-2">{{ $publicacao->titulo }}</h6>
                @endif

                <p class="card-text mb-3" style="white-space: pre-wrap; line-height: 1.6;">
                    {{ $publicacao->conteudo }}
                </p>

                @if($publicacao->imagem_url)
                    <div class="mb-3">
                        <img src="{{ asset($publicacao->imagem_url) }}"
                             alt="Imagem da publicação"
                             class="img-fluid rounded"
                             style="max-height: 400px;">
                    </div>
                @endif

                <div class="d-flex gap-3 text-muted small pt-2 border-top">
                    <span>
                        <i class="fas fa-heart text-danger me-1"></i>
                        {{ $publicacao->curtidas_count ?? 0 }} curtidas
                    </span>
                    <span>
                        <i class="fas fa-comment text-primary me-1"></i>
                        {{ $publicacao->comentarios_count ?? 0 }} comentários
                    </span>
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-end gap-2">
                <a href="{{ route('admin.feed.show', $publicacao->id) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i> Ver Detalhes
                </a>

                <form action="{{ route('admin.feed.destroy', $publicacao->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('Tem certeza que deseja eliminar esta publicação?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-center mt-4">
        {{ $publicacoes->appends(request()->query())->links() }}
    </div>

@endif

