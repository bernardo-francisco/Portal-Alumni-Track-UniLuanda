@extends('layouts.admin')

@section('title', 'Editar Egresso - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Editar Egresso</h1>
        <p class="text-muted">Atualizar informações de <strong>{{ $egresso->nome_completo }}</strong></p>
    </div>
    <div>
        <a href="{{ route('admin.egressos.show', $egresso->id) }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-eye"></i> Ver Perfil
        </a>
        <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Por favor, corrija os seguintes erros:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.egressos.update', $egresso->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- ============================================================
                 DADOS PESSOAIS
            ============================================================ -->
            <div class="row g-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2 text-primary">
                        <i class="fas fa-user me-2"></i> Dados Pessoais
                    </h5>
                </div>

                <div class="col-md-4">
                    <label for="numero_processo" class="form-label">Nº Processo <span class="text-danger">*</span></label>
                    <input type="text" name="numero_processo" id="numero_processo"
                           class="form-control @error('numero_processo') is-invalid @enderror"
                           value="{{ old('numero_processo', $egresso->numero_processo) }}" required>
                    @error('numero_processo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" name="nome_completo" id="nome_completo"
                           class="form-control @error('nome_completo') is-invalid @enderror"
                           value="{{ old('nome_completo', $egresso->nome_completo) }}" required>
                    @error('nome_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                    <select name="genero" id="genero"
                            class="form-select @error('genero') is-invalid @enderror" required>
                        <option value="M" {{ old('genero', $egresso->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('genero', $egresso->genero) == 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="O" {{ old('genero', $egresso->genero) == 'O' ? 'selected' : '' }}>Outro</option>
                    </select>
                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                    <input type="date" name="data_nascimento" id="data_nascimento"
                           class="form-control @error('data_nascimento') is-invalid @enderror"
                           value="{{ old('data_nascimento', $egresso->data_nascimento ? $egresso->data_nascimento->format('Y-m-d') : '') }}">
                    @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $egresso->email) }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" name="telefone" id="telefone"
                           class="form-control @error('telefone') is-invalid @enderror"
                           value="{{ old('telefone', $egresso->telefone) }}">
                    @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label for="foto" class="form-label">Foto</label>
                    @if($egresso->foto_url)
                        <div class="mb-2">
                            <img src="{{ asset($egresso->foto_url) }}" class="rounded-circle"
                                 width="80" height="80" style="object-fit: cover;">
                            <span class="text-muted ms-2">Foto atual</span>
                        </div>
                    @endif
                    <input type="file" name="foto" id="foto"
                           class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Deixe em branco para manter a foto atual. Formatos: JPG, PNG. Máximo 2MB</small>
                    @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- ============================================================
                     DADOS ACADÉMICOS
                ============================================================ -->
                <div class="col-12 mt-3">
                    <h5 class="border-bottom pb-2 text-primary">
                        <i class="fas fa-graduation-cap me-2"></i> Dados Académicos
                    </h5>
                </div>

                {{-- ✅ UNIDADE ORGÂNICA --}}
                <div class="col-md-4">
                    <label for="unidade_id" class="form-label">
                        Unidade Orgânica <span class="text-danger">*</span>
                    </label>
                    <select name="unidade_id" id="unidade_id"
                            class="form-select @error('unidade_id') is-invalid @enderror"
                            required>
                        <option value="">Selecione a unidade</option>
                        @foreach($unidades as $unidade)
                            <option value="{{ $unidade->id }}"
                                {{ old('unidade_id', $egresso->curso->unidade_id ?? '') == $unidade->id ? 'selected' : '' }}>
                                {{ $unidade->sigla }} - {{ $unidade->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('unidade_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ✅ CURSO (dinâmico) --}}
                <div class="col-md-4">
                    <label for="curso_id" class="form-label">
                        Curso <span class="text-danger">*</span>
                    </label>
                    <select name="curso_id" id="curso_id"
                            class="form-select @error('curso_id') is-invalid @enderror"
                            required>
                        <option value="">Selecione o curso</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}"
                                    data-unidade="{{ $curso->unidade_id }}"
                                {{ old('curso_id', $egresso->curso_id) == $curso->id ? 'selected' : '' }}>
                                {{ $curso->nome }} ({{ $curso->codigo ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('curso_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted d-block mt-1" id="aviso-curso"></small>
                </div>

                <div class="col-md-4">
                    <label for="ano_formatura" class="form-label">Ano de Formatura</label>
                    <input type="number" name="ano_formatura" id="ano_formatura"
                           class="form-control @error('ano_formatura') is-invalid @enderror"
                           value="{{ old('ano_formatura', $egresso->ano_formatura) }}"
                           min="1990" max="{{ date('Y') + 5 }}">
                    @error('ano_formatura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="nota_final" class="form-label">Nota Final (0-20)</label>
                    <input type="number" name="nota_final" id="nota_final"
                           class="form-control @error('nota_final') is-invalid @enderror"
                           value="{{ old('nota_final', $egresso->nota_final) }}"
                           step="0.01" min="0" max="20">
                    @error('nota_final') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- ============================================================
                     LOCALIZAÇÃO
                ============================================================ -->
                <div class="col-12 mt-3">
                    <h5 class="border-bottom pb-2 text-primary">
                        <i class="fas fa-map-marker-alt me-2"></i> Localização Atual
                    </h5>
                </div>

                <div class="col-md-3">
                    <label for="pais" class="form-label">País</label>
                    <input type="text" name="pais" id="pais" class="form-control"
                           value="{{ old('pais', $egresso->localizacaoAtual->pais ?? '') }}">
                </div>

                <div class="col-md-3">
                    <label for="provincia" class="form-label">Província / Região</label>
                    <input type="text" name="provincia" id="provincia" class="form-control"
                           value="{{ old('provincia', $egresso->localizacaoAtual->provincia ?? '') }}">
                </div>

                <div class="col-md-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control"
                           value="{{ old('cidade', $egresso->localizacaoAtual->cidade ?? '') }}">
                </div>

                <div class="col-md-3">
                    <label for="data_desde" class="form-label">Desde</label>
                    <input type="date" name="data_desde" id="data_desde" class="form-control"
                           value="{{ old('data_desde', $egresso->localizacaoAtual && $egresso->localizacaoAtual->data_desde ? $egresso->localizacaoAtual->data_desde->format('Y-m-d') : '') }}">
                </div>

                <div class="col-md-3">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-control"
                           value="{{ old('latitude', $egresso->localizacaoAtual->latitude ?? '') }}">
                </div>

                <div class="col-md-3">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-control"
                           value="{{ old('longitude', $egresso->localizacaoAtual->longitude ?? '') }}">
                </div>

                <div class="col-md-6">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control"
                           value="{{ old('endereco', $egresso->localizacaoAtual->endereco ?? '') }}">
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-4 pt-2">
                        <input type="checkbox" name="is_current" id="is_current"
                               class="form-check-input" value="1"
                            {{ old('is_current', $egresso->localizacaoAtual->is_current ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_current">Localização atual</label>
                    </div>
                </div>

                <!-- ============================================================
                     SITUAÇÃO PROFISSIONAL
                ============================================================ -->
                <div class="col-12 mt-3">
                    <h5 class="border-bottom pb-2 text-primary">
                        <i class="fas fa-briefcase me-2"></i> Situação Profissional
                    </h5>
                </div>

                <div class="col-md-4">
                    <label for="emprego_tipo" class="form-label">Tipo de Emprego</label>
                    <select name="emprego_tipo" id="emprego_tipo" class="form-select">
                        <option value="">Selecione</option>
                        <option value="full_time" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'full_time' ? 'selected' : '' }}>Tempo Inteiro</option>
                        <option value="part_time" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'part_time' ? 'selected' : '' }}>Tempo Parcial</option>
                        <option value="freelance" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="self_employed" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'self_employed' ? 'selected' : '' }}>Autónomo</option>
                        <option value="unemployed" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'unemployed' ? 'selected' : '' }}>Desempregado</option>
                        <option value="student" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'student' ? 'selected' : '' }}>A Estudar</option>
                        <option value="unknown" {{ old('emprego_tipo', $egresso->profissionalAtual->tipo_emprego ?? '') == 'unknown' ? 'selected' : '' }}>Desconhecido</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="cargo" class="form-control"
                           value="{{ old('cargo', $egresso->profissionalAtual->cargo ?? '') }}">
                </div>

                <div class="col-md-4">
                    <label for="empregador" class="form-label">Empregador</label>
                    <input type="text" name="empregador" id="empregador" class="form-control"
                           value="{{ old('empregador', $egresso->profissionalAtual->empregador ?? '') }}">
                </div>

                <div class="col-md-4">
                    <label for="sector" class="form-label">Sector</label>
                    <input type="text" name="sector" id="sector" class="form-control"
                           value="{{ old('sector', $egresso->profissionalAtual->sector ?? '') }}">
                </div>

                <div class="col-md-4">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                           value="{{ old('data_inicio', $egresso->profissionalAtual && $egresso->profissionalAtual->data_inicio ? $egresso->profissionalAtual->data_inicio->format('Y-m-d') : '') }}">
                </div>

                <div class="col-md-4">
                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" class="form-control"
                           value="{{ old('linkedin_url', $egresso->profissionalAtual->linkedin_url ?? '') }}">
                </div>

                <div class="col-md-4">
                    <div class="form-check mt-4 pt-2">
                        <input type="checkbox" name="is_current_emprego" id="is_current_emprego"
                               class="form-check-input" value="1"
                            {{ old('is_current_emprego', $egresso->profissionalAtual->is_current ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_current_emprego">Emprego atual</label>
                    </div>
                </div>

                <!-- ============================================================
                     STATUS
                ============================================================ -->
                <div class="col-12 mt-3">
                    <h5 class="border-bottom pb-2 text-primary">
                        <i class="fas fa-info-circle me-2"></i> Status
                    </h5>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" {{ old('status', $egresso->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status', $egresso->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="lost_contact" {{ old('status', $egresso->status) == 'lost_contact' ? 'selected' : '' }}>Sem Contacto</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" rows="3">{{ old('observacoes', $egresso->observacoes) }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar Egresso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


