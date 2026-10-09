@extends('layouts.egresso')

@section('title', 'Responder Pesquisa')

@section('page_title', '📝 Responder Pesquisa')
@section('page_subtitle', $pesquisa->titulo)

@section('content')

@php
    $totalPerguntas = $perguntas->count();
    $perguntasObrigatorias = $perguntas->where('obrigatoria', true)->count();
@endphp

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- ============================================================
             HEADER / INTRO
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4 survey-intro-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">

                    <div class="survey-intro-icon">
                        <i class="fas fa-poll"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $pesquisa->titulo }}</h5>

                        @if($pesquisa->descricao)
                            <p class="text-muted mb-3 small">{{ $pesquisa->descricao }}</p>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                <i class="fas fa-list me-1"></i>
                                {{ $totalPerguntas }} {{ $totalPerguntas === 1 ? 'pergunta' : 'perguntas' }}
                            </span>

                            @if($perguntasObrigatorias > 0)
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                    <i class="fas fa-asterisk me-1"></i>
                                    {{ $perguntasObrigatorias }} {{ $perguntasObrigatorias === 1 ? 'obrigatória' : 'obrigatórias' }}
                                </span>
                            @endif

                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <i class="fas fa-clock me-1"></i>
                                {{ $pesquisa->data_fim?->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- ============================================================
             FORMULÁRIO
        ============================================================ --}}
        <form method="POST"
              action="{{ route('egresso.pesquisas.salvar', $pesquisa->id) }}"
              id="formPesquisa">
            @csrf

            @foreach($perguntas as $pergunta)
                @php
                    $opcoes = json_decode($pergunta->opcoes ?? '[]', true) ?? [];

                    $tiposMap = [
                        'texto'             => ['label' => 'Texto Livre',       'icon' => 'align-left',   'color' => 'secondary'],
                        'sim_nao'           => ['label' => 'Sim / Não',         'icon' => 'toggle-on',    'color' => 'success'],
                        'multipla_escolha'  => ['label' => 'Múltipla Escolha',  'icon' => 'dot-circle',   'color' => 'primary'],
                        'selecao_multipla'  => ['label' => 'Seleção Múltipla',  'icon' => 'check-square', 'color' => 'info'],
                        'escala'            => ['label' => 'Escala (1 a 5)',    'icon' => 'star',         'color' => 'warning'],
                    ];
                    $tipoInfo = $tiposMap[$pergunta->tipo] ?? ['label' => $pergunta->tipo, 'icon' => 'question', 'color' => 'secondary'];
                @endphp

                <div class="card border-0 shadow-sm mb-3 question-card">
                    <div class="card-body p-4">

                        {{-- ============================================================
                             HEADER DA PERGUNTA
                        ============================================================ --}}
                        <div class="d-flex align-items-start gap-3 mb-3">

                            {{-- Número --}}
                            <div class="question-number">
                                {{ $loop->iteration }}
                            </div>

                            <div class="flex-grow-1 min-width-0">

                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2 flex-wrap">
                                    <h6 class="question-title mb-0">
                                        {{ $pergunta->pergunta }}
                                        @if($pergunta->obrigatoria)
                                            <span class="text-danger ms-1">*</span>
                                        @endif
                                    </h6>

                                    <span class="badge bg-{{ $tipoInfo['color'] }}-subtle text-{{ $tipoInfo['color'] }}-emphasis border border-{{ $tipoInfo['color'] }}-subtle flex-shrink-0">
                                        <i class="fas fa-{{ $tipoInfo['icon'] }} me-1"></i>
                                        {{ $tipoInfo['label'] }}
                                    </span>
                                </div>

                                @if($pergunta->obrigatoria)
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Resposta obrigatória
                                    </small>
                                @endif

                            </div>

                        </div>

                        {{-- ============================================================
                             CAMPO DE RESPOSTA
                        ============================================================ --}}

                        @if($pergunta->tipo === 'texto')
                            <textarea name="resposta[{{ $pergunta->id }}]"
                                      class="form-control answer-input"
                                      rows="4"
                                      placeholder="Escreva a sua resposta..."
                                      {{ $pergunta->obrigatoria ? 'required' : '' }}></textarea>

                        @elseif($pergunta->tipo === 'sim_nao')
                            <div class="answer-options-row">
                                <label class="answer-option">
                                    <input type="radio"
                                           name="resposta[{{ $pergunta->id }}]"
                                           value="Sim"
                                           {{ $pergunta->obrigatoria ? 'required' : '' }}>
                                    <span class="answer-option-box answer-option-yes">
                                        <i class="fas fa-check-circle"></i>
                                        Sim
                                    </span>
                                </label>

                                <label class="answer-option">
                                    <input type="radio"
                                           name="resposta[{{ $pergunta->id }}]"
                                           value="Não">
                                    <span class="answer-option-box answer-option-no">
                                        <i class="fas fa-times-circle"></i>
                                        Não
                                    </span>
                                </label>
                            </div>

                        @elseif($pergunta->tipo === 'multipla_escolha')
                            <div class="answer-list">
                                @foreach($opcoes as $i => $opcao)
                                    <label class="answer-list-item">
                                        <input type="radio"
                                               name="resposta[{{ $pergunta->id }}]"
                                               value="{{ $opcao }}"
                                               {{ $pergunta->obrigatoria ? 'required' : '' }}>
                                        <span class="answer-list-marker"></span>
                                        <span class="answer-list-text">{{ $opcao }}</span>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($pergunta->tipo === 'selecao_multipla')
                            <div class="answer-list">
                                @foreach($opcoes as $i => $opcao)
                                    <label class="answer-list-item">
                                        <input type="checkbox"
                                               name="resposta[{{ $pergunta->id }}][]"
                                               value="{{ $opcao }}">
                                        <span class="answer-list-marker answer-list-marker-square"></span>
                                        <span class="answer-list-text">{{ $opcao }}</span>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($pergunta->tipo === 'escala')
                            <div class="answer-scale">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="answer-scale-item">
                                        <input type="radio"
                                               name="resposta[{{ $pergunta->id }}]"
                                               value="{{ $i }}"
                                               {{ $pergunta->obrigatoria ? 'required' : '' }}>
                                        <span class="answer-scale-box">
                                            <strong>{{ $i }}</strong>
                                        </span>
                                    </label>
                                @endfor
                            </div>

                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Tipo de pergunta não suportado: <strong>{{ $pergunta->tipo }}</strong>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach


            {{-- ============================================================
                 AÇÕES
            ============================================================ --}}
            <div class="card border-0 shadow-sm sticky-actions">
                <div class="card-body p-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                        <a href="{{ route('egresso.pesquisas.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-paper-plane me-1"></i>
                            Enviar Respostas
                        </button>

                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

