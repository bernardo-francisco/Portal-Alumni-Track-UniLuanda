@extends('layouts.empresa')

@section('title', 'Minhas Oportunidades')
@section('page-title', 'Minhas Oportunidades')

@section('content')

<div class="page-header">
    <div>
        <h5 class="mb-1">Gestão de Oportunidades</h5>
        <p class="text-muted small">Publique e gira as suas oportunidades</p>
    </div>
    <a href="{{ route('empresa.oportunidades.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i> Nova Oportunidade
    </a>
</div>

{{-- CARDS DE RESUMO --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-primary-subtle text-primary"><i class="fas fa-briefcase"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $oportunidades->total() }}</h4>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $oportunidades->where('is_active', true)->count() }}</h4>
                <small class="text-muted">Ativas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-warning-subtle text-warning"><i class="fas fa-pause-circle"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $oportunidades->where('is_active', false)->count() }}</h4>
                <small class="text-muted">Inativas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-icon bg-info-subtle text-info"><i class="fas fa-users"></i></div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $totalCandidaturas ?? 0 }}</h4>
                <small class="text-muted">Candidaturas</small>
            </div>
        </div>
    </div>
</div>

{{-- LISTA --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">

        @if($oportunidades->isEmpty())
            <div class="text-center py-5">
                <div class="empty-state-icon mb-3"><i class="fas fa-briefcase"></i></div>
                <h5 class="fw-bold mb-2">Nenhuma oportunidade publicada</h5>
                <p class="text-muted mb-4">Comece por criar a sua primeira oportunidade</p>
                <a href="{{ route('empresa.oportunidades.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> Criar Oportunidade
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Oportunidade</th>
                            <th>Tipo</th>
                            <th>Data Limite</th>
                            <th>Estado</th>
                            <th>Candidaturas</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $icones = [
                                'emprego' => ['icon' => 'briefcase',     'cor' => 'primary'],
                                'estagio' => ['icon' => 'user-graduate', 'cor' => 'info'],
                                'bolsa'   => ['icon' => 'award',         'cor' => 'success'],
                                'curso'   => ['icon' => 'book',          'cor' => 'warning'],
                                'evento'  => ['icon' => 'calendar',      'cor' => 'purple'],
                            ];
                        @endphp
                        @foreach($oportunidades as $op)
                            @php
                                $ic = $icones[$op->tipo] ?? ['icon' => 'briefcase', 'cor' => 'secondary'];
                                $totalCands = $op->candidaturas_count ?? \App\Models\Candidatura::where('oportunidade_id', $op->id)->count();
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="oportunidade-icon">
                                            <i class="fas fa-{{ $ic['icon'] }} text-{{ $ic['cor'] }}"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $op->titulo }}</strong>
                                            <small class="text-muted">{{ Str::limit($op->descricao, 60) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $ic['cor'] }}-subtle text-{{ $ic['cor'] }}-emphasis border border-{{ $ic['cor'] }}">
                                        {{ ucfirst($op->tipo) }}
                                    </span>
                                </td>
                                <td>
                                    @if($op->data_limite)
                                        @php
                                            $data = \Carbon\Carbon::parse($op->data_limite);
                                            $expirado = $data->isPast();
                                        @endphp
                                        <small class="{{ $expirado ? 'text-danger' : 'text-muted' }}">
                                            <i class="fas fa-calendar me-1"></i> {{ $data->format('d/m/Y') }}
                                            @if($expirado)
                                                <br><span class="badge bg-danger-subtle text-danger mt-1">Expirado</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($op->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Ativa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> Inativa
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('empresa.candidaturas.index', ['oportunidade' => $op->id]) }}"
                                       class="badge bg-primary-subtle text-primary text-decoration-none px-3 py-2">
                                        <i class="fas fa-users me-1"></i> {{ $totalCands }}
                                    </a>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('empresa.oportunidades.edit', $op->id) }}"
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('empresa.oportunidades.destroy', $op->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Remover esta oportunidade?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $oportunidades->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
