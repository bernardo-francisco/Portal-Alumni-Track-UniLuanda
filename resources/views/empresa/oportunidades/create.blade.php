@extends('layouts.empresa')

@section('title', 'Nova Oportunidade')
@section('page-title', 'Nova Oportunidade')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10">

        <div class="page-header">
            <div>
                <h5 class="mb-1">Criar Nova Oportunidade</h5>
                <p class="text-muted small">Publique uma nova oportunidade para os egressos</p>
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

                <form method="POST" action="{{ route('empresa.oportunidades.store') }}">
                    @csrf

                    <div class="section-title"><i class="fas fa-info-circle me-2"></i> Informações Básicas</div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Título <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-heading text-muted"></i></span>
                                <input type="text" name="titulo" class="form-control border-start-0"
                                       value="{{ old('titulo') }}" required
                                       placeholder="Ex: Desenvolvedor Laravel Júnior">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                <option value="emprego" {{ old('tipo') == 'emprego' ? 'selected' : '' }}>💼 Emprego</option>
                                <option value="estagio" {{ old('tipo') == 'estagio' ? 'selected' : '' }}>🎓 Estágio</option>
                                <option value="bolsa" {{ old('tipo') == 'bolsa' ? 'selected' : '' }}>🏆 Bolsa</option>
                                <option value="curso" {{ old('tipo') == 'curso' ? 'selected' : '' }}>📚 Curso</option>
                                <option value="evento" {{ old('tipo') == 'evento' ? 'selected' : '' }}>🎪 Evento</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descrição <span class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control" rows="5" required
                                      placeholder="Descreva a oportunidade em detalhe...">{{ old('descricao') }}</textarea>
                        </div>

                    </div>

                    <div class="section-title"><i class="fas fa-cog me-2"></i> Detalhes</div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Localização</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" name="localizacao" class="form-control border-start-0"
                                       value="{{ old('localizacao') }}"
                                       placeholder="Ex: Luanda, Angola (Híbrido)">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Salário / Remuneração</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-money-bill text-muted"></i></span>
                                <input type="text" name="salario" class="form-control border-start-0"
                                       value="{{ old('salario') }}"
                                       placeholder="Ex: 350.000 Kz">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Requisitos</label>
                            <textarea name="requisitos" class="form-control" rows="4"
                                      placeholder="Liste os requisitos, competências e benefícios...">{{ old('requisitos') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Data Limite</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                                <input type="date" name="data_limite" class="form-control border-start-0"
                                       value="{{ old('data_limite') }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <small class="text-muted">Deixe vazio se não tiver prazo</small>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('empresa.oportunidades.index') }}" class="btn btn-light">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-check-circle me-2"></i> Publicar Oportunidade
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
