@extends('layouts.egresso')

@section('title', 'Mensagens')

@section('page_title', '💬 Mensagens')
@section('page_subtitle', 'As suas conversas com outros egressos')

@section('content')

@php
    $totalConversas = isset($conversasData) ? count($conversasData) : 0;
    $totalNaoLidas = collect($conversasData ?? [])->sum('nao_lidas');
    $conversasComNaoLidas = collect($conversasData ?? [])->filter(fn($c) => $c['nao_lidas'] > 0)->count();
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalConversas }}</div>
                <div class="stat-label">Total de Conversas</div>
                <small class="stat-desc">Contactos ativos</small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon-wrap">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalNaoLidas }}</div>
                <div class="stat-label">Mensagens Não Lidas</div>
                <small class="stat-desc">
                    {{ $conversasComNaoLidas }}
                    {{ $conversasComNaoLidas === 1 ? 'conversa' : 'conversas' }}
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalConversas - $conversasComNaoLidas }}</div>
                <div class="stat-label">Conversas em Dia</div>
                <small class="stat-desc">Sem mensagens pendentes</small>
            </div>
        </div>
    </div>

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
     LISTA DE CONVERSAS
============================================================ --}}
@if($totalConversas > 0)

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Conversas</h6>
                        <small class="text-muted">Ordenadas pela mais recente</small>
                    </div>
                </div>

                <a href="{{ route('egresso.rede') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i> Nova Conversa
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @foreach($conversasData as $index => $conv)
                @php
                    $contato = $conv['contato'];
                    $temFoto = !empty($contato->foto_url);

                    // URL da foto
                    $fotoUrl = null;
                    if ($temFoto) {
                        $fotoUrl = filter_var($contato->foto_url, FILTER_VALIDATE_URL)
                            ? $contato->foto_url
                            : asset($contato->foto_url);
                    }

                    $iniciais = collect(explode(' ', trim($contato->nome_completo ?? 'E')))
                        ->filter()
                        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->take(2)
                        ->implode('');

                    $coresAvatar = [
                        'linear-gradient(135deg, #1a56db, #3b82f6)',
                        'linear-gradient(135deg, #22c55e, #16a34a)',
                        'linear-gradient(135deg, #f59e0b, #d97706)',
                        'linear-gradient(135deg, #a855f7, #7e22ce)',
                        'linear-gradient(135deg, #ec4899, #db2777)',
                    ];
                    $corAvatar = $coresAvatar[$index % count($coresAvatar)];

                    $temNaoLidas = $conv['nao_lidas'] > 0;
                @endphp

                <a href="{{ route('egresso.mensagens.conversa', $contato->id) }}"
                   class="text-decoration-none text-reset">
                    <div class="conversation-item d-flex align-items-center gap-3 {{ $temNaoLidas ? 'conversation-unread' : '' }} {{ !$loop->last ? 'border-bottom' : '' }}">

                        {{-- Avatar --}}
                        <div class="position-relative flex-shrink-0">
                            @if($temFoto)
                                <img src="{{ $fotoUrl }}"
                                     alt="{{ $contato->nome_completo }}"
                                     class="rounded-circle border"
                                     style="width: 54px; height: 54px; object-fit: cover;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle align-items-center justify-content-center text-white fw-bold"
                                     style="width: 54px; height: 54px; background: {{ $corAvatar }}; font-size: 1.1rem; display: none;">
                                    {{ $iniciais }}
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width: 54px; height: 54px; background: {{ $corAvatar }}; font-size: 1.1rem;">
                                    {{ $iniciais }}
                                </div>
                            @endif

                            @if($temNaoLidas)
                                <span class="position-absolute bottom-0 end-0 bg-danger rounded-circle border border-2 border-white"
                                      style="width: 14px; height: 14px;"></span>
                            @endif
                        </div>

                        {{-- Conteúdo --}}
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong class="conversation-name text-truncate">
                                    {{ $contato->nome_completo ?? 'Egresso' }}
                                </strong>

                                <small class="text-muted flex-shrink-0 text-nowrap">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $conv['data_ultima']->diffForHumans() }}
                                </small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div class="text-muted text-truncate conversation-preview">
                                    @if($temNaoLidas)
                                        <strong class="text-dark">{{ $conv['ultima_mensagem'] }}</strong>
                                    @else
                                        {{ $conv['ultima_mensagem'] }}
                                    @endif
                                </div>

                                @if($temNaoLidas)
                                    <span class="badge bg-primary rounded-pill flex-shrink-0 px-2">
                                        {{ $conv['nao_lidas'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Seta --}}
                        <div class="conversation-arrow flex-shrink-0">
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

@else

    {{-- Estado vazio --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-comments"></i>
            </div>
            <h5 class="fw-bold mb-2">Sem conversas iniciadas</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 420px;">
                Ainda não tem conversas. Vá à <strong>Rede de Contactos</strong> para se conectar com outros egressos e começar a conversar.
            </p>
            <a href="{{ route('egresso.rede') }}" class="btn btn-primary">
                <i class="fas fa-users me-1"></i> Ver Rede de Contactos
            </a>
        </div>
    </div>

@endif

@endsection

