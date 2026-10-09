@extends('layouts.admin')

@section('title', 'Editar Pesquisa')

@section('page_title', '📊 Editar Pesquisa')
@section('page_subtitle', $pesquisa->titulo)

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Aviso --}}
        <div class="alert alert-info d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
            <div class="alert-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <strong class="d-block">Editar Pesquisa</strong>
                <small class="text-muted">
                    Pode alterar o título, descrição, datas e o estado (ativa/inativa) da pesquisa.
                    As perguntas são geridas numa página separada.
                </small>
            </div>
        </div>

        <form action="{{ route('admin.pesquisas.update', $pesquisa->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            {{-- Dados Principais --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Dados da Pesquisa</h6>
                    </div>
                </div>
                <div class="card-body p-4">

                    {{-- Título --}}
                    <div class="mb-4">
                        <label for="titulo" class="form-label fw-semibold">
                            Título <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="titulo"
                               id="titulo"
                               class="form-control form-control-lg @error('titulo') is-invalid @enderror"
                               value="{{ old('titulo', $pesquisa->titulo) }}"
                               placeholder="Ex: Pesquisa de Satisfação 2026"
                               maxlength="200"
                               required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Máximo de 200 caracteres.
                        </small>
                    </div>

                    {{-- Descrição --}}
                    <div class="mb-4">
                        <label for="descricao" class="form-label fw-semibold">Descrição</label>
                        <textarea name="descricao"
                                  id="descricao"
                                  class="form-control @error('descricao') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Breve descrição do objetivo desta pesquisa...">{{ old('descricao', $pesquisa->descricao) }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Datas --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="data_inicio" class="form-label fw-semibold">
                                Data de Início <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar text-primary"></i>
                                </span>
                                <input type="date"
                                       name="data_inicio"
                                       id="data_inicio"
                                       class="form-control border-start-0 @error('data_inicio') is-invalid @enderror"
                                       value="{{ old('data_inicio', $pesquisa->data_inicio ? \Carbon\Carbon::parse($pesquisa->data_inicio)->format('Y-m-d') : '') }}"
                                       required>
                                @error('data_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="data_fim" class="form-label fw-semibold">
                                Data de Término <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-calendar-check text-danger"></i>
                                </span>
                                <input type="date"
                                       name="data_fim"
                                       id="data_fim"
                                       class="form-control border-start-0 @error('data_fim') is-invalid @enderror"
                                       value="{{ old('data_fim', $pesquisa->data_fim ? \Carbon\Carbon::parse($pesquisa->data_fim)->format('Y-m-d') : '') }}"
                                       required>
                                @error('data_fim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            {{-- Estado --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Estado da Pesquisa</h6>
                    </div>
                </div>
                <div class="card-body p-4">

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="ativa"
                               id="ativa"
                               value="1"
                               {{ old('ativa', $pesquisa->ativa) ? 'checked' : '' }}
                               style="width: 3rem; height: 1.5rem;">
                        <label class="form-check-label ms-2" for="ativa">
                            <strong>Pesquisa Ativa</strong>
                            <br>
                            <small class="text-muted">
                                Quando ativa, os egressos podem visualizar e responder a esta pesquisa.
                            </small>
                        </label>
                    </div>

                </div>
            </div>


            {{-- Informações --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Estatísticas</h6>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-mini">
                                <small class="text-muted d-block">Perguntas</small>
                                <strong class="fs-4">{{ $pesquisa->perguntas()->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-mini">
                                <small class="text-muted d-block">Respostas</small>
                                <strong class="fs-4">{{ $pesquisa->respostas()->count() }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-mini">
                                <small class="text-muted d-block">Criada em</small>
                                <strong class="fs-6">
                                    {{ $pesquisa->created_at?->format('d/m/Y') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Ações --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                        <a href="{{ route('admin.pesquisas.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.pesquisas.perguntas', $pesquisa->id) }}"
                               class="btn btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Gerir Perguntas
                            </a>

                            <a href="{{ route('admin.pesquisas.resultados', $pesquisa->id) }}"
                               class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-1"></i> Ver Resultados
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Alterações
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

