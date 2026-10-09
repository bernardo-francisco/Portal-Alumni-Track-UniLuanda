@extends('layouts.admin')

@section('title', 'Nova Publicação')

@section('page_title', 'Nova Publicação')
@section('page_subtitle', 'Crie uma nova publicação para o mural')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle text-primary"></i> Nova Publicação</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.mural.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="titulo" class="form-label text-dark">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" placeholder="Digite o título..." required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="tipo" class="form-label text-dark">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="noticia" {{ old('tipo') == 'noticia' ? 'selected' : '' }}>Notícia</option>
                            <option value="evento" {{ old('tipo') == 'evento' ? 'selected' : '' }}>Evento</option>
                            <option value="edital" {{ old('tipo') == 'edital' ? 'selected' : '' }}>Edital</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="conteudo" class="form-label text-dark">Conteúdo <span class="text-danger">*</span></label>
                        <textarea name="conteudo" id="conteudo" class="form-control @error('conteudo') is-invalid @enderror" rows="6" placeholder="Digite o conteúdo da publicação..." required>{{ old('conteudo') }}</textarea>
                        @error('conteudo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="data_evento" class="form-label text-dark">Data do Evento</label>
                                <input type="date" name="data_evento" id="data_evento" class="form-control @error('data_evento') is-invalid @enderror" value="{{ old('data_evento') }}">
                                @error('data_evento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="local" class="form-label text-dark">Local</label>
                                <input type="text" name="local" id="local" class="form-control @error('local') is-invalid @enderror" value="{{ old('local') }}" placeholder="Ex: Online, Auditório Principal, etc.">
                                @error('local')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="imagem" class="form-label text-dark">Imagem</label>
                        <input type="file" name="imagem" id="imagem" class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Formatos: JPG, PNG (máx. 2MB)</small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="destaque" class="form-check-input" id="destaque" value="1" {{ old('destaque') ? 'checked' : '' }}>
                                <label class="form-check-label text-dark" for="destaque">⭐ Destacar publicação</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="publicado" class="form-check-input" id="publicado" value="1" {{ old('publicado', true) ? 'checked' : '' }}>
                                <label class="form-check-label text-dark" for="publicado">Publicar imediatamente</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Publicar
                        </button>
                        <a href="{{ route('admin.mural.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection