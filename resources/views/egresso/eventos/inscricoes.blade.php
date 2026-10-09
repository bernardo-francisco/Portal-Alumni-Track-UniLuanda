@extends('layouts.egresso')

@section('title', 'Minhas Inscrições')

@section('page_title', '📅 Minhas Inscrições')
@section('page_subtitle', 'Histórico de eventos em que se inscreveu')

@section('content')

@php
    $total        = $inscricoes->count();
    $pendentes    = $inscricoes->filter(fn($i) => $i->status === 'pendente')->count();
    $confirmadas  = $inscricoes->filter(fn($i) => $i->status === 'confirmada')->count();
    $certificados = $inscricoes->filter(fn($i) => $i->presente
                        && $i->status === 'confirmada'
                        && $i->evento->data_inicio
                        && $i->evento->data_inicio->isPast())->count();

    $statusMap = [
        'pendente'   => ['color' => 'warning',   'icon' => 'hourglass-half', 'label' => 'Pendente'],
        'confirmada' => ['color' => 'success',   'icon' => 'check-circle',   'label' => 'Confirmada'],
        'rejeitada'  => ['color' => 'danger',    'icon' => 'times-circle',   'label' => 'Rejeitada'],
        'cancelada'  => ['color' => 'secondary', 'icon' => 'ban',            'label' => 'Cancelada'],
    ];

    $stats = [
        ['color' => 'primary', 'icon' => 'clipboard-list', 'label' => 'Total',        'value' => $total,        'desc' => 'Inscrições'],
        ['color' => 'warning', 'icon' => 'hourglass-half', 'label' => 'Pendentes',    'value' => $pendentes,    'desc' => 'A aguardar'],
        ['color' => 'success', 'icon' => 'check-circle',   'label' => 'Confirmadas',  'value' => $confirmadas,  'desc' => 'Aprovadas'],
        ['color' => 'info',    'icon' => 'certificate',    'label' => 'Certificados', 'value' => $certificados, 'desc' => 'Disponíveis'],
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        {{-- ============================================
             HEADER PRINCIPAL
        ============================================ --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
            <div class="detail-header header-primary">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="service-big-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 fw-bold text-white">
                            Histórico de Inscrições
                        </h3>
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-list me-1"></i>
                            {{ $total }} {{ $total === 1 ? 'inscrição' : 'inscrições' }} no total
                        </p>
                    </div>
                    <a href="{{ route('egresso.eventos') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-calendar me-1"></i> Ver Eventos
                    </a>
                </div>
            </div>
        </div>

        @if($total > 0)

            {{-- ============================================
                 RESUMO (estatísticas)
            ============================================ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
                <div class="card-body p-4 p-lg-5">
                    <section class="detail-section mb-0">
                        <h6 class="section-title">
                            <i class="fas fa-chart-pie"></i> Resumo
                        </h6>

                        <div class="row g-3">
                            @foreach($stats as $s)
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

            {{-- ============================================
                 LISTA DE INSCRIÇÕES
            ============================================ --}}
            @foreach($inscricoes as $inscricao)
                @php
                    $evento = $inscricao->evento;

                    $dataPassada      = $evento->data_inicio && $evento->data_inicio->isPast();
                    $podeCertificado  = $dataPassada && $inscricao->presente && $inscricao->status === 'confirmada';
                    $podeCancelar     = !$dataPassada
                                        && !in_array($inscricao->status, ['rejeitada', 'cancelada'])
                                        && ($evento->permite_cancelamento ?? true);
                    $podeComprovativo = $inscricao->status === 'confirmada';

                    $statusInfo = $statusMap[$inscricao->status]
                        ?? ['color' => 'secondary', 'icon' => 'info-circle', 'label' => ucfirst($inscricao->status)];

                    $catSlug = str_replace([' ', '-'], '_', strtolower($evento->categoria ?? ''));
                    $catInfo = match(true) {
                        str_contains($catSlug, 'workshop')    => ['color' => 'info',      'icon' => 'tools'],
                        str_contains($catSlug, 'palestra')    => ['color' => 'primary',   'icon' => 'microphone'],
                        str_contains($catSlug, 'seminario')   => ['color' => 'warning',   'icon' => 'chalkboard-teacher'],
                        str_contains($catSlug, 'feira')       => ['color' => 'success',   'icon' => 'store'],
                        str_contains($catSlug, 'conferencia') => ['color' => 'danger',    'icon' => 'users'],
                        str_contains($catSlug, 'formacao')    => ['color' => 'secondary', 'icon' => 'book'],
                        default                               => ['color' => 'primary',   'icon' => 'calendar'],
                    };
                @endphp

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">

                    {{-- HEADER DO CARD --}}
                    <div class="detail-header header-{{ $statusInfo['color'] }}">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="service-big-icon">
                                <i class="fas fa-{{ $catInfo['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold text-white">
                                    {{ $evento->titulo }}
                                </h5>
                                <p class="mb-0 text-white-50 small">
                                    <i class="far fa-calendar me-1"></i>
                                    {{ $evento->data_inicio ? $evento->data_inicio->format('d/m/Y') : 'Data a definir' }}
                                    @if($evento->data_inicio)
                                        <span class="mx-1">•</span>
                                        <i class="far fa-clock me-1"></i>
                                        {{ $evento->data_inicio->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <span class="status-pill">
                                <i class="fas fa-{{ $statusInfo['icon'] }}"></i>
                                {{ $statusInfo['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 p-lg-5">

                        {{-- BADGE PRESENÇA (evento passado) --}}
                        @if($dataPassada && $inscricao->status === 'confirmada')
                            <div class="mb-4">
                                @if($inscricao->presente)
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <i class="fas fa-check-circle me-1"></i> Presente
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-times-circle me-1"></i> Ausente
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- DADOS DO EVENTO --}}
                        <section class="detail-section">
                            <h6 class="section-title">
                                <i class="fas fa-info-circle"></i> Detalhes do Evento
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="far fa-calendar"></i></div>
                                        <div class="info-tile-body">
                                            <small>Data de Início</small>
                                            <strong>
                                                {{ $evento->data_inicio ? $evento->data_inicio->format('d/m/Y \à\s H:i') : 'A definir' }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                @if($evento->local)
                                    <div class="col-md-6">
                                        <div class="info-tile">
                                            <div class="info-tile-icon"><i class="fas fa-map-marker-alt"></i></div>
                                            <div class="info-tile-body">
                                                <small>Local</small>
                                                <strong>{{ $evento->local }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <div class="info-tile">
                                        <div class="info-tile-icon"><i class="fas fa-tag"></i></div>
                                        <div class="info-tile-body">
                                            <small>Tipo</small>
                                            <strong>{{ ucfirst($evento->tipo ?? 'Presencial') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                @if($evento->categoria)
                                    <div class="col-md-6">
                                        <div class="info-tile">
                                            <div class="info-tile-icon"><i class="fas fa-star"></i></div>
                                            <div class="info-tile-body">
                                                <small>Categoria</small>
                                                <strong>{{ ucfirst(str_replace('_', ' ', $evento->categoria)) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        {{-- DESCRIÇÃO --}}
                        @if($evento->descricao)
                            <section class="detail-section">
                                <h6 class="section-title">
                                    <i class="fas fa-align-left"></i> Sobre o Evento
                                </h6>
                                <div class="description-box">
                                    <i class="fas fa-quote-left quote-icon"></i>
                                    <p class="mb-0">
                                        {!! nl2br(e(\Illuminate\Support\Str::limit($evento->descricao, 300))) !!}
                                    </p>
                                </div>
                            </section>
                        @endif

                        {{-- AVISO PENDENTE --}}
                        @if($inscricao->status === 'pendente')
                            <section class="detail-section">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    A sua inscrição está a aguardar aprovação. O comprovativo ficará disponível após confirmação.
                                </div>
                            </section>
                        @endif

                        {{-- MOTIVO DA REJEIÇÃO --}}
                        @if($inscricao->status === 'rejeitada' && $inscricao->motivo_rejeicao)
                            <section class="detail-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle"></i> Motivo da Rejeição
                                </h6>
                                <div class="alert alert-danger mb-0">
                                    {!! nl2br(e($inscricao->motivo_rejeicao)) !!}
                                </div>
                            </section>
                        @endif

                        {{-- AÇÕES --}}
                        <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                            @if($podeComprovativo)
                                <a href="{{ route('egresso.eventos.comprovativo', $inscricao->id) }}"
                                   class="btn btn-outline-primary btn-sm"
                                   target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> Comprovativo
                                </a>
                            @else
                                <button class="btn btn-outline-secondary btn-sm" disabled
                                        title="Disponível após aprovação">
                                    <i class="fas fa-file-pdf me-1"></i> Comprovativo
                                </button>
                            @endif

                            @if($podeCertificado)
                                <a href="{{ route('egresso.certificados.download', $inscricao->id) }}"
                                   class="btn btn-success btn-sm">
                                    <i class="fas fa-certificate me-1"></i> Certificado
                                </a>
                            @endif

                            @if($podeCancelar)
                                <form action="{{ route('egresso.minhas.inscricoes.cancelar', $inscricao->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tens a certeza que queres cancelar esta inscrição?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach

        @else

            {{-- ESTADO VAZIO --}}
            <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                <div class="detail-header header-secondary">
                    <div class="d-flex align-items-center gap-3">
                        <div class="service-big-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <h3 class="mb-1 fw-bold text-white">Sem inscrições</h3>
                            <p class="mb-0 text-white-50 small">Ainda não te inscreveste em nenhum evento</p>
                        </div>
                    </div>
                </div>
                <div class="card-body text-center py-5">
                    <p class="text-muted small mb-4 mx-auto" style="max-width: 400px;">
                        Ainda não te inscreveste em nenhum evento. Explora os eventos disponíveis e participa!
                    </p>
                    <a href="{{ route('egresso.eventos') }}" class="btn btn-primary">
                        <i class="fas fa-calendar me-2"></i> Ver Eventos Disponíveis
                    </a>
                </div>
            </div>

        @endif

    </div>
</div>

@endsection