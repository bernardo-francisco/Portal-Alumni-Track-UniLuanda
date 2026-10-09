@extends('layouts.egresso')

@section('title', 'Minhas Candidaturas')

@section('page_title', '📄 Minhas Candidaturas')
@section('page_subtitle', 'Acompanhe o estado das suas candidaturas')

@section('content')

@php
    $totalCandidaturas = isset($candidaturas) ? $candidaturas->count() : 0;

    $totalPendentes = 0;
    $totalAprovadas = 0;
    $totalRecusadas = 0;
    $totalEntrevistas = 0;

    if (isset($candidaturas)) {
        foreach ($candidaturas as $c) {
            $s = strtolower($c->status ?? 'pendente');
            if ($s === 'pendente')  $totalPendentes++;
            if ($s === 'aprovado')  $totalAprovadas++;
            if ($s === 'recusado')  $totalRecusadas++;
            if ($s === 'entrevista') $totalEntrevistas++;
        }
    }
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total de Candidaturas</div>
                <div class="stat-card-value">{{ $totalCandidaturas }}</div>
                <div class="stat-card-desc"><i class="fas fa-database"></i> Candidaturas enviadas</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-label">Pendentes</div>
                <div class="stat-card-value">{{ $totalPendentes }}</div>
                <div class="stat-card-desc"><i class="fas fa-hourglass-half"></i> A aguardar resposta</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-card-icon"><i class="fas fa-user-tie"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-label">Entrevistas</div>
                <div class="stat-card-value">{{ $totalEntrevistas }}</div>
                <div class="stat-card-desc"><i class="fas fa-comments"></i> Em fase de entrevista</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-label">Aprovadas</div>
                <div class="stat-card-value">{{ $totalAprovadas }}</div>
                <div class="stat-card-desc"><i class="fas fa-thumbs-up"></i> Parabéns!</div>
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

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- ============================================================
     AÇÕES
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-list text-primary me-2"></i>
            Histórico de Candidaturas
        </h6>
        <small class="text-muted">
            {{ $totalCandidaturas }} {{ $totalCandidaturas === 1 ? 'candidatura registada' : 'candidaturas registadas' }}
        </small>
    </div>

    <a href="{{ route('egresso.oportunidades') }}" class="btn btn-primary">
        <i class="fas fa-briefcase me-1"></i> Ver Oportunidades
    </a>
</div>


{{-- ============================================================
     LISTA DE CANDIDATURAS
============================================================ --}}
@if($totalCandidaturas > 0)

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Oportunidade</th>
                            <th>Empresa</th>
                            <th>Data da Candidatura</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidaturas as $candidatura)
                            @php
                                $oportunidade = $candidatura->oportunidade;
                                $status = strtolower($candidatura->status ?? 'pendente');

                                $statusMap = [
                                    'pendente'   => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
                                    'aprovado'   => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Aprovado'],
                                    'recusado'   => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Recusado'],
                                    'rejeitado'  => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Rejeitado'],
                                    'entrevista' => ['color' => 'info',    'icon' => 'user-tie',     'label' => 'Entrevista'],
                                    'em_analise' => ['color' => 'primary', 'icon' => 'search',       'label' => 'Em Análise'],
                                ];
                                $statusInfo = $statusMap[$status] ?? ['color' => 'secondary', 'icon' => 'info-circle', 'label' => ucfirst($status)];

                                $tipoMap = [
                                    'emprego' => ['color' => 'primary',   'icon' => 'briefcase'],
                                    'estagio' => ['color' => 'info',      'icon' => 'user-graduate'],
                                    'estágio' => ['color' => 'info',      'icon' => 'user-graduate'],
                                    'bolsa'   => ['color' => 'success',   'icon' => 'award'],
                                    'curso'   => ['color' => 'warning',   'icon' => 'book'],
                                    'evento'  => ['color' => 'secondary', 'icon' => 'calendar'],
                                ];
                                $tipoSlug = strtolower($oportunidade->tipo ?? '');
                                $tipoInfo = $tipoMap[$tipoSlug] ?? ['color' => 'secondary', 'icon' => 'briefcase'];
                            @endphp

                            <tr>
                                {{-- Oportunidade --}}
                                <td class="ps-4">
                                    @if($oportunidade)
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="oportunidade-icon bg-{{ $tipoInfo['color'] }} bg-opacity-10 text-{{ $tipoInfo['color'] }}">
                                                <i class="fas fa-{{ $tipoInfo['icon'] }}"></i>
                                            </div>
                                            <div class="min-width-0">
                                                <strong class="d-block text-truncate" style="max-width: 300px;">
                                                    {{ $oportunidade->titulo }}
                                                </strong>
                                                @if($oportunidade->tipo)
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-tag me-1" style="font-size: 0.65rem;"></i>
                                                        {{ ucfirst($oportunidade->tipo) }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Oportunidade removida
                                        </span>
                                    @endif
                                </td>

                                {{-- Empresa --}}
                                <td>
                                    @if($oportunidade && $oportunidade->empresa)
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-building text-primary" style="font-size: 0.75rem;"></i>
                                            <span class="small">{{ $oportunidade->empresa }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Data --}}
                                <td>
                                    @if($candidatura->created_at)
                                        <div class="d-flex flex-column">
                                            <strong class="small">{{ $candidatura->created_at->format('d/m/Y') }}</strong>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                                {{ $candidatura->created_at->format('H:i') }}
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusInfo['color'] }}-subtle text-{{ $statusInfo['color'] }}-emphasis border border-{{ $statusInfo['color'] }}-subtle">
                                        <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>

                                {{-- Ações --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">

                                        {{-- ✅ NOVO — Ver CV --}}
                                        @if($candidatura->cv_anexo)
                                            <a href="{{ asset('storage/' . $candidatura->cv_anexo) }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Ver CV que enviei">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @else
                                            <span class="btn btn-sm btn-outline-secondary disabled"
                                                  title="Sem CV anexado">
                                                <i class="fas fa-file-slash"></i>
                                            </span>
                                        @endif

                                        <a href="{{ route('egresso.candidaturas.show', $candidatura->id) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Ver detalhes da candidatura">
                                            <i class="fas fa-eye me-1"></i> Ver
                                        </a>

                                        @if($status === 'entrevista' && $candidatura->data_entrevista)
                                            <a href="{{ route('egresso.candidaturas.entrevista.ics', $candidatura->id) }}"
                                               class="btn btn-sm btn-outline-info"
                                               title="Adicionar entrevista ao calendário">
                                                <i class="fas fa-calendar-plus"></i>
                                            </a>
                                        @endif

                                        @if($status === 'pendente')
                                            <form action="{{ route('egresso.oportunidades.cancelar', ['candidatura' => $candidatura->id]) }}"
                                                  method="POST"
                                                  class="form-cancelar-candidatura d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Cancelar candidatura">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-4"><i class="fas fa-file-circle-xmark"></i></div>
            <h5 class="fw-bold mb-2">Nenhuma candidatura encontrada</h5>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                Você ainda não se candidatou a nenhuma oportunidade.
                Explore as vagas disponíveis e candidate-se!
            </p>
            <a href="{{ route('egresso.oportunidades') }}" class="btn btn-primary">
                <i class="fas fa-search me-1"></i> Procurar Oportunidades
            </a>
        </div>
    </div>

@endif

@endsection