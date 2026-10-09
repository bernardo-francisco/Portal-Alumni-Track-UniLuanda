@extends('layouts.admin')

@section('title', 'Editar Unidades Organica - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Editar Unidade Orgânica</h1>
        <p class="text-muted">Atualizar informações da unidade <strong>{{ $unidade->sigla }}</strong></p>
    </div>
    <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.unidades.update', $unidade->id) }}">
            @csrf @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sigla" class="form-label">Sigla <span class="text-danger">*</span></label>
                        <input type="text" name="sigla" id="sigla" class="form-control @error('sigla') is-invalid @enderror" value="{{ old('sigla', $unidade->sigla) }}" required>
                        @error('sigla')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $unidade->nome) }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4">{{ old('descricao', $unidade->descricao) }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar Unidade
                </button>
            </div>
        </form>
    </div>
</div>
@endsection