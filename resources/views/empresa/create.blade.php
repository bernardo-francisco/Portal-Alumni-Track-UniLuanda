@extends('layouts.empresa')

@section('title', 'Nova Oportunidade')
@section('page-title', 'Nova Oportunidade')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('empresa.oportunidades.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" required value="{{ old('titulo') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="emprego">Emprego</option>
                        <option value="estagio">Estágio</option>
                        <option value="bolsa">Bolsa</option>
                        <option value="curso">Curso</option>
                        <option value="evento">Evento</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descrição *</label>
                    <textarea name="descricao" class="form-control" rows="4" required>{{ old('descricao') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Localização</label>
                    <input type="text" name="localizacao" class="form-control" value="{{ old('localizacao') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Salário</label>
                    <input type="text" name="salario" class="form-control" value="{{ old('salario') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Requisitos</label>
                    <textarea name="requisitos" class="form-control" rows="3">{{ old('requisitos') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data Limite</label>
                    <input type="date" name="data_limite" class="form-control" value="{{ old('data_limite') }}">
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('empresa.oportunidades.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Publicar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection