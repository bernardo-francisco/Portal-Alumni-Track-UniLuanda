@extends('layouts.egresso')

@section('title', 'Resultados da Pesquisa')

@section('page_title', '🔍 Resultados da Pesquisa')
@section('page_subtitle', "Para: '{$termo}'")

@section('content')

<div class="row">
    <div class="col-12">

        {{-- ============================================================
             CABEÇALHO
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4 hero-search-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                    <div class="d-flex align-items-center gap-3">
                        <div class="hero-search-icon">
                            <i class="fas fa-search"></i>
                        </div>

                        <div>
                            <h5 class="fw-bold mb-1">Resultados da Pesquisa</h5>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted small">
                                    <strong class="text-dark">{{ $totalResultados }}</strong>
                                    {{ $totalResultados === 1 ? 'resultado' : 'resultados' }} para
                                </span>
                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                    <i class="fas fa-quote-left me-1" style="font-size: 0.6rem;"></i>
                                    {{ $termo }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('egresso.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>

                </div>
            </div>
        </div>


        {{-- ============================================================
             SEM RESULTADOS
        ============================================================ --}}
        @if($totalResultados == 0)

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon mb-4">
                        <i class="fas fa-search"></i>
                    </div>

                    <h5 class="fw-bold mb-2">Nenhum resultado encontrado</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 450px;">
                        Não encontrámos resultados para <strong>"{{ $termo }}"</strong>.
                        Tente pesquisar com outras palavras-chave.
                    </p>

                    <a href="{{ route('egresso.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>

        @else

            {{-- ============================================================
                 LISTA DE RESULTADOS
            ============================================================ --}}
            <div class="row g-4">

                {{-- ============================================
                     EGRESSOS
                ============================================ --}}
                @if($egressos->count() > 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm result-card">
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Egressos</h6>
                                            <small class="text-muted">Perfis encontrados</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill px-3">
                                        {{ $egressos->count() }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                @foreach($egressos as $egresso)
                                    @php
                                        $temFoto = !empty($egresso->foto_url);
                                        $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
                                            ->filter()
                                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                            ->take(2)
                                            ->implode('');
                                    @endphp

                                    <div class="result-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}">

                                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                            @if($temFoto)
                                                <img src="{{ asset($egresso->foto_url) }}"
                                                     alt="{{ $egresso->nome_completo }}"
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
                                                <strong class="d-block text-truncate">{{ $egresso->nome_completo }}</strong>
                                                <small class="text-muted d-block text-truncate">
                                                    <i class="fas fa-graduation-cap me-1" style="font-size: 0.7rem;"></i>
                                                    {{ $egresso->curso->nome ?? 'Curso não definido' }}
                                                </small>
                                            </div>
                                        </div>

                                        <a href="{{ route('egresso.egressos.show', $egresso->id) }}"
                                           class="btn btn-sm btn-outline-primary flex-shrink-0">
                                            <i class="fas fa-eye me-1"></i> Ver Perfil
                                        </a>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif


                {{-- ============================================
                     OPORTUNIDADES
                ============================================ --}}
                @if($oportunidades->count() > 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm result-card">
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="section-icon bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Oportunidades</h6>
                                            <small class="text-muted">Vagas e estágios encontrados</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success rounded-pill px-3">
                                        {{ $oportunidades->count() }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                @foreach($oportunidades as $op)
                                    <div class="result-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}">

                                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                            <div class="section-icon bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-briefcase"></i>
                                            </div>
                                            <div class="min-width-0">
                                                <strong class="d-block text-truncate">{{ $op->titulo }}</strong>
                                                <small class="text-muted d-block text-truncate">
                                                    <i class="fas fa-building me-1" style="font-size: 0.7rem;"></i>
                                                    {{ $op->empresa ?? 'Empresa não informada' }}
                                                </small>
                                            </div>
                                        </div>

                                        <a href="{{ route('egresso.oportunidades') }}"
                                           class="btn btn-sm btn-outline-success flex-shrink-0">
                                            <i class="fas fa-eye me-1"></i> Ver
                                        </a>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif


                {{-- ============================================
                     EVENTOS
                ============================================ --}}
                @if($eventos->count() > 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm result-card">
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="section-icon bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Eventos</h6>
                                            <small class="text-muted">Próximos eventos encontrados</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-info rounded-pill px-3">
                                        {{ $eventos->count() }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                @foreach($eventos as $evento)
                                    <div class="result-item d-flex align-items-center justify-content-between gap-2 {{ !$loop->last ? 'border-bottom' : '' }}">

                                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                                            <div class="section-icon bg-info bg-opacity-10 text-info">
                                                <i class="fas fa-calendar"></i>
                                            </div>
                                            <div class="min-width-0">
                                                <strong class="d-block text-truncate">{{ $evento->titulo }}</strong>
                                                <small class="text-muted d-block text-truncate">
                                                    <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                                    {{ date('d/m/Y H:i', strtotime($evento->data_inicio)) }}
                                                </small>
                                            </div>
                                        </div>

                                        <a href="{{ route('egresso.eventos') }}"
                                           class="btn btn-sm btn-outline-info flex-shrink-0">
                                            <i class="fas fa-eye me-1"></i> Ver
                                        </a>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif


                {{-- ============================================
                     PUBLICAÇÕES
                ============================================ --}}
                @if(isset($publicacoes) && $publicacoes->count() > 0)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm result-card">
                            <div class="card-header bg-white border-bottom py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="section-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="fas fa-newspaper"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Publicações</h6>
                                            <small class="text-muted">Publicações encontradas</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning rounded-pill px-3">
                                        {{ $publicacoes->count() }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                @foreach($publicacoes as $pub)
                                    <div class="result-item {{ !$loop->last ? 'border-bottom' : '' }}">

                                        <div class="d-flex align-items-start gap-3">
                                            <div class="section-icon bg-warning bg-opacity-10 text-warning flex-shrink-0">
                                                <i class="fas fa-newspaper"></i>
                                            </div>
                                            <div class="min-width-0 flex-grow-1">
                                                <p class="mb-2 text-dark" style="font-size: 0.925rem; line-height: 1.5;">
                                                    {{ Str::limit($pub->conteudo, 120) }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-user-circle me-1"></i>
                                                    {{ $pub->egresso->nome_completo ?? 'Anónimo' }}
                                                    <span class="mx-1">·</span>
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $pub->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>

        @endif

    </div>
</div>

@endsection
