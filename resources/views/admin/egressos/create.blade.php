@extends('layouts.admin')

@section('title', 'Novo Egresso - Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Novo Egresso</h1>
        <p class="text-muted">Registar um novo ex-estudante com todos os dados</p>
    </div>
    <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

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
        <form method="POST" action="{{ route('admin.egressos.store') }}" enctype="multipart/form-data">
            @csrf

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
                           value="{{ old('numero_processo') }}" required>
                    @error('numero_processo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" name="nome_completo" id="nome_completo"
                           class="form-control @error('nome_completo') is-invalid @enderror"
                           value="{{ old('nome_completo') }}" required>
                    @error('nome_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                    <select name="genero" id="genero"
                            class="form-select @error('genero') is-invalid @enderror" required>
                        <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Feminino</option>
                        <option value="O" {{ old('genero') == 'O' ? 'selected' : '' }}>Outro</option>
                    </select>
                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                    <input type="date" name="data_nascimento" id="data_nascimento"
                           class="form-control @error('data_nascimento') is-invalid @enderror"
                           value="{{ old('data_nascimento') }}">
                    @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="exemplo@email.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" name="telefone" id="telefone"
                           class="form-control @error('telefone') is-invalid @enderror"
                           value="{{ old('telefone') }}" placeholder="+244 900 000 000">
                    @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" name="foto" id="foto"
                           class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Formatos: JPG, PNG. Máximo 2MB</small>
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
                                {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
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
                        <option value="">Selecione primeiro a unidade</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}"
                                    data-unidade="{{ $curso->unidade_id }}"
                                {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
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
                           value="{{ old('ano_formatura') }}"
                           min="1990" max="{{ date('Y') + 5 }}">
                    @error('ano_formatura') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="nota_final" class="form-label">Nota Final (0-20)</label>
                    <input type="number" name="nota_final" id="nota_final"
                           class="form-control @error('nota_final') is-invalid @enderror"
                           value="{{ old('nota_final') }}"
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
                           value="{{ old('pais') }}" placeholder="Ex: Angola">
                </div>

                <div class="col-md-3">
                    <label for="provincia" class="form-label">Província / Região</label>
                    <input type="text" name="provincia" id="provincia" class="form-control"
                           value="{{ old('provincia') }}" placeholder="Ex: Luanda">
                </div>

                <div class="col-md-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" name="cidade" id="cidade" class="form-control"
                           value="{{ old('cidade') }}" placeholder="Ex: Luanda">
                </div>

                <div class="col-md-3">
                    <label for="data_desde" class="form-label">Desde</label>
                    <input type="date" name="data_desde" id="data_desde" class="form-control"
                           value="{{ old('data_desde') }}">
                </div>

                <div class="col-md-3">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="latitude" class="form-control"
                           value="{{ old('latitude') }}" placeholder="Ex: -8.8383">
                </div>

                <div class="col-md-3">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="longitude" class="form-control"
                           value="{{ old('longitude') }}" placeholder="Ex: 13.2344">
                </div>

                <div class="col-md-6">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" name="endereco" id="endereco" class="form-control"
                           value="{{ old('endereco') }}" placeholder="Ex: Rua 1, Bairro 2">
                </div>

                <div class="col-md-3">
                    <div class="form-check mt-4 pt-2">
                        <input type="checkbox" name="is_current" id="is_current"
                               class="form-check-input" value="1"
                            {{ old('is_current') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_current">
                            <i class="fas fa-map-pin me-1"></i> Localização atual
                        </label>
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
                        <option value="full_time" {{ old('emprego_tipo') == 'full_time' ? 'selected' : '' }}>Tempo Inteiro</option>
                        <option value="part_time" {{ old('emprego_tipo') == 'part_time' ? 'selected' : '' }}>Tempo Parcial</option>
                        <option value="freelance" {{ old('emprego_tipo') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="self_employed" {{ old('emprego_tipo') == 'self_employed' ? 'selected' : '' }}>Autónomo</option>
                        <option value="unemployed" {{ old('emprego_tipo') == 'unemployed' ? 'selected' : '' }}>Desempregado</option>
                        <option value="student" {{ old('emprego_tipo') == 'student' ? 'selected' : '' }}>A Estudar</option>
                        <option value="unknown" {{ old('emprego_tipo') == 'unknown' ? 'selected' : '' }}>Desconhecido</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" name="cargo" id="cargo" class="form-control"
                           value="{{ old('cargo') }}" placeholder="Ex: Engenheiro de Software">
                </div>

                <div class="col-md-4">
                    <label for="empregador" class="form-label">Empregador</label>
                    <input type="text" name="empregador" id="empregador" class="form-control"
                           value="{{ old('empregador') }}" placeholder="Ex: Empresa ABC">
                </div>

                <div class="col-md-4">
                    <label for="sector" class="form-label">Sector</label>
                    <input type="text" name="sector" id="sector" class="form-control"
                           value="{{ old('sector') }}" placeholder="Ex: Tecnologia">
                </div>

                <div class="col-md-4">
                    <label for="data_inicio" class="form-label">Data de Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                           value="{{ old('data_inicio') }}">
                </div>

                <div class="col-md-4">
                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" class="form-control"
                           value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/perfil">
                </div>

                <div class="col-md-4">
                    <div class="form-check mt-4 pt-2">
                        <input type="checkbox" name="is_current_emprego" id="is_current_emprego"
                               class="form-check-input" value="1"
                            {{ old('is_current_emprego') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_current_emprego">
                            <i class="fas fa-briefcase me-1"></i> Emprego atual
                        </label>
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
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="lost_contact" {{ old('status') == 'lost_contact' ? 'selected' : '' }}>Sem Contacto</option>
                    </select>
                </div>

                <div class="col-md-8">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('admin.egressos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Egresso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


