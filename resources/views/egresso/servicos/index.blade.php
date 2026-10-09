@extends('layouts.egresso')

@section('title', 'Meus Pedidos')

@section('page_title', '📋 Meus Pedidos')
@section('page_subtitle', 'Acompanhe os seus pedidos de serviços')

@section('content')

@php
    $totalPedidos   = $total_pedidos ?? 0;
    $totalPendentes = $pendentes ?? 0;
    $totalAndamento = $andamento ?? 0;
    $totalAtendidos = $atendidos ?? 0;
    $totalCancelados= $cancelados ?? 0;
@endphp

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('egresso.servicos.meus-pedidos', ['status' => 'todos']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-primary {{ $statusAtual === 'todos' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap"><i class="fas fa-list"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalPedidos }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('egresso.servicos.meus-pedidos', ['status' => 'pendente']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-warning {{ $statusAtual === 'pendente' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap"><i class="fas fa-clock"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalPendentes }}</div>
                    <div class="stat-label">Pendentes</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('egresso.servicos.meus-pedidos', ['status' => 'andamento']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-info {{ $statusAtual === 'andamento' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap"><i class="fas fa-spinner"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalAndamento }}</div>
                    <div class="stat-label">Em Andamento</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('egresso.servicos.meus-pedidos', ['status' => 'atendido']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-success {{ $statusAtual === 'atendido' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap"><i class="fas fa-check-circle"></i></div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalAtendidos }}</div>
                    <div class="stat-label">Atendidos</div>
                </div>
            </div>
        </a>
    </div>

</div>


{{-- ============================================================
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-filter"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">Filtros</h6>
                <small class="text-muted">Encontre o pedido que procura</small>
            </div>
        </div>

        <form action="{{ route('egresso.servicos.meus-pedidos') }}" method="GET">
            <div class="row g-3 align-items-end">

                {{-- Pesquisa --}}
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Pesquisar
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="q"
                               class="form-control border-start-0"
                               placeholder="Serviço ou descrição..."
                               value="{{ request('q') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Estado
                    </label>
                    <select name="status" class="form-select">
                        <option value="todos"     {{ $statusAtual === 'todos'     ? 'selected' : '' }}>Todos</option>
                        <option value="pendente"  {{ $statusAtual === 'pendente'  ? 'selected' : '' }}>Pendente</option>
                        <option value="andamento" {{ $statusAtual === 'andamento' ? 'selected' : '' }}>Em Andamento</option>
                        <option value="atendido"  {{ $statusAtual === 'atendido'  ? 'selected' : '' }}>Atendido</option>
                        <option value="cancelado" {{ $statusAtual === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                {{-- Ordenação --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Ordenar
                    </label>
                    <select name="ordenacao" class="form-select">
                        <option value="recentes" {{ request('ordenacao') === 'recentes' ? 'selected' : '' }}>Recentes</option>
                        <option value="antigos" {{ request('ordenacao') === 'antigos' ? 'selected' : '' }}>Antigos</option>
                        <option value="pendentes_primeiro" {{ request('ordenacao') === 'pendentes_primeiro' ? 'selected' : '' }}>Pendentes primeiro</option>
                        <option value="atendidos_recentes" {{ request('ordenacao') === 'atendidos_recentes' ? 'selected' : '' }}>Atendidos recentes</option>
                    </select>
                </div>

                {{-- Data início --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Data início
                    </label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>

                {{-- Data fim --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Data fim
                    </label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>

                {{-- Botões --}}
                <div class="col-lg-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>

                        @if($temFiltros ?? false)
                            <a href="{{ route('egresso.servicos.meus-pedidos') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Limpar filtros
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>


{{-- ============================================================
     AÇÕES
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-list text-primary me-2"></i>
            Histórico de Pedidos
        </h6>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('egresso.servicos.solicitar') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Novo Pedido
        </a>
    </div>
</div>


{{-- ============================================================
     LISTA DE PEDIDOS
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Pedidos</h6>
                    <small class="text-muted">
                        {{ $pedidos->total() ?? 0 }} {{ ($pedidos->total() ?? 0) === 1 ? 'pedido' : 'pedidos' }}
                        @if($statusAtual !== 'todos')
                            · <span class="text-primary">{{ ucfirst($statusAtual) }}</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if(isset($pedidos) && $pedidos->count() > 0)

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>Serviço</th>
                            <th>Data</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $pedido)
                            @php
                                $statusInfo = [
                                    'pendente'  => ['color' => 'warning', 'icon' => 'clock',        'label' => 'Pendente'],
                                    'andamento' => ['color' => 'info',    'icon' => 'spinner',      'label' => 'Em Andamento'],
                                    'atendido'  => ['color' => 'success', 'icon' => 'check-circle', 'label' => 'Atendido'],
                                    'cancelado' => ['color' => 'danger',  'icon' => 'times-circle', 'label' => 'Cancelado'],
                                ][$pedido->status] ?? ['color' => 'secondary', 'icon' => 'circle', 'label' => ucfirst($pedido->status)];
                            @endphp

                            <tr>
                                <td class="ps-4">
                                    <div class="order-number">{{ $loop->iteration }}</div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="service-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="min-width-0">
                                            <strong class="d-block">{{ $pedido->servico }}</strong>
                                            @if($pedido->descricao)
                                                <small class="text-muted d-block" style="max-width: 400px;">
                                                    {{ Str::limit($pedido->descricao, 70) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">
                                            <i class="far fa-calendar me-1" style="font-size: 0.7rem;"></i>
                                            {{ $pedido->created_at->format('d/m/Y') }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1" style="font-size: 0.7rem;"></i>
                                            {{ $pedido->created_at->format('H:i') }}
                                        </small>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-{{ $statusInfo['color'] }}-subtle text-{{ $statusInfo['color'] }}-emphasis border border-{{ $statusInfo['color'] }}-subtle">
                                        <i class="fas fa-{{ $statusInfo['icon'] }} me-1"></i>
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>

                                <td class="text-end pe-4">
                                    @if($pedido->status === 'pendente')
                                        <form action="{{ route('egresso.servicos.cancelar', $pedido->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja cancelar este pedido?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times me-1"></i>
                                                <span class="d-none d-md-inline">Cancelar</span>
                                            </button>
                                        </form>
                                    @elseif($pedido->status === 'atendido')
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                            <i class="fas fa-check-circle me-1"></i> Concluído
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pedidos->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-center">
                        {{ $pedidos->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif

        @else

            <div class="text-center py-5">
                <div class="empty-state-icon mb-4">
                    <i class="fas fa-concierge-bell"></i>
                </div>

                @if($temFiltros ?? false)
                    <h6 class="fw-bold mb-1">Sem pedidos com estes filtros</h6>
                    <p class="text-muted small mb-4">
                        Não encontrámos nenhum pedido com os critérios aplicados.
                    </p>
                    <a href="{{ route('egresso.servicos.meus-pedidos') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Limpar filtros
                    </a>
                @else
                    <h6 class="fw-bold mb-1">Nenhum pedido registado</h6>
                    <p class="text-muted small mb-4">
                        Ainda não solicitou nenhum serviço. Comece agora!
                    </p>
                    <a href="{{ route('egresso.servicos.solicitar') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Solicitar Serviço
                    </a>
                @endif
            </div>

        @endif
    </div>

</div>

@endsection

