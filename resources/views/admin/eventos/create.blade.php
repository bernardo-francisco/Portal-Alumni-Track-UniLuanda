@extends('layouts.admin')

@section('title', 'Novo Evento - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Novo Evento</h1>
        <p class="text-muted">Criar um novo evento para egressos</p>
    </div>
    <a href="{{ route('admin.eventos.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.eventos.store') }}">
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
                        <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="4" required>{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="presencial" {{ old('tipo') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                            <option value="online" {{ old('tipo') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="hibrido" {{ old('tipo') == 'hibrido' ? 'selected' : '' }}>Híbrido</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                            <option value="workshop" {{ old('categoria') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                            <option value="palestra" {{ old('categoria') == 'palestra' ? 'selected' : '' }}>Palestra</option>
                            <option value="networking" {{ old('categoria') == 'networking' ? 'selected' : '' }}>Networking</option>
                            <option value="job_fair" {{ old('categoria') == 'job_fair' ? 'selected' : '' }}>Job Fair</option>
                            <option value="curso" {{ old('categoria') == 'curso' ? 'selected' : '' }}>Curso</option>
                            <option value="outro" {{ old('categoria') == 'outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="data_inicio" class="form-label">Data de Início <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="data_inicio" id="data_inicio" class="form-control @error('data_inicio') is-invalid @enderror" value="{{ old('data_inicio') }}" required>
                        @error('data_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="data_fim" class="form-label">Data de Fim</label>
                        <input type="datetime-local" name="data_fim" id="data_fim" class="form-control @error('data_fim') is-invalid @enderror" value="{{ old('data_fim') }}">
                        @error('data_fim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="local" class="form-label">Local</label>
                        <input type="text" name="local" id="local" class="form-control @error('local') is-invalid @enderror" value="{{ old('local') }}" placeholder="Ex: Auditório Central">
                        @error('local')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="max_participantes" class="form-label">Máximo de Participantes</label>
                        <input type="number" name="max_participantes" id="max_participantes" class="form-control @error('max_participantes') is-invalid @enderror" value="{{ old('max_participantes') }}" min="1">
                        @error('max_participantes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="link_reuniao" class="form-label">Link da Reunião (para online)</label>
                        <input type="url" name="link_reuniao" id="link_reuniao" class="form-control @error('link_reuniao') is-invalid @enderror" value="{{ old('link_reuniao') }}" placeholder="https://meet.google.com/...">
                        @error('link_reuniao')
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
                <a href="{{ route('admin.eventos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Evento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection