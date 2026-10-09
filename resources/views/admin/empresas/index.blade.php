@extends('layouts.admin')

@section('title', 'Gestão de Empresas')
@section('page-title', 'Empresas')

@section('content')
<div class="container-fluid">

    {{-- CABEÇALHO --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">
            <i class="fas fa-building me-2 text-primary"></i>
            Gestão de Empresas
        </h5>

        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="width: 180px;">
                <option value="">Todos os estados</option>
                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendentes</option>
                <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovadas</option>
                <option value="reprovado" {{ request('status') == 'reprovado' ? 'selected' : '' }}>Reprovadas</option>
            </select>
            <button class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    {{-- MENSAGENS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- TABELA --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            @if($empresas->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Nenhuma empresa registada.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Logo</th>
                                <th>Nome</th>
                                <th>Sector</th>
                                <th>Email</th>
                                <th>Localização</th>
                                <th style="width: 130px;">Estado</th>
                                <th style="width: 120px;" class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $cores = [
                                    'pendente'  => 'warning',
                                    'aprovado'  => 'success',
                                    'reprovado' => 'danger',
                                ];
                            @endphp
                            @foreach($empresas as $emp)
                                <tr>
                                    <td>
                                        @if($emp->logo_url)
                                            <img src="{{ asset($emp->logo_url) }}"
                                                 alt="Logótipo de {{ $emp->nome }}"
                                                 style="max-height: 40px; max-width: 40px; object-fit: contain;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-building text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $emp->nome }}</strong>
                                        @if($emp->nif)
                                            <br><small class="text-muted">NIF: {{ $emp->nif }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            {{ $emp->sector ?? '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $emp->email }}</td>
                                    <td>
                                        {{ $emp->localizacao ?? '—' }}
                                        @if($emp->provincia)
                                            <br><small class="text-muted">{{ $emp->provincia }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php $cor = $cores[$emp->status_validacao] ?? 'secondary'; @endphp
                                        <span class="badge bg-{{ $cor }}">
                                            {{ ucfirst($emp->status_validacao) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.empresas.show', $emp->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $empresas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
