@extends('layouts.admin')

@section('title', 'Validação de Egressos')

@section('page_title', '✅ Validação de Egressos')
@section('page_subtitle', 'Aprove ou reprove os cadastros pendentes')

@section('content')

@php
    $totalPendentes = $stats['pendentes'] ?? 0;
    $totalAprovados = $stats['aprovados'] ?? 0;
    $totalReprovados = $stats['reprovados'] ?? 0;
    $totalGeral = $totalPendentes + $totalAprovados + $totalReprovados;
    $taxaAprovacao = ($totalAprovados + $totalReprovados) > 0
        ? round(($totalAprovados / ($totalAprovados + $totalReprovados)) * 100, 1)
        : 0;
@endphp


{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.validacao.index', ['status' => 'pendente']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-warning {{ request('status') === 'pendente' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalPendentes }}</div>
                    <div class="stat-label">Pendentes</div>
                    <small class="stat-desc">A aguardar análise</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.validacao.index', ['status' => 'aprovado']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-success {{ request('status') === 'aprovado' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalAprovados }}</div>
                    <div class="stat-label">Aprovados</div>
                    <small class="stat-desc">Cadastros válidos</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <a href="{{ route('admin.validacao.index', ['status' => 'reprovado']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-danger {{ request('status') === 'reprovado' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalReprovados }}</div>
                    <div class="stat-label">Reprovados</div>
                    <small class="stat-desc">Cadastros rejeitados</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $taxaAprovacao }}%</div>
                <div class="stat-label">Taxa de Aprovação</div>
                <small class="stat-desc">Baseado nas decisões</small>
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

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
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
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.validacao.index') }}">

            <div class="row g-3 align-items-end">

                {{-- Pesquisa --}}
                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Pesquisar
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control border-start-0"
                               placeholder="Nome, email ou número de processo..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Todos os Estados</option>
                        <option value="pendente"  {{ request('status') == 'pendente'  ? 'selected' : '' }}>⏳ Pendentes</option>
                        <option value="aprovado"  {{ request('status') == 'aprovado'  ? 'selected' : '' }}>✅ Aprovados</option>
                        <option value="reprovado" {{ request('status') == 'reprovado' ? 'selected' : '' }}>❌ Reprovados</option>
                    </select>
                </div>

                {{-- Ordenação --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Ordenar
                    </label>
                    <select name="order" class="form-select">
                        <option value="asc"  {{ request('order') == 'asc'  ? 'selected' : '' }}>Mais antigos</option>
                        <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Mais recentes</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-lg-2 col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        @if(request()->hasAny(['search', 'status', 'order']))
                            <a href="{{ route('admin.validacao.index') }}"
                               class="btn btn-outline-secondary flex-fill"
                               title="Limpar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </div>

        </form>
    </div>
</div>


{{-- ============================================================
     LISTA DE EGRESSOS PENDENTES
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Egressos Pendentes</h6>
                    <small class="text-muted">
                        {{ $pendentes->total() }} {{ $pendentes->total() === 1 ? 'cadastro' : 'cadastros' }}
                        a aguardar validação
                    </small>
                </div>
            </div>

            @if($pendentes->total() > 0)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    {{ $pendentes->total() }} por validar
                </span>
            @endif
        </div>
    </div>

    <div class="card-body p-0">
        @if($pendentes->count() > 0)

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Egresso</th>
                            <th>Nº Processo</th>
                            <th>Curso</th>
                            <th>Unidade</th>
                            <th>Submetido em</th>
                            <th class="text-end pe-4" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendentes as $egresso)
                            @php
                                $temFoto = !empty($egresso->foto_url);
                                $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
                                    ->filter()
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                    ->take(2)
                                    ->implode('');

                                $processoGerado = str_contains($egresso->numero_processo ?? '', 'PENDENTE_');

                                // Dias desde submissão
                                $dias = $egresso->created_at->diffInDays(now());
                                $urgente = $dias >= 3;
                            @endphp

                            <tr>
                                <td class="ps-4">
                                    <span class="text-muted small">{{ $loop->iteration }}</span>
                                </td>

                                {{-- Egresso --}}
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($temFoto)
                                            <img src="{{ asset($egresso->foto_url) }}"
                                                 alt="{{ $egresso->nome_completo }}"
                                                 class="rounded-circle border flex-shrink-0"
                                                 style="width: 42px; height: 42px; object-fit: cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #f59e0b, #fbbf24); font-size: 0.85rem; display: none;">
                                                {{ $iniciais }}
                                            </div>
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #f59e0b, #fbbf24); font-size: 0.85rem;">
                                                {{ $iniciais }}
                                            </div>
                                        @endif

                                        <div class="min-width-0">
                                            <strong class="d-block text-truncate" style="max-width: 220px;">
                                                {{ $egresso->nome_completo }}
                                            </strong>
                                            @if($egresso->email)
                                                <small class="text-muted d-block text-truncate" style="max-width: 220px;">
                                                    <i class="fas fa-envelope me-1" style="font-size: 0.7rem;"></i>
                                                    {{ $egresso->email }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Nº Processo --}}
                                <td>
                                    <code class="text-dark fw-bold">{{ $egresso->numero_processo }}</code>
                                    @if($processoGerado)
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-1">
                                            <i class="fas fa-robot me-1"></i> Auto
                                        </span>
                                    @endif
                                </td>

                                {{-- Curso --}}
                                <td>
                                    @if($egresso->curso)
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-graduation-cap text-primary" style="font-size: 0.75rem;"></i>
                                            <span class="small">{{ $egresso->curso->nome }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Unidade --}}
                                <td>
                                    @if($egresso->curso && $egresso->curso->unidade)
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $egresso->curso->unidade->sigla }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Data --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">
                                            <i class="far fa-calendar me-1" style="font-size: 0.7rem;"></i>
                                            {{ $egresso->created_at->format('d/m/Y') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                            {{ $egresso->created_at->format('H:i') }}
                                        </small>
                                        @if($urgente)
                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle mt-1"
                                                  style="font-size: 0.6rem;">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }}
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mt-1"
                                                  style="font-size: 0.6rem;">
                                                há {{ $dias }} {{ $dias == 1 ? 'dia' : 'dias' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Ações --}}
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.validacao.show', $egresso->id) }}"
                                       class="btn btn-sm btn-primary"
                                       title="Analisar cadastro">
                                        <i class="fas fa-eye me-1"></i>
                                        Analisar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            @if($pendentes->hasPages())
                <div class="d-flex justify-content-center py-4 border-top">
                    {{ $pendentes->appends(request()->query())->links() }}
                </div>
            @endif

        @else

            {{-- Estado vazio --}}
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h6 class="fw-bold mb-2">🎉 Nenhum egresso pendente</h6>
                <p class="text-muted small mb-0">
                    Todos os cadastros foram validados. Bom trabalho!
                </p>
            </div>

        @endif
    </div>

</div>

@endsection

