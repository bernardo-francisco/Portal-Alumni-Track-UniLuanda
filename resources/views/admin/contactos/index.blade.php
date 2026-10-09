@extends('layouts.admin')

@section('title', 'Mensagens de Apoio')

@section('page_title', '📩 Mensagens de Apoio')
@section('page_subtitle', 'Gerir as mensagens enviadas pelo formulário de Apoio ao Alumni')

@section('content')

@php
    $stats = $stats ?? ['total' => 0, 'novos' => 0, 'respondidos' => 0, 'arquivados' => 0];

    $statusMap = [
        'novo'       => ['color' => 'primary',   'icon' => 'circle',        'label' => 'Novo'],
        'lido'       => ['color' => 'info',      'icon' => 'eye',           'label' => 'Lido'],
        'respondido' => ['color' => 'success',   'icon' => 'check-circle',  'label' => 'Respondido'],
        'arquivado'  => ['color' => 'secondary', 'icon' => 'archive',       'label' => 'Arquivado'],
    ];

    $statsCards = [
        ['color' => 'primary',   'icon' => 'envelope',      'label' => 'Total',       'value' => $stats['total'],       'desc' => 'Mensagens'],
        ['color' => 'warning',   'icon' => 'envelope-open', 'label' => 'Novos',       'value' => $stats['novos'],       'desc' => 'Por ler'],
        ['color' => 'success',   'icon' => 'check-circle',  'label' => 'Respondidos', 'value' => $stats['respondidos'], 'desc' => 'Concluídos'],
        ['color' => 'secondary', 'icon' => 'archive',       'label' => 'Arquivados',  'value' => $stats['arquivados'],  'desc' => 'Guardados'],
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">

        {{-- HEADER PRINCIPAL --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="detail-header header-primary">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="service-big-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 fw-bold text-white">
                            Mensagens de Apoio
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-inbox me-1"></i>
                            {{ $stats['total'] }} {{ $stats['total'] === 1 ? 'mensagem' : 'mensagens' }} no total
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>

        {{-- ALERTAS --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ESTATÍSTICAS --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-lg-5">
                <section class="detail-section mb-0">
                    <h6 class="section-title">
                        <i class="fas fa-chart-pie"></i> Resumo
                    </h6>

                    <div class="row g-3">
                        @foreach($statsCards as $s)
                            <div class="col-6 col-md-3">
                                <div class="info-tile">
                                    <div class="info-tile-icon text-{{ $s['color'] }}">
                                        <i class="fas fa-{{ $s['icon'] }}"></i>
                                    </div>
                                    <div class="info-tile-body">
                                        <small>{{ $s['label'] }}</small>
                                        <strong>{{ $s['value'] }}</strong>
                                        <div class="text-muted" style="font-size: .75rem;">
                                            {{ $s['desc'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-lg-5">
                <section class="detail-section mb-0">
                    <h6 class="section-title">
                        <i class="fas fa-filter"></i> Filtrar
                    </h6>

                    <form method="GET" action="{{ route('admin.contactos.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">
                                <i class="fas fa-search me-1"></i> Buscar
                            </label>
                            <input type="text"
                                   name="search"
                                   class="form-control bg-light border-0"
                                   placeholder="Nome, email ou assunto..."
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">
                                <i class="fas fa-tag me-1"></i> Estado
                            </label>
                            <select name="status" class="form-select bg-light border-0">
                                <option value="todos" {{ request('status') == 'todos' ? 'selected' : '' }}>Todos</option>
                                <option value="novo" {{ request('status') == 'novo' ? 'selected' : '' }}>Novos</option>
                                <option value="lido" {{ request('status') == 'lido' ? 'selected' : '' }}>Lidos</option>
                                <option value="respondido" {{ request('status') == 'respondido' ? 'selected' : '' }}>Respondidos</option>
                                <option value="arquivado" {{ request('status') == 'arquivado' ? 'selected' : '' }}>Arquivados</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        {{-- LISTA DE MENSAGENS --}}
        @if(isset($contactos) && $contactos->count() > 0)

            @foreach($contactos as $contacto)
                @php
                    $statusInfo = $statusMap[$contacto->status]
                        ?? ['color' => 'secondary', 'icon' => 'info-circle', 'label' => ucfirst($contacto->status)];
                @endphp

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">

                    <div class="detail-header header-{{ $statusInfo['color'] }}">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="service-big-icon">
                                <i class="fas fa-{{ $statusInfo['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold text-white">
                                    {{ $contacto->nome }}
                                </h5>
                                <p class="mb-0 text-white-50 small">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $contacto->email }}
                                    <span class="mx-1">•</span>
                                    <i class="far fa-calendar me-1"></i>
                                    {{ $contacto->created_at->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                            <span class="status-pill">
                                <i class="fas fa-{{ $statusInfo['icon'] }}"></i>
                                {{ $statusInfo['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 p-lg-5">

                        <section class="detail-section">
                            <h6 class="section-title">
                                <i class="fas fa-info-circle"></i> Detalhes da Mensagem
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="fas fa-user"></i></div>
                                        <div class="info-tile-body">
                                            <small>Nome</small>
                                            <strong>{{ $contacto->nome }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="fas fa-envelope"></i></div>
                                        <div class="info-tile-body">
                                            <small>Email</small>
                                            <strong>
                                                <a href="mailto:{{ $contacto->email }}" class="text-decoration-none">
                                                    {{ $contacto->email }}
                                                </a>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="fas fa-tag"></i></div>
                                        <div class="info-tile-body">
                                            <small>Assunto</small>
                                            <strong>{{ $contacto->assunto }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="far fa-clock"></i></div>
                                        <div class="info-tile-body">
                                            <small>Recebido em</small>
                                            <strong>{{ $contacto->created_at->format('d/m/Y \à\s H:i') }}</strong>
                                            <div class="text-muted" style="font-size: .75rem;">
                                                {{ $contacto->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        @if($contacto->mensagem)
                            <section class="detail-section">
                                <h6 class="section-title">
                                    <i class="fas fa-comment-alt"></i> Mensagem
                                </h6>
                                <div class="description-box">
                                    <i class="fas fa-quote-left quote-icon"></i>
                                    <p class="mb-0">{!! nl2br(e(\Illuminate\Support\Str::limit($contacto->mensagem, 350))) !!}</p>
                                </div>
                            </section>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.contactos.show', $contacto->id) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                            </a>

                            <a href="mailto:{{ $contacto->email }}"
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-envelope me-1"></i> Responder por Email
                            </a>

                            <form action="{{ route('admin.contactos.destroy', $contacto->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Tens a certeza que queres eliminar esta mensagem?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i> Eliminar
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center mt-4">
                {{ $contactos->links('pagination::bootstrap-5') }}
            </div>

        @else

            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="detail-header header-secondary">
                    <div class="d-flex align-items-center gap-3">
                        <div class="service-big-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold text-white">Sem mensagens</h3>
                            <p class="mb-0 text-white-50 small">Ainda não recebeste mensagens de apoio</p>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center py-5">
                    <p class="text-muted small mb-4 mx-auto" style="max-width: 400px;">
                        Ainda não recebeste mensagens pelo formulário de Apoio ao Alumni.
                    </p>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>

        @endif

    </div>
</div>

@endsection