@extends('layouts.admin')

@section('title', 'Nova Oportunidade - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Nova Oportunidade</h1>
        <p class="text-muted">Criar uma nova vaga, estágio ou bolsa</p>
    </div>
    <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.oportunidades.store') }}">
            @csrf

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unidade_id" class="form-label">Unidade Orgânica</label>
                        <select name="unidade_id" id="unidade_id" class="form-select @error('unidade_id') is-invalid @enderror">
                            <option value="">Todas as Unidades</option>
                            @foreach($unidades as $unidade)
                                <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                    {{ $unidade->sigla }}
                                </option>
                            @endforeach
                        </select>
                        @error('unidade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="5" required>{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="emprego" {{ old('tipo') == 'emprego' ? 'selected' : '' }}>Emprego</option>
                            <option value="estagio" {{ old('tipo') == 'estagio' ? 'selected' : '' }}>Estágio</option>
                            <option value="bolsa" {{ old('tipo') == 'bolsa' ? 'selected' : '' }}>Bolsa</option>
                            <option value="curso" {{ old('tipo') == 'curso' ? 'selected' : '' }}>Curso</option>
                            <option value="evento" {{ old('tipo') == 'evento' ? 'selected' : '' }}>Evento</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="empresa" class="form-label">Empresa</label>
                        <input type="text" name="empresa" id="empresa" class="form-control @error('empresa') is-invalid @enderror" value="{{ old('empresa') }}">
                        @error('empresa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="localizacao" class="form-label">Localização</label>
                        <input type="text" name="localizacao" id="localizacao" class="form-control @error('localizacao') is-invalid @enderror" value="{{ old('localizacao') }}">
                        @error('localizacao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="data_limite" class="form-label">Data Limite</label>
                        <input type="date" name="data_limite" id="data_limite" class="form-control @error('data_limite') is-invalid @enderror" value="{{ old('data_limite') }}">
                        @error('data_limite')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="salario" class="form-label">Salário</label>
                        <input type="text" name="salario" id="salario" class="form-control @error('salario') is-invalid @enderror" value="{{ old('salario') }}" placeholder="Ex: 500.000 Kz">
                        @error('salario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="requisitos" class="form-label">Requisitos</label>
                        <textarea name="requisitos" id="requisitos" class="form-control @error('requisitos') is-invalid @enderror" rows="3">{{ old('requisitos') }}</textarea>
                        @error('requisitos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="is_active" class="form-label">Activo</label>
                        <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                            <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Não</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.oportunidades.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Publicar Oportunidade
                </button>
            </div>
        </form>
    </div>
</div>
@endsection