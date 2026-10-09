@extends('layouts.egresso')

@section('title', 'Pesquisas')

@section('page_title', '📊 Pesquisas')
@section('page_subtitle', 'Participe das pesquisas da Universidade')

@section('content')

@php
    $totalPesquisas = isset($pesquisas) ? $pesquisas->count() : 0;
    $agora = \Carbon\Carbon::now();

    // Estatísticas
    $pesquisasAtivas      = 0;
    $pesquisasRespondidas = 0;
    $pesquisasPendentes   = 0;
    $pesquisasEncerradas  = 0;

    if (isset($pesquisas)) {
        foreach ($pesquisas as $p) {
            $inicio = \Carbon\Carbon::parse($p->data_inicio);
            $fim    = \Carbon\Carbon::parse($p->data_fim);

            // ✅ Ativa: já começou E ainda não terminou
            $ativa = $agora->greaterThanOrEqualTo($inicio)
                  && $agora->lessThanOrEqualTo($fim);

            $jaRespondeu = isset($respostas) && in_array($p->id, $respostas);

            if ($ativa) {
                $pesquisasAtivas++;
                if ($jaRespondeu) {
                    $pesquisasRespondidas++;
                } else {
                    $pesquisasPendentes++;
                }
            } else {
                $pesquisasEncerradas++;
            }
        }
    }
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-poll"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Pesquisas Ativas</div>
                <div class="stat-card-value">{{ $pesquisasAtivas }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-circle" style="font-size: 0.4rem; color: #16a34a;"></i>
                    Disponíveis para responder
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Pendentes</div>
                <div class="stat-card-value">{{ $pesquisasPendentes }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-exclamation-circle"></i>
                    A aguardar a sua resposta
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Respondidas</div>
                <div class="stat-card-value">{{ $pesquisasRespondidas }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-thumbs-up"></i>
                    Obrigado pela participação
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-icon">
                <i class="fas fa-archive"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Encerradas</div>
                <div class="stat-card-value">{{ $pesquisasEncerradas }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-lock"></i>
                    Prazo expirado
                </div>
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
     LISTA DE PESQUISAS
============================================================ --}}
@if($totalPesquisas > 0)

    <div class="row g-4">

        @foreach($pesquisas as $pesquisa)
            @php
                // ✅ Comparação robusta com Carbon
                $agora  = \Carbon\Carbon::now();
                $inicio = \Carbon\Carbon::parse($pesquisa->data_inicio);
                $fim    = \Carbon\Carbon::parse($pesquisa->data_fim);

                $ativa = $agora->greaterThanOrEqualTo($inicio)
                      && $agora->lessThanOrEqualTo($fim);

                $jaRespondeu = isset($respostas) && in_array($pesquisa->id, $respostas);

                // Dias restantes (arredondado para baixo)
                $diasRestantes = null;
                if ($ativa) {
                    $diasRestantes = (int) $agora->diffInDays($fim, false);
                    if ($diasRestantes < 0) $diasRestantes = 0;
                }

                // Estado visual
                if ($jaRespondeu) {
                    $estado = 'respondida';
                } elseif ($ativa) {
                    $estado = 'ativa';
                } else {
                    $estado = 'encerrada';
                }
            @endphp

            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 survey-card survey-card-{{ $estado }}">

                    {{-- HEADER COM ÍCONE --}}
                    <div class="survey-header">
                        <div class="survey-icon">
                            <i class="fas fa-poll"></i>
                        </div>

                        <div class="survey-badges">
                            @if($jaRespondeu)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Respondida
                                </span>
                            @elseif($ativa)
                                <span class="badge bg-success">
                                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Ativa
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-lock me-1"></i> Encerrada
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body p-4">

                        <h6 class="survey-title">{{ $pesquisa->titulo }}</h6>

                        <p class="survey-description">
                            {{ Str::limit($pesquisa->descricao, 120) ?? 'Sem descrição.' }}
                        </p>

                        <div class="survey-period">
                            <div class="survey-period-item">
                                <div class="survey-period-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Início</small>
                                    <strong class="small">
                                        {{ \Carbon\Carbon::parse($pesquisa->data_inicio)->format('d/m/Y') }}
                                    </strong>
                                </div>
                            </div>

                            <div class="survey-period-item">
                                <div class="survey-period-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-stop"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">Término</small>
                                    <strong class="small">
                                        {{ \Carbon\Carbon::parse($pesquisa->data_fim)->format('d/m/Y') }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {{-- Badge de prazo --}}
                        @if($ativa && !$jaRespondeu && $diasRestantes !== null)
                            <div class="mt-3">
                                @if($diasRestantes == 0)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Termina hoje!
                                    </span>
                                @elseif($diasRestantes <= 3)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        {{ $diasRestantes }} {{ $diasRestantes == 1 ? 'dia' : 'dias' }} restantes
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $diasRestantes }} dias restantes
                                    </span>
                                @endif
                            </div>
                        @endif

                    </div>

                    {{-- FOOTER COM AÇÃO --}}
                    <div class="card-footer bg-white border-top p-3">
                        @if($jaRespondeu)
                            <button class="btn btn-success-subtle text-success-emphasis border border-success-subtle w-100"
                                    disabled>
                                <i class="fas fa-check-circle me-1"></i> Já Respondida
                            </button>
                        @elseif($ativa)
                            <a href="{{ route('egresso.pesquisas.responder', $pesquisa->id) }}"
                               class="btn btn-primary w-100">
                                <i class="fas fa-pen me-1"></i> Responder Agora
                            </a>
                        @else
                            <button class="btn btn-outline-secondary w-100" disabled>
                                <i class="fas fa-lock me-1"></i> Encerrada
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        @endforeach

    </div>

@else

    {{-- ESTADO VAZIO --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-poll"></i>
            </div>
            <h5 class="fw-bold mb-2">Nenhuma pesquisa disponível</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                De momento não há pesquisas disponíveis. Volte em breve para participar das próximas.
            </p>
        </div>
    </div>

@endif

@endsection

