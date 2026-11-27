@extends('layouts.admin')

@section('title', 'Egressos')

@section('page_title', '👥 Gestão de Egressos')
@section('page_subtitle', 'Ex-estudantes registados na plataforma')

@section('content')

@php
    $total = $egressos->total() ?? 0;
    $paginaAtual = $egressos->count() ?? 0;
@endphp


{{-- ============================================================
     CABEÇALHO
============================================================ --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h5 class="mb-1 fw-bold">
            <i class="fas fa-users text-primary me-2"></i>
            Lista de Egressos
        </h5>
        <p class="text-muted mb-0 small">
            {{ $total }} {{ $total === 1 ? 'egresso registado' : 'egressos registados' }}
        </p>
    </div>

    <a href="{{ route('admin.egressos.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Novo Egresso
    </a>
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
     FILTROS
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        {{-- ✅ Adicionada a classe 'form-filtros-ajax' --}}
        <form method="GET" action="{{ route('admin.egressos.index') }}" class="form-filtros-ajax">
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
                               name="search"
                               class="form-control border-start-0"
                               placeholder="Nome, número ou email..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Unidade --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Unidade
                    </label>
                    <select name="unidade_id" class="form-select">
                        <option value="">Todas as Unidades</option>
                        @foreach($unidades as $unidade)
                            <option value="{{ $unidade->id }}"
                                {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                {{ $unidade->sigla }} — {{ $unidade->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Curso --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Curso
                    </label>
                    <select name="curso_id" class="form-select">
                        <option value="">Todos os Cursos</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}"
                                {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-semibold small text-muted text-uppercase">
                        Status
                    </label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="active"       {{ request('status') == 'active'       ? 'selected' : '' }}>Activo</option>
                        <option value="inactive"     {{ request('status') == 'inactive'     ? 'selected' : '' }}>Inactivo</option>
                        <option value="lost_contact" {{ request('status') == 'lost_contact' ? 'selected' : '' }}>Sem Contacto</option>
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        @if(request()->hasAny(['search', 'unidade_id', 'curso_id', 'status']))
                            {{-- ✅ Adicionada a classe 'btn-limpar-filtros' --}}
                            <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary btn-limpar-filtros">
                                <i class="fas fa-times me-1"></i> Limpar filtros
                            </a>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Aplicar filtros
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>


{{-- ============================================================
     LISTA DE EGRESSOS
============================================================ --}}
<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Lista de Egressos</h6>
                    <small class="text-muted" id="contador-egressos">
                        {{ $paginaAtual }} de {{ $total }} {{ $total === 1 ? 'egresso' : 'egressos' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Contentor onde o AJAX vai injetar a tabela atualizada --}}
    {{-- ✅ Adicionada a classe 'container-tabela-ajax' --}}
    <div class="card-body p-0 container-tabela-ajax" id="container-tabela-egressos">
        @include('admin.egressos.partials.tabela', ['egressos' => $egressos])
    </div>

    {{-- Paginação --}}
    @if(isset($egressos) && method_exists($egressos, 'links') && $egressos->hasPages())
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $egressos->appends(request()->query())->links() }}
            </div>
        </div>
    @endif

</div>

@endsection

{{-- ✅ Carrega o JS específico de filtros (apenas nesta página) --}}
@push('scripts')
    @vite('resources/js/filtros-ajax.js')
@endpush