@extends('layouts.empresa')

@section('title', 'Editar Oportunidade')
@section('page-title', 'Editar Oportunidade')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10">

        <div class="page-header">
            <div>
                <h5 class="mb-1">Editar Oportunidade</h5>
                <p class="text-muted small">{{ $oportunidade->titulo }}</p>
            </div>
            <a href="{{ route('empresa.oportunidades.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger border-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('empresa.oportunidades.update', $oportunidade->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="section-title"><i class="fas fa-info-circle me-2"></i> Informações Básicas</div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" class="form-control" required
                                   value="{{ old('titulo', $oportunidade->titulo) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                @foreach(['emprego' => 'Emprego', 'estagio' => 'Estágio', 'bolsa' => 'Bolsa', 'curso' => 'Curso', 'evento' => 'Evento'] as $key => $label)
                                    <option value="{{ $key }}" {{ old('tipo', $oportunidade->tipo) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descrição <span class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control" rows="5" required>{{ old('descricao', $oportunidade->descricao) }}</textarea>
                        </div>
                    </div>

                    <div class="section-title"><i class="fas fa-cog me-2"></i> Detalhes</div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Localização</label>
                            <input type="text" name="localizacao" class="form-control"
                                   value="{{ old('localizacao', $oportunidade->localizacao) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Salário</label>
                            <input type="text" name="salario" class="form-control"
                                   value="{{ old('salario', $oportunidade->salario) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Requisitos</label>
                            <textarea name="requisitos" class="form-control" rows="4">{{ old('requisitos', $oportunidade->requisitos) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Data Limite</label>
                            <input type="date" name="data_limite" class="form-control"
                                   value="{{ old('data_limite', $oportunidade->data_limite) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="isActive" {{ $oportunidade->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Oportunidade Ativa</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('empresa.oportunidades.index') }}" class="btn btn-light">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i> Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
