@extends('layouts.admin')

@section('title', 'Detalhes do Feedback')

@section('page_title', '💬 Detalhes do Feedback')
@section('page_subtitle', $feedback->titulo ?? 'Feedback do Egresso')

@section('content')

@php
    $egresso = $feedback->egresso;
    $user = $egresso?->user;

    // ✅ FOTO: prioridade egresso → user
    $fotoUrl = $egresso?->foto_url ?? $user?->photo_url ?? null;
    $nomeEgresso = $egresso?->nome_completo ?? $user?->name ?? 'Egresso';
    $iniciais = mb_strtoupper(mb_substr($nomeEgresso, 0, 1));

    // Estado
    $estados = [
        'pendente'  => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
        'aprovado'  => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
        'rejeitado' => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
    ];
    $estado = $estados[$feedback->status] ?? $estados['pendente'];
@endphp


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
     HERO CARD
============================================================ --}}
<div class="card border-0 shadow-sm hero-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3 flex-grow-1">
                @if($fotoUrl)
                    <img src="{{ asset($fotoUrl) }}"
                         alt="{{ $nomeEgresso }}"
                         class="rounded-circle border border-2 border-white shadow-sm"
                         style="width: 56px; height: 56px; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="rounded-circle align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width: 56px; height: 56px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1.3rem; display: none;">
                        {{ $iniciais }}
                    </div>
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width: 56px; height: 56px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 1.3rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <strong class="fs-6">{{ $nomeEgresso }}</strong>
                        <span class="badge bg-{{ $estado['color'] }}-subtle text-{{ $estado['color'] }}-emphasis border border-{{ $estado['color'] }}-subtle">
                            <i class="fas fa-{{ $estado['icon'] }} me-1"></i> {{ $estado['label'] }}
                        </span>
                    </div>
                    <small class="text-muted">
                        <i class="far fa-clock me-1"></i>
                        Enviado {{ $feedback->created_at->diffForHumans() }}
                    </small>
                </div>
            </div>

            {{-- Nota em estrelas --}}
            @if($feedback->nota)
                <div class="text-end">
                    <div class="stars-large">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $feedback->nota ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted fw-bold">{{ $feedback->nota }}/5</small>
                </div>
            @endif
        </div>
    </div>
</div>


<div class="row g-4">

    {{-- ============================================================
         COLUNA PRINCIPAL
    ============================================================ --}}
    <div class="col-lg-8">

        {{-- Conteúdo --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Conteúdo do Feedback</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($feedback->titulo)
                    <h5 class="fw-bold mb-3">{{ $feedback->titulo }}</h5>
                @endif

                @if($feedback->curso)
                    <span class="badge bg-light text-dark border mb-3">
                        <i class="fas fa-graduation-cap me-1"></i>
                        {{ $feedback->curso->nome }}
                    </span>
                @endif

                <div class="content-box">
                    {{ $feedback->mensagem ?? $feedback->conteudo ?? 'Sem conteúdo.' }}
                </div>
            </div>
        </div>


        {{-- Meta Informações --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Meta Informações</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Data de Envio</small>
                                <strong>{{ $feedback->created_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Última Atualização</small>
                                <strong>{{ $feedback->updated_at->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">ID do Feedback</small>
                                <strong>#{{ $feedback->id }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-box">
                            <div class="meta-box-icon bg-{{ $estado['color'] }} bg-opacity-10 text-{{ $estado['color'] }}">
                                <i class="fas fa-{{ $estado['icon'] }}"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Estado Atual</small>
                                <strong>{{ $estado['label'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Ações --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                    <a href="{{ route('admin.feedbacks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>

                    <div class="d-flex gap-2 flex-wrap">
                        @if($feedback->status === 'pendente')
                            <form action="{{ route('admin.feedbacks.aprovar', $feedback->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Tem certeza que deseja aprovar este feedback?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Aprovar
                                </button>
                            </form>

                            <form action="{{ route('admin.feedbacks.rejeitar', $feedback->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Tem certeza que deseja rejeitar este feedback?');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-times me-1"></i> Rejeitar
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.feedbacks.destroy', $feedback->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tem certeza que deseja eliminar este feedback permanentemente?');">
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

    </div>


    {{-- ============================================================
         SIDEBAR
    ============================================================ --}}
    <div class="col-lg-4">

        {{-- Sobre o Egresso --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-user"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Sobre o Egresso</h6>
                </div>
            </div>
            <div class="card-body p-4 text-center">
                @if($fotoUrl)
                    <img src="{{ asset($fotoUrl) }}"
                         alt="{{ $nomeEgresso }}"
                         class="rounded-circle border border-3 border-white shadow-sm mb-3"
                         style="width: 90px; height: 90px; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="rounded-circle align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow-sm"
                         style="width: 90px; height: 90px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 2rem; display: none;">
                        {{ $iniciais }}
                    </div>
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3 shadow-sm"
                         style="width: 90px; height: 90px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 2rem;">
                        {{ $iniciais }}
                    </div>
                @endif

                <h6 class="fw-bold mb-1">{{ $nomeEgresso }}</h6>

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

                        @if($egresso->telefone)
                            <div class="info-row">
                                <div class="info-icon text-info">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted d-block">Telefone</small>
                                    <strong>{{ $egresso->telefone }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('admin.egressos.show', $egresso->id) }}"
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-user me-1"></i> Ver Perfil Completo
                        </a>
                    </div>
                @endif
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
                    <a href="{{ route('admin.feedbacks.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> Todos os Feedbacks
                    </a>

                    <a href="{{ route('admin.feedbacks.index', ['status' => 'pendente']) }}"
                       class="btn btn-outline-warning">
                        <i class="fas fa-clock me-1"></i> Apenas Pendentes
                    </a>

                    @if($feedback->status !== 'pendente')
                        <a href="{{ route('admin.feedbacks.index', ['status' => $feedback->status]) }}"
                           class="btn btn-outline-{{ $estado['color'] }}">
                            <i class="fas fa-{{ $estado['icon'] }} me-1"></i> Outros {{ $estado['label'] }}s
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

