@extends('layouts.empresa')

@section('title', 'Candidaturas')
@section('page-title', 'Candidaturas Recebidas')

@section('content')

<div class="page-header">
    <div>
        <h5 class="mb-1">Gestão de Candidaturas</h5>
        <p class="text-muted small">Acompanhe e avalie as candidaturas recebidas</p>
    </div>
</div>

{{-- FILTROS --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos os estados</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="em_analise" {{ request('status') == 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                    <option value="entrevista" {{ request('status') == 'entrevista' ? 'selected' : '' }}>Entrevista</option>
                    <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                    <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Oportunidade</label>
                <select name="oportunidade" class="form-select">
                    <option value="">Todas</option>
                    @foreach($oportunidades ?? [] as $op)
                        <option value="{{ $op->id }}" {{ request('oportunidade') == $op->id ? 'selected' : '' }}>
                            {{ $op->titulo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('empresa.candidaturas.index') }}" class="btn btn-light w-100">
                    <i class="fas fa-times me-1"></i> Limpar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- CARDS DE RESUMO --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $candidaturas->total() }}</h4>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $totalPendentes ?? $candidaturas->where('status', 'pendente')->count() }}</h4>
                <small class="text-muted">Pendentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-info-subtle text-info"><i class="fas fa-comments"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $totalEntrevistas ?? $candidaturas->where('status', 'entrevista')->count() }}</h4>
                <small class="text-muted">Entrevistas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $totalAprovadas ?? $candidaturas->whereIn('status', ['aprovado', 'aceite'])->count() }}</h4>
                <small class="text-muted">Aprovadas</small>
            </div>
        </div>
    </div>
</div>

{{-- LISTA --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        @if($candidaturas->isEmpty())
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3"><i class="fas fa-inbox"></i></div>
                <h5 class="fw-bold mb-2">Nenhuma candidatura recebida</h5>
                <p class="text-muted">As candidaturas aparecerão aqui automaticamente.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Candidato</th>
                            <th>Oportunidade</th>
                            <th>Estado</th>
                            <th>Data</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $coresStatus = [
                                'pendente'   => 'warning',
                                'em_analise' => 'info',
                                'entrevista' => 'primary',
                                'aprovado'   => 'success',
                                'aceite'     => 'success',
                                'rejeitado'  => 'danger',
                            ];
                        @endphp
                        @foreach($candidaturas as $cand)
                            @php $cor = $coresStatus[$cand->status] ?? 'secondary'; @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($cand->egresso && $cand->egresso->foto_url)
                                            <img src="{{ asset($cand->egresso->foto_url) }}"
                                                 alt="Foto de {{ $cand->egresso->nome_completo }}"
                                                 class="rounded-circle"
                                                 style="width: 44px; height: 44px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                                 style="width: 44px; height: 44px;">
                                                {{ strtoupper(substr($cand->egresso->nome_completo ?? 'EG', 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong class="d-block">{{ $cand->egresso->nome_completo ?? 'Egresso' }}</strong>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                {{ $cand->egresso->email ?? '—' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="fw-semibold">{{ $cand->oportunidade->titulo ?? '—' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $cor }}-subtle text-{{ $cor }}-emphasis border border-{{ $cor }}">
                                        {{ ucfirst(str_replace('_', ' ', $cand->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i> {{ $cand->created_at->format('d/m/Y') }}
                                        <br>
                                        <i class="fas fa-clock me-1"></i> {{ $cand->created_at->format('H:i') }}
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('empresa.candidaturas.show', $cand->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $candidaturas->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
