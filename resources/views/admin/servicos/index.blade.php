@extends('layouts.admin')

@section('title', 'Pedidos de Serviços')

@section('page_title', '📋 Pedidos de Serviços')
@section('page_subtitle', 'Gerir os pedidos de serviços dos egressos')

@section('content')

@php
    $statusAtual = $status ?? 'todos';
@endphp

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('admin.servicos.index', ['status' => 'todos']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-primary {{ $statusAtual === 'todos' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $totalPedidos }}</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('admin.servicos.index', ['status' => 'pendente']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-warning {{ $statusAtual === 'pendente' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $pendentes }}</div>
                    <div class="stat-label">Pendentes</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('admin.servicos.index', ['status' => 'andamento']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-info {{ $statusAtual === 'andamento' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $andamento }}</div>
                    <div class="stat-label">Em Andamento</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-md-6 col-6">
        <a href="{{ route('admin.servicos.index', ['status' => 'atendido']) }}" class="text-decoration-none">
            <div class="stat-card stat-card-success {{ $statusAtual === 'atendido' ? 'stat-active' : '' }}">
                <div class="stat-icon-wrap">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-value">{{ $atendidos }}</div>
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

        <form action="{{ route('admin.servicos.index') }}" method="GET">
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
                               placeholder="Egresso, nº processo ou serviço..."
                               value="{{ request('q') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Estado
                    </label>
                    <select name="status" class="form-select">
                        <option value="todos"    {{ $statusAtual === 'todos'    ? 'selected' : '' }}>Todos</option>
                        <option value="pendente" {{ $statusAtual === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                        <option value="andamento"{{ $statusAtual === 'andamento'? 'selected' : '' }}>Em Andamento</option>
                        <option value="atendido" {{ $statusAtual === 'atendido' ? 'selected' : '' }}>Atendidos</option>
                        <option value="cancelado"{{ $statusAtual === 'cancelado'? 'selected' : '' }}>Cancelados</option>
                    </select>
                </div>

                {{-- Egresso --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Egresso
                    </label>
                    <select name="egresso_id" class="form-select">
                        <option value="">Todos os egressos</option>
                        @foreach($egressos as $e)
                            <option value="{{ $e->id }}"
                                {{ request('egresso_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->nome_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ordenação --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Ordenar
                    </label>
                    <select name="ordenacao" class="form-select">
                        <option value="recentes" {{ request('ordenacao') === 'recentes' ? 'selected' : '' }}>
                            Mais recentes
                        </option>
                        <option value="antigos" {{ request('ordenacao') === 'antigos' ? 'selected' : '' }}>
                            Mais antigos
                        </option>
                        <option value="pendentes_primeiro" {{ request('ordenacao') === 'pendentes_primeiro' ? 'selected' : '' }}>
                            Pendentes primeiro
                        </option>
                        <option value="atendidos_recentes" {{ request('ordenacao') === 'atendidos_recentes' ? 'selected' : '' }}>
                            Atendidos recentes
                        </option>
                    </select>
                </div>

                {{-- Data início --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Data início
                    </label>
                    <input type="date"
                           name="data_inicio"
                           class="form-control"
                           value="{{ request('data_inicio') }}">
                </div>

                {{-- Data fim --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Data fim
                    </label>
                    <input type="date"
                           name="data_fim"
                           class="form-control"
                           value="{{ request('data_fim') }}">
                </div>

                {{-- Botões --}}
                <div class="col-lg-6 col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>

                        @if($temFiltros ?? false)
                            <a href="{{ route('admin.servicos.index') }}"
                               class="btn btn-outline-secondary">
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
                        {{ $pedidos->total() }} {{ $pedidos->total() === 1 ? 'pedido' : 'pedidos' }}
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
                            <th>Egresso</th>
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
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="egresso-avatar">
                                            {{ strtoupper(mb_substr($pedido->egresso->nome_completo ?? 'E', 0, 1)) }}
                                        </div>
                                        <div class="min-width-0">
                                            <strong class="d-block text-truncate">
                                                {{ $pedido->egresso->nome_completo ?? 'Egresso' }}
                                            </strong>
                                            <small class="text-muted">
                                                {{ $pedido->egresso->numero_processo ?? '' }}
                                            </small>
                                        </div>
                                    </div>
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
                                    <div class="d-flex justify-content-end gap-1">

                                        {{-- Ver --}}
                                        <a href="{{ route('admin.servicos.show', $pedido->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Atender --}}
                                        @if($pedido->status === 'pendente' || $pedido->status === 'andamento')
                                            <form action="{{ route('admin.servicos.atender', $pedido->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Marcar este pedido como atendido?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Marcar como atendido">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Eliminar --}}
                                        <form action="{{ route('admin.servicos.destroy', $pedido->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja eliminar este pedido?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar">
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

            {{-- Paginação --}}
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
                        Não encontramos nenhum pedido com os critérios aplicados.
                    </p>
                    <a href="{{ route('admin.servicos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Limpar filtros
                    </a>
                @else
                    <h6 class="fw-bold mb-1">Nenhum pedido registado</h6>
                    <p class="text-muted small mb-0">
                        Ainda não existem pedidos de serviços submetidos.
                    </p>
                @endif
            </div>

        @endif
    </div>

</div>

@endsection

