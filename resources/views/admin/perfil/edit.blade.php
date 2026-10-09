@extends('layouts.admin')

@section('title', 'Editar Perfil - Administrador')

@section('content')
<!-- Cabeçalho -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Editar Perfil</h1>
        <p class="text-muted">Actualize as suas informações pessoais</p>
    </div>
    <a href="{{ route('admin.perfil.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="fas fa-arrow-left me-2"></i> Voltar
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 card-shadow">
    <div class="card-body p-4 p-lg-5">
        <form method="POST" action="{{ route('admin.perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Coluna Esquerda - Foto -->
                <div class="col-lg-3 text-center">
                    <div class="form-group">
                        <label class="d-block mb-3">Foto de Perfil</label>
                        <div class="photo-preview-container">
                            @if($admin->foto_url)
                                <img id="fotoPreview" src="{{ asset($admin->foto_url) }}" class="photo-preview" alt="Foto de perfil">
                            @else
                                <div id="fotoPreviewPlaceholder" class="avatar-placeholder">
                                    {{ obterIniciais($admin->nome_completo) }}
                                </div>
                                <img id="fotoPreview" src="" class="photo-preview" style="display: none;" alt="Foto de perfil">
                            @endif
                            <div class="photo-upload-overlay">
                                <i class="fas fa-camera me-1"></i> Alterar Foto
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="custom-file-input">
                                <button type="button" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fas fa-upload me-2"></i> Escolher Foto
                                </button>
                                <input type="file" name="foto" id="foto" accept="image/*">
                            </div>
                            <small class="form-text d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i> Formatos: JPG, PNG (máx. 2MB)
                            </small>
                            @error('foto')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita - Campos -->
                <div class="col-lg-9">
                    <!-- Nome Completo -->
                    <div class="form-group mb-3">
                        <label>Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nome_completo" 
                               class="form-control @error('nome_completo') is-invalid @enderror" 
                               value="{{ old('nome_completo', $admin->nome_completo) }}" 
                               placeholder="Digite o seu nome completo"
                               required>
                        @error('nome_completo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email e Telefone -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $admin->email) }}" 
                                       placeholder="seu@email.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="tel" 
                                       name="telefone" 
                                       class="form-control @error('telefone') is-invalid @enderror" 
                                       value="{{ old('telefone', $admin->telefone) }}" 
                                       placeholder="+244 900 000 000">
                                @error('telefone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Cargo e Unidade -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cargo</label>
                                <input type="text" 
                                       name="cargo" 
                                       class="form-control @error('cargo') is-invalid @enderror" 
                                       value="{{ old('cargo', $admin->cargo ?? 'Administrador') }}" 
                                       placeholder="Ex: Administrador, Coordenador">
                                @error('cargo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unidade Orgânica</label>
                                <select name="unidade_id" class="form-select @error('unidade_id') is-invalid @enderror">
                                    <option value="">Selecione a unidade</option>
                                    @foreach($unidades as $unidade)
                                        <option value="{{ $unidade->id }}" 
                                            {{ old('unidade_id', $admin->unidade_id) == $unidade->id ? 'selected' : '' }}>
                                            {{ $unidade->sigla }} - {{ $unidade->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidade_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Nova Senha -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group position-relative">
                                <label>Nova Senha <small class="text-muted">(opcional)</small></label>
                                <input type="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password"
                                       placeholder="Digite a nova senha">
                                <span class="field-icon" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </span>
                                <small class="form-text">Mínimo 6 caracteres. Deixe em branco para manter a actual.</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group position-relative">
                                <label>Confirmar Nova Senha</label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       class="form-control" 
                                       id="password_confirmation"
                                       placeholder="Confirme a nova senha">
                                <span class="field-icon" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye" id="password-confirm-icon"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-3 mt-4 pt-4 border-top justify-content-end">
                <a href="{{ route('admin.perfil.index') }}" class="btn btn-cancel px-4">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-save text-white px-5">
                    <i class="fas fa-save me-2"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
   
@endsection