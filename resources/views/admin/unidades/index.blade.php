@extends('layouts.admin')

@section('title', 'Unidades Orgânicas - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Unidades Orgânicas</h1>
        <p class="text-muted">Gestão das unidades da UniLuanda</p>
    </div>
    <a href="{{ route('admin.unidades.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Unidade
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    @forelse($unidades as $unidade)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5>{{ $unidade->sigla }}</h5>
                        <h6 class="text-muted">{{ $unidade->nome }}</h6>
                    </div>
                    <span class="badge bg-primary rounded-pill">{{ $unidade->cursos_count ?? 0 }} cursos</span>
                </div>
                @if($unidade->descricao)
                    <p class="text-muted mt-2">{{ $unidade->descricao }}</p>
                @endif
            </div>
            <div class="card-footer bg-transparent border-top">
                <div class="btn-group w-100">
                    <a href="{{ route('admin.unidades.edit', $unidade->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form action="{{ route('admin.unidades.destroy', $unidade->id) }}" method="POST" class="d-inline w-50" onsubmit="return confirm('Tem certeza?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h4>Nenhuma unidade cadastrada</h4>
                <a href="{{ route('admin.unidades.create') }}" class="btn btn-primary">Nova Unidade</a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection