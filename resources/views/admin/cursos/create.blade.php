@extends('layouts.admin')

@section('title', 'Novo Curso - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Novo Curso</h1>
        <p class="text-muted">Adicionar um novo curso ao sistema</p>
    </div>
    <a href="{{ route('admin.cursos.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cursos.store') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unidade_id" class="form-label">Unidade Orgânica <span class="text-danger">*</span></label>
                        <select name="unidade_id" id="unidade_id" class="form-select @error('unidade_id') is-invalid @enderror" required>
                            <option value="">Selecione a unidade</option>
                            @foreach($unidades as $unidade)
                                <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                    {{ $unidade->sigla }} - {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('unidade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}" placeholder="Ex: EI" required>
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do Curso <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" placeholder="Ex: Engenharia Informática" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label for="departamento" class="form-label">Departamento</label>
                        <input type="text" name="departamento" id="departamento" class="form-control @error('departamento') is-invalid @enderror" value="{{ old('departamento') }}" placeholder="Ex: Ciências e Tecnologia">
                        @error('departamento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="duracao" class="form-label">Duração (anos)</label>
                        <input type="number" name="duracao" id="duracao" class="form-control @error('duracao') is-invalid @enderror" value="{{ old('duracao') }}" placeholder="Ex: 5" min="1" max="10">
                        @error('duracao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.cursos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Curso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection