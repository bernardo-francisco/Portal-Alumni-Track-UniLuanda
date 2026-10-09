@extends('layouts.admin')

@section('title', 'Candidaturas')

@section('page_title', '📋 Candidaturas')
@section('page_subtitle', 'Gerir candidaturas a oportunidades')

@section('content')

@php
    $status = request()->get('status', 'todos');
@endphp

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'pendente']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'pendente' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['pendente'] }}</div>
                    <div class="stat-label">Pendentes</div>
                </div>
                <i class="fas fa-clock stat-bg-icon"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'em_analise']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'em_analise' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #0ea5e9, #38bdf8);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['em_analise'] }}</div>
                    <div class="stat-label">Em Análise</div>
                </div>
                <i class="fas fa-search stat-bg-icon"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'entrevista']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'entrevista' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #a855f7, #c084fc);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['entrevista'] }}</div>
                    <div class="stat-label">Entrevista</div>
                </div>
                <i class="fas fa-user-tie stat-bg-icon"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'aprovado']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'aprovado' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #16a34a, #4ade80);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['aprovado'] }}</div>
                    <div class="stat-label">Aprovadas</div>
                </div>
                <i class="fas fa-check-circle stat-bg-icon"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'rejeitado']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'rejeitado' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #dc2626, #f87171);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['rejeitado'] }}</div>
                    <div class="stat-label">Rejeitadas</div>
                </div>
                <i class="fas fa-times-circle stat-bg-icon"></i>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-6">
        <a href="{{ route('admin.candidaturas.index', ['status' => 'todos']) }}" class="text-decoration-none">
            <div class="stat-card {{ $status === 'todos' ? 'stat-active' : '' }}"
                 style="background: linear-gradient(135deg, #1a56db, #3b82f6);">
                <div class="stat-card-body">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total</div>
                </div>
                <i class="fas fa-list stat-bg-icon"></i>
            </div>
        </a>
    </div>

</div>


{{-- ============================================================
     LISTA DE CANDIDATURAS
============================================================ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Candidaturas Recebidas</h6>
                    <small class="text-muted">
                        {{ $candidaturas->total() }} {{ $candidaturas->total() === 1 ? 'candidatura' : 'candidaturas' }}
                    </small>
                </div>
            </div>

            {{-- ✅ BOTÕES DE EXPORTAÇÃO --}}
            <div class="d-flex gap-2">
                <a href="{{ route('admin.candidaturas.exportar.csv', request()->query()) }}"
                   class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-csv me-1"></i> CSV
                </a>

                <a href="{{ route('admin.candidaturas.exportar.pdf', request()->query()) }}"
                   class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($candidaturas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Egresso</th>
                            <th>Oportunidade</th>
                            <th>Data</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidaturas as $cand)
                            @php
                                $egresso = $cand->egresso;
                                $nomeEgresso = $egresso?->nome_completo ?? 'Egresso';
                                $iniciais = collect(explode(' ', trim($nomeEgresso)))
                                    ->filter()
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                    ->take(2)
                                    ->implode('');

                                $cores = [
                                    'pendente'   => ['bg' => 'warning', 'label' => 'Pendente',   'icon' => 'clock'],
                                    'em_analise' => ['bg' => 'info',    'label' => 'Em Análise', 'icon' => 'search'],
                                    'entrevista' => ['bg' => 'primary', 'label' => 'Entrevista', 'icon' => 'user-tie'],
                                    'aprovado'   => ['bg' => 'success', 'label' => 'Aprovado',   'icon' => 'check-circle'],
                                    'rejeitado'  => ['bg' => 'danger',  'label' => 'Rejeitado',  'icon' => 'times-circle'],
                                ];
                                $cor = $cores[$cand->status] ?? ['bg' => 'secondary', 'label' => $cand->status, 'icon' => 'circle'];
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($egresso?->foto_url)
                                            <img src="{{ asset($egresso->foto_url) }}"
                                                 class="rounded-circle border"
                                                 style="width: 42px; height: 42px; object-fit: cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="avatar-fallback" style="display: none;">{{ $iniciais }}</div>
                                        @else
                                            <div class="avatar-fallback">{{ $iniciais }}</div>
                                        @endif
                                        <div class="min-width-0">
                                            <strong class="d-block text-truncate">{{ $nomeEgresso }}</strong>
                                            <small class="text-muted text-truncate d-block">
                                                {{ $egresso?->email }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="d-block">{{ $cand->oportunidade->titulo ?? 'N/A' }}</strong>
                                    <small class="text-muted">
                                        {{ $cand->oportunidade->empresa ?? '' }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-calendar me-1"></i>
                                        {{ $cand->created_at->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cor['bg'] }}-subtle text-{{ $cor['bg'] }}-emphasis border border-{{ $cor['bg'] }}-subtle">
                                        <i class="fas fa-{{ $cor['icon'] }} me-1"></i>
                                        {{ $cor['label'] }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.candidaturas.show', $cand->id) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($candidaturas->hasPages())
                <div class="d-flex justify-content-center py-3 border-top">
                    {{ $candidaturas->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <div class="empty-icon mb-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h6 class="fw-bold mb-1">Sem candidaturas</h6>
                <p class="text-muted small mb-0">
                    As candidaturas dos egressos aparecerão aqui.
                </p>
            </div>
        @endif
    </div>
</div>

@endsection


