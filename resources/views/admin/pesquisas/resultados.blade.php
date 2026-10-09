@extends('layouts.admin')

@section('title', 'Resultados da Pesquisa')

@section('page_title', '📈 Resultados da Pesquisa')
@section('page_subtitle', $pesquisa->titulo)

@section('content')

@php
    $totalRespostas      = $pesquisa->respostas_count ?? 0;
    $totalPerguntas      = $perguntas->count();
    $totalParticipantes  = $totalParticipantes ?? 0;

    // Taxa média de resposta (respostas / (perguntas * participantes))
    $taxaResposta = ($totalPerguntas > 0 && $totalParticipantes > 0)
        ? round(($totalRespostas / ($totalPerguntas * $totalParticipantes)) * 100)
        : 0;
@endphp


{{-- ============================================================
     CABEÇALHO — STAT CARDS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-poll"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalPerguntas }}</div>
                <div class="stat-label">Perguntas</div>
                <small class="stat-desc">Nesta pesquisa</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalParticipantes }}</div>
                <div class="stat-label">Participantes</div>
                <small class="stat-desc">Egressos que responderam</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalRespostas }}</div>
                <div class="stat-label">Respostas</div>
                <small class="stat-desc">Total recolhido</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $taxaResposta }}%</div>
                <div class="stat-label">Taxa de resposta</div>
                <small class="stat-desc">Média por participante</small>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     AÇÕES
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.pesquisas.perguntas', $pesquisa->id) }}"
       class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar às Perguntas
    </a>

    <a href="{{ route('admin.pesquisas.index') }}"
       class="btn btn-outline-primary">
        <i class="fas fa-list me-1"></i> Todas as Pesquisas
    </a>
</div>


{{-- ============================================================
     RESULTADOS POR PERGUNTA
============================================================ --}}
@if($perguntas->count() > 0)

    @foreach($perguntas as $pergunta)

        @php
            $respostas              = $pergunta->respostas;
            $totalRespostasPergunta = $respostas->count();
            $dadosPergunta          = $dados[$pergunta->id] ?? collect();

            // Cores por tipo
            $corTipo = match($pergunta->tipo) {
                'sim_nao'            => ['#10b981', '#34d399'],
                'multipla_escolha'   => ['#6366f1', '#8b5cf6'],
                'selecao_multipla'   => ['#06b6d4', '#22d3ee'],
                'escala'             => ['#f59e0b', '#fbbf24'],
                'texto'              => ['#ef4444', '#f87171'],
                default              => ['#64748b', '#94a3b8'],
            };

            $tipoLabel = match($pergunta->tipo) {
                'sim_nao'          => 'Sim / Não',
                'multipla_escolha' => 'Múltipla Escolha',
                'selecao_multipla' => 'Seleção Múltipla',
                'escala'           => 'Escala (1–5)',
                'texto'            => 'Texto Livre',
                default            => ucfirst($pergunta->tipo),
            };

            $tipoDesc = match($pergunta->tipo) {
                'sim_nao'          => 'Respostas binárias (Sim ou Não).',
                'multipla_escolha' => 'Cada participante escolheu 1 opção.',
                'selecao_multipla' => 'Cada participante podia escolher várias.',
                'escala'           => 'Nível de concordância/satisfação de 1 a 5.',
                'texto'            => 'Respostas escritas livremente.',
                default            => '',
            };

            // Para gráficos de escolha, calcular total
            $somaEscolhas = $dadosPergunta->sum('total') ?: 0;
        @endphp

        <div class="card border-0 shadow-sm mb-4 result-question-card"
             style="--cor: {{ $corTipo[0] }}; --cor2: {{ $corTipo[1] }};">

            {{-- HEADER DA PERGUNTA --}}
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">

                        <div class="question-number">
                            {{ $loop->iteration }}
                        </div>

                        <div>
                            <h6 class="mb-1 fw-bold">{{ $pergunta->pergunta }}</h6>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <small class="text-muted">
                                    <i class="fas fa-comment me-1"></i>
                                    {{ $totalRespostasPergunta }}
                                    {{ $totalRespostasPergunta == 1 ? 'resposta' : 'respostas' }}
                                </small>
                                @if($pergunta->obrigatoria)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        Obrigatória
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <span class="tipo-badge" style="--cor: {{ $corTipo[0] }}; --cor2: {{ $corTipo[1] }};">
                        <i class="fas fa-tag me-1"></i> {{ $tipoLabel }}
                    </span>
                </div>

                {{-- Descrição do tipo --}}
                <div class="tipo-desc">
                    <i class="fas fa-info-circle me-1"></i> {{ $tipoDesc }}
                </div>
            </div>

            <div class="card-body p-4">

                @if($totalRespostasPergunta > 0)

                    {{-- ============================================
                         TEXTO LIVRE
                    ============================================ --}}
                    @if($pergunta->tipo === 'texto')

                        <div class="text-responses">
                            @foreach($dadosPergunta as $resposta)
                                <div class="text-response-item">
                                    <div class="text-response-icon">
                                        <i class="fas fa-quote-left"></i>
                                    </div>
                                    <div class="text-response-content">
                                        {{ $resposta->resposta }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else

                        {{-- ============================================
                             GRÁFICO + LEGENDA LADO A LADO
                        ============================================ --}}
                        <div class="row g-4 align-items-center">

                            {{-- BARRAS --}}
                            <div class="col-lg-7">
                                <div class="chart-wrapper">
                                    <div class="chart-title">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Distribuição das respostas
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="chart_bar_{{ $pergunta->id }}"></canvas>
                                    </div>
                                </div>
                            </div>

                            {{-- DOUGHNUT + LEGENDA --}}
                            <div class="col-lg-5">
                                <div class="chart-wrapper">
                                    <div class="chart-title">
                                        <i class="fas fa-chart-pie me-1"></i>
                                        Percentagem do total
                                    </div>
                                    <div class="chart-doughnut-container">
                                        <canvas id="chart_doughnut_{{ $pergunta->id }}"></canvas>
                                    </div>
                                </div>

                                {{-- Legenda explicativa com % --}}
                                <div class="legend-list mt-3">
                                    @foreach($dadosPergunta as $i => $item)
                                        @php
                                            $pct = $somaEscolhas > 0
                                                ? round(($item->total / $somaEscolhas) * 100)
                                                : 0;
                                        @endphp
                                        <div class="legend-item-pro">
                                            <span class="legend-dot"
                                                  style="background: {{ ['#6366f1','#10b981','#06b6d4','#f59e0b','#ef4444','#8b5cf6','#0ea5e9','#f43f5e','#84cc16','#a855f7'][$i % 10] }};"></span>
                                            <span class="legend-label-pro">{{ $item->resposta }}</span>
                                            <span class="legend-value-pro">
                                                {{ $item->total }}
                                                <small>({{ $pct }}%)</small>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                    @endif

                @else

                    <div class="text-center py-4">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-comment-slash"></i>
                        </div>
                        <p class="text-muted mb-0">Nenhuma resposta para esta pergunta.</p>
                    </div>

                @endif
            </div>
        </div>

    @endforeach

@else

    {{-- SEM PERGUNTAS --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h6 class="fw-bold mb-1">Nenhuma pergunta criada</h6>
            <p class="text-muted small mb-4">
                Ainda não existem perguntas associadas a esta pesquisa.
            </p>
            <a href="{{ route('admin.pesquisas.perguntas', $pesquisa->id) }}"
               class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Adicionar Perguntas
            </a>
        </div>
    </div>

@endif

@endsection

