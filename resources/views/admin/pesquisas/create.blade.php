@extends('layouts.admin')

@section('title', 'Nova Pesquisa')

@section('page_title', 'Nova Pesquisa')
@section('page_subtitle', 'Crie uma nova pesquisa para os egressos')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle text-primary"></i> Criar Nova Pesquisa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pesquisas.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="titulo" class="form-label text-dark">Título da Pesquisa <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" placeholder="Ex: Pesquisa de Satisfação dos Egressos" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="descricao" class="form-label text-dark">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="3" placeholder="Descreva o objetivo da pesquisa...">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="data_inicio" class="form-label text-dark">Data de Início <span class="text-danger">*</span></label>
                                <input type="date" name="data_inicio" id="data_inicio" class="form-control @error('data_inicio') is-invalid @enderror" value="{{ old('data_inicio', date('Y-m-d')) }}" required>
                                @error('data_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="data_fim" class="form-label text-dark">Data de Fim <span class="text-danger">*</span></label>
                                <input type="date" name="data_fim" id="data_fim" class="form-control @error('data_fim') is-invalid @enderror" value="{{ old('data_fim', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                @error('data_fim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar e Adicionar Perguntas
                        </button>
                        <a href="{{ route('admin.pesquisas.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection