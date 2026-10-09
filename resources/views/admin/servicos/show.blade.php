@extends('layouts.admin')

@section('title', 'Detalhes do Pedido')

@section('page_title', 'Detalhes do Pedido')
@section('page_subtitle', $pedido->servico)

@section('content')

@php
    $egresso = $pedido->egresso;
    $nomeEgresso = $egresso?->nome_completo ?? 'Egresso';
    $iniciais = collect(explode(' ', trim($nomeEgresso)))
        ->filter()
        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');

    $isPendente = $pedido->status === 'pendente';
    $statusColor = $isPendente ? 'warning' : 'success';
    $statusIcon  = $isPendente ? 'clock' : 'check-circle';
    $statusLabel = ucfirst($pedido->status);

    // Ícone do serviço conforme o tipo
    $tipoServico = strtolower($pedido->servico ?? '');
    $iconeServico = 'file-alt';
    if (str_contains($tipoServico, 'declara')) $iconeServico = 'file-signature';
    elseif (str_contains($tipoServico, 'certific')) $iconeServico = 'certificate';
    elseif (str_contains($tipoServico, 'histor')) $iconeServico = 'scroll';
    elseif (str_contains($tipoServico, 'diploma')) $iconeServico = 'graduation-cap';
    elseif (str_contains($tipoServico, 'carta')) $iconeServico = 'envelope-open-text';
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">


        {{-- ============================================================
             CARD PRINCIPAL
        ============================================================ --}}
        <div class="card border-0 shadow-sm detail-card mb-4 overflow-hidden">

            {{-- HEADER COM GRADIENTE --}}
            <div class="detail-header {{ $isPendente ? 'header-warning' : 'header-success' }}">
                <div class="detail-header-content">

                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                        <div class="service-big-icon">
                            <i class="fas fa-{{ $iconeServico }}"></i>
                        </div>
                        <div class="min-width-0">
                            <h3 class="mb-1 fw-bold text-white text-truncate">
                                {{ $pedido->servico }}
                            </h3>
                            <p class="mb-0 text-white-50 small">
                                <i class="far fa-calendar me-1"></i>
                                Pedido em {{ $pedido->created_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                    </div>

                    <span class="status-pill {{ $isPendente ? 'status-pill-warning' : 'status-pill-success' }}">
                        <i class="fas fa-{{ $statusIcon }}"></i>
                        {{ $statusLabel }}
                    </span>

                </div>
            </div>


            {{-- BODY --}}
            <div class="card-body p-4 p-lg-5">

                {{-- ============================================================
                     EGRESSO
                ============================================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Solicitado por
                    </h6>

                    <div class="egresso-card">
                        <div class="egresso-avatar-wrap">
                            @if($egresso?->foto_url)
                                <img src="{{ asset($egresso->foto_url) }}"
                                     alt="{{ $nomeEgresso }}"
                                     class="egresso-avatar"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="egresso-avatar-fallback" style="display: none;">
                                    {{ $iniciais }}
                                </div>
                            @else
                                <div class="egresso-avatar-fallback">
                                    {{ $iniciais }}
                                </div>
                            @endif
                        </div>

                        <div class="egresso-info">
                            <h5 class="mb-1 fw-bold">{{ $nomeEgresso }}</h5>
                            <div class="egresso-contacts">
                                @if($egresso?->email)
                                    <a href="mailto:{{ $egresso->email }}" class="contact-chip">
                                        <i class="fas fa-envelope"></i>
                                        <span>{{ $egresso->email }}</span>
                                    </a>
                                @endif
                                @if($egresso?->telefone)
                                    <a href="tel:{{ $egresso->telefone }}" class="contact-chip">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $egresso->telefone }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>


                {{-- ============================================================
                     DESCRIÇÃO
                ============================================================ --}}
                @if($pedido->descricao || $pedido->observacoes)
                    <section class="detail-section">
                        <h6 class="section-title">
                            <i class="fas fa-align-left"></i>
                            Descrição do Pedido
                        </h6>

                        <div class="description-box">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="mb-0 description-text">
                                {!! nl2br(e($pedido->descricao ?? $pedido->observacoes)) !!}
                            </p>
                        </div>
                    </section>
                @endif


                {{-- ============================================================
                     INFORMAÇÕES
                ============================================================ --}}
                <section class="detail-section">
                    <h6 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Detalhes do Pedido
                    </h6>

                    <div class="info-grid">

                        <div class="info-tile">
                            <div class="info-tile-icon">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div class="info-tile-body">
                                <small>ID do Pedido</small>
                                <strong>#{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</strong>
                            </div>
                        </div>

                        <div class="info-tile">
                            <div class="info-tile-icon">
                                <i class="far fa-calendar-plus"></i>
                            </div>
                            <div class="info-tile-body">
                                <small>Criado em</small>
                                <strong>{{ $pedido->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>

                        @if($pedido->updated_at && $pedido->updated_at != $pedido->created_at)
                            <div class="info-tile">
                                <div class="info-tile-icon">
                                    <i class="far fa-clock"></i>
                                </div>
                                <div class="info-tile-body">
                                    <small>Última Actualização</small>
                                    <strong>{{ $pedido->updated_at->format('d/m/Y H:i') }}</strong>
                                </div>
                            </div>
                        @endif

                        <div class="info-tile">
                            <div class="info-tile-icon tile-{{ $statusColor }}">
                                <i class="fas fa-{{ $statusIcon }}"></i>
                            </div>
                            <div class="info-tile-body">
                                <small>Status</small>
                                <strong class="text-{{ $statusColor }}">{{ $statusLabel }}</strong>
                            </div>
                        </div>

                    </div>
                </section>


                {{-- ============================================================
                     AÇÕES
                ============================================================ --}}
                <div class="actions-bar">

                    <div class="actions-left">
                        @if($isPendente)
                            <form action="{{ route('admin.servicos.atender', $pedido->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Marcar este pedido como atendido?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-action">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Marcar como Atendido
                                </button>
                            </form>
                        @else
                            <span class="badge-attended">
                                <i class="fas fa-check-double"></i>
                                Pedido já atendido
                            </span>
                        @endif
                    </div>

                    <div class="actions-right">
                        <a href="{{ route('admin.servicos.index') }}"
                           class="btn btn-light border btn-action">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar
                        </a>

                        <form action="{{ route('admin.servicos.destroy', $pedido->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tem certeza que deseja eliminar este pedido? Esta acção não pode ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-action">
                                <i class="fas fa-trash-alt me-1"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection

