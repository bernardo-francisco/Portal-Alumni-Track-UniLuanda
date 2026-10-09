@extends('layouts.egresso')

@section('title', 'Editar Perfil')

@section('page_title', '✏️ Editar Perfil')
@section('page_subtitle', 'Actualize as suas informações pessoais')

@section('content')

@php
    $temFoto = !empty($egresso->foto_url);
    $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
        ->filter()
        ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->take(2)
        ->implode('');

    $loc  = $localizacaoAtual ?? null;
    $prof = $profissionalAtual ?? null;

    $areas = [
        'Tecnologias de Informação',
        'Engenharia',
        'Educação',
        'Saúde',
        'Finanças',
        'Gestão e Administração',
        'Direito',
        'Comunicação Social',
        'Marketing e Vendas',
        'Recursos Humanos',
        'Construção Civil',
        'Agricultura',
        'Energia',
        'Outro',
    ];
@endphp

<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">

        {{-- ALERTAS --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                <strong><i class="fas fa-exclamation-triangle me-1"></i> Verifique os seguintes erros:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        <form method="POST"
              action="{{ route('egresso.perfil.update') }}"
              enctype="multipart/form-data"
              id="formPerfil">
            @csrf
            @method('PUT')

            {{-- ============================================================
                 CABEÇALHO — FOTO + NOME + AÇÕES
            ============================================================ --}}
            <div class="card border-0 shadow-sm mb-4 profile-header">
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">

                        {{-- FOTO --}}
                        <div class="col-auto">
                            <div class="avatar-upload-wrapper">
                                <div class="avatar-wrapper">
                                    @if($temFoto)
                                        <img src="{{ asset($egresso->foto_url) }}"
                                             id="avatarPreview"
                                             class="avatar-preview"
                                             alt="Foto de perfil"
                                             onerror="this.style.display='none'; document.getElementById('avatarFallback').style.display='flex';">
                                        <div id="avatarFallback" class="avatar-placeholder" style="display: none;">
                                            {{ $iniciais }}
                                        </div>
                                    @else
                                        <div id="avatarPreview" class="avatar-placeholder">
                                            {{ $iniciais }}
                                        </div>
                                    @endif
                                </div>

                                <label for="foto" class="avatar-change-btn" title="Alterar foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="foto" id="foto" class="d-none" accept="image/*">
                            </div>

                            <div class="text-center mt-3">
                                <label for="foto" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-upload me-1"></i> Alterar Foto
                                </label>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-info-circle me-1"></i> JPG, PNG · Máx. 2MB
                                </div>
                                @error('foto')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- INFO + AÇÕES --}}
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h4 class="fw-bold mb-2 profile-name">{{ $egresso->nome_completo }}</h4>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="header-pill">
                                            <i class="fas fa-id-card"></i>
                                            {{ $egresso->numero_processo ?? '—' }}
                                        </span>
                                        <span class="header-pill">
                                            <i class="fas fa-envelope"></i>
                                            {{ $egresso->email }}
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>


            {{-- TABS --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs nav-tabs-modern justify-content-center px-3 pt-3" id="perfilTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-pessoais" data-bs-toggle="tab"
                                    data-bs-target="#pane-pessoais" type="button" role="tab">
                                <i class="fas fa-user"></i> <span class="d-none d-sm-inline">Pessoais</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-academicos" data-bs-toggle="tab"
                                    data-bs-target="#pane-academicos" type="button" role="tab">
                                <i class="fas fa-graduation-cap"></i> <span class="d-none d-sm-inline">Académicos</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-localizacao" data-bs-toggle="tab"
                                    data-bs-target="#pane-localizacao" type="button" role="tab">
                                <i class="fas fa-map-marker-alt"></i> <span class="d-none d-sm-inline">Localização</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-profissional" data-bs-toggle="tab"
                                    data-bs-target="#pane-profissional" type="button" role="tab">
                                <i class="fas fa-briefcase"></i> <span class="d-none d-sm-inline">Profissional</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-seguranca" data-bs-toggle="tab"
                                    data-bs-target="#pane-seguranca" type="button" role="tab">
                                <i class="fas fa-lock"></i> <span class="d-none d-sm-inline">Segurança</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">

                        {{-- TAB 1 — DADOS PESSOAIS --}}
                        <div class="tab-pane fade show active" id="pane-pessoais" role="tabpanel">
                            <div class="section-header mb-4">
                                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Dados Pessoais</h6>
                                    <small class="text-muted">Informações básicas do seu perfil</small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-8">
                                    <label for="nome_completo" class="form-label fw-semibold">
                                        Nome Completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nome_completo" id="nome_completo"
                                           class="form-control @error('nome_completo') is-invalid @enderror"
                                           value="{{ old('nome_completo', $egresso->nome_completo) }}" required>
                                    @error('nome_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="telefone" class="form-label fw-semibold">Telefone</label>
                                    <input type="text" name="telefone" id="telefone"
                                           class="form-control @error('telefone') is-invalid @enderror"
                                           value="{{ old('telefone', $egresso->telefone) }}"
                                           placeholder="+244 900 000 000">
                                    @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="genero" class="form-label fw-semibold">
                                        Género <span class="text-danger">*</span>
                                    </label>
                                    <select name="genero" id="genero"
                                            class="form-select @error('genero') is-invalid @enderror" required>
                                        <option value="M" {{ old('genero', $egresso->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('genero', $egresso->genero) == 'F' ? 'selected' : '' }}>Feminino</option>
                                        <option value="O" {{ old('genero', $egresso->genero) == 'O' ? 'selected' : '' }}>Outro</option>
                                    </select>
                                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="data_nascimento" class="form-label fw-semibold">Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" id="data_nascimento"
                                           class="form-control @error('data_nascimento') is-invalid @enderror"
                                           value="{{ old('data_nascimento', $egresso->data_nascimento) }}">
                                    @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Email <i class="fas fa-lock text-muted ms-1" style="font-size: 0.7rem;"></i>
                                    </label>
                                    <input type="email" class="form-control bg-light"
                                           value="{{ $egresso->email }}" readonly>
                                    <small class="text-muted">
                                        Contacte a administração para alterar.
                                    </small>
                                </div>

                            </div>
                        </div>


                        {{-- TAB 2 — DADOS ACADÉMICOS --}}
                        <div class="tab-pane fade" id="pane-academicos" role="tabpanel">
                            <div class="section-header mb-4">
                                <div class="section-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Dados Académicos</h6>
                                    <small class="text-muted">Informação sobre a sua formação</small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label for="curso_id" class="form-label fw-semibold">Curso</label>
                                    <select name="curso_id" id="curso_id" class="form-select">
                                        <option value="">Selecione um curso</option>
                                        @foreach($cursos as $curso)
                                            <option value="{{ $curso->id }}"
                                                {{ old('curso_id', $egresso->curso_id) == $curso->id ? 'selected' : '' }}>
                                                {{ $curso->nome }} @if($curso->codigo) ({{ $curso->codigo }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="ano_formatura" class="form-label fw-semibold">Ano de Formatura</label>
                                    <input type="number" name="ano_formatura" id="ano_formatura"
                                           class="form-control"
                                           value="{{ old('ano_formatura', $egresso->ano_formatura) }}"
                                           min="1990" max="{{ date('Y') + 5 }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="nota_final" class="form-label fw-semibold">Nota Final (0-20)</label>
                                    <input type="number" name="nota_final" id="nota_final"
                                           class="form-control"
                                           value="{{ old('nota_final', $egresso->nota_final) }}"
                                           step="0.01" min="0" max="20">
                                </div>

                                <div class="col-12">
                                    <label for="observacoes" class="form-label fw-semibold">Observações</label>
                                    <textarea name="observacoes" id="observacoes" class="form-control" rows="3"
                                              placeholder="Informações adicionais sobre o seu percurso académico...">{{ old('observacoes', $egresso->observacoes) }}</textarea>
                                    <small class="text-muted">
                                        <i class="fas fa-eye-slash me-1"></i> Informações privadas.
                                    </small>
                                </div>

                            </div>
                        </div>


                        {{-- TAB 3 — LOCALIZAÇÃO --}}
                        <div class="tab-pane fade" id="pane-localizacao" role="tabpanel">
                            <div class="section-header mb-4">
                                <div class="section-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Localização Atual</h6>
                                    <small class="text-muted">Onde resides atualmente</small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="pais" class="form-label fw-semibold">País</label>
                                    <input type="text" name="pais" id="pais" class="form-control"
                                           value="{{ old('pais', $loc->pais ?? '') }}" placeholder="Ex: Angola">
                                </div>

                                <div class="col-md-6">
                                    <label for="provincia" class="form-label fw-semibold">Província / Região</label>
                                    <input type="text" name="provincia" id="provincia" class="form-control"
                                           value="{{ old('provincia', $loc->provincia ?? '') }}" placeholder="Ex: Luanda">
                                </div>

                                <div class="col-md-6">
                                    <label for="cidade" class="form-label fw-semibold">Cidade</label>
                                    <input type="text" name="cidade" id="cidade" class="form-control"
                                           value="{{ old('cidade', $loc->cidade ?? '') }}" placeholder="Ex: Luanda">
                                </div>

                                <div class="col-md-6">
                                    <label for="data_desde" class="form-label fw-semibold">A residir desde</label>
                                    <input type="date" name="data_desde" id="data_desde" class="form-control"
                                           value="{{ old('data_desde', $loc && $loc->data_desde ? \Carbon\Carbon::parse($loc->data_desde)->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-12">
                                    <label for="endereco" class="form-label fw-semibold">Endereço</label>
                                    <input type="text" name="endereco" id="endereco" class="form-control"
                                           value="{{ old('endereco', $loc->endereco ?? '') }}"
                                           placeholder="Ex: Rua 1, Bairro 2">
                                </div>

                                <div class="col-md-6">
                                    <label for="latitude" class="form-label fw-semibold">
                                        Latitude <small class="text-muted">(opcional)</small>
                                    </label>
                                    <input type="text" name="latitude" id="latitude" class="form-control"
                                           value="{{ old('latitude', $loc->latitude ?? '') }}" placeholder="Ex: -8.8383">
                                </div>

                                <div class="col-md-6">
                                    <label for="longitude" class="form-label fw-semibold">
                                        Longitude <small class="text-muted">(opcional)</small>
                                    </label>
                                    <input type="text" name="longitude" id="longitude" class="form-control"
                                           value="{{ old('longitude', $loc->longitude ?? '') }}" placeholder="Ex: 13.2344">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_current" id="is_current"
                                               class="form-check-input" value="1"
                                               {{ old('is_current', $loc->is_current ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_current">
                                            <i class="fas fa-map-pin me-1 text-info"></i>
                                            Esta é a minha localização atual
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>


                        {{-- TAB 4 — SITUAÇÃO PROFISSIONAL --}}
                        <div class="tab-pane fade" id="pane-profissional" role="tabpanel">
                            <div class="section-header mb-4">
                                <div class="section-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Situação Profissional</h6>
                                    <small class="text-muted">A sua carreira atual</small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label for="emprego_tipo" class="form-label fw-semibold">Tipo de Emprego</label>
                                    <select name="emprego_tipo" id="emprego_tipo" class="form-select">
                                        <option value="">Selecione</option>
                                        <option value="full_time"     {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'full_time' ? 'selected' : '' }}>Tempo Inteiro</option>
                                        <option value="part_time"     {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'part_time' ? 'selected' : '' }}>Tempo Parcial</option>
                                        <option value="freelance"     {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                                        <option value="self_employed" {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'self_employed' ? 'selected' : '' }}>Autónomo</option>
                                        <option value="unemployed"    {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'unemployed' ? 'selected' : '' }}>Desempregado</option>
                                        <option value="student"       {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'student' ? 'selected' : '' }}>A Estudar</option>
                                        <option value="unknown"       {{ old('emprego_tipo', $prof->tipo_emprego ?? '') == 'unknown' ? 'selected' : '' }}>Desconhecido</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="cargo" class="form-label fw-semibold">Cargo</label>
                                    <input type="text" name="cargo" id="cargo" class="form-control"
                                           value="{{ old('cargo', $prof->cargo ?? '') }}"
                                           placeholder="Ex: Engenheiro de Software">
                                </div>

                                <div class="col-md-4">
                                    <label for="empregador" class="form-label fw-semibold">Empregador</label>
                                    <input type="text" name="empregador" id="empregador" class="form-control"
                                           value="{{ old('empregador', $prof->empregador ?? '') }}"
                                           placeholder="Ex: Empresa ABC">
                                </div>

                                <div class="col-md-6">
                                    <label for="sector" class="form-label fw-semibold">Sector / Área de Atuação</label>
                                    <select name="sector" id="sector" class="form-select">
                                        <option value="">— Seleciona uma área —</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area }}"
                                                {{ old('sector', $prof->sector ?? '') == $area ? 'selected' : '' }}>
                                                {{ $area }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="data_inicio" class="form-label fw-semibold">Data de Início</label>
                                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                                           value="{{ old('data_inicio', $prof && $prof->data_inicio ? \Carbon\Carbon::parse($prof->data_inicio)->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="linkedin_url" class="form-label fw-semibold">LinkedIn</label>
                                    <input type="url" name="linkedin_url" id="linkedin_url" class="form-control"
                                           value="{{ old('linkedin_url', $prof->linkedin_url ?? '') }}"
                                           placeholder="https://linkedin.com/in/perfil">
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_current_emprego" id="is_current_emprego"
                                               class="form-check-input" value="1"
                                               {{ old('is_current_emprego', $prof->is_current ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_current_emprego">
                                
                                            Este é o meu emprego atual
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>


                        {{-- TAB 5 — SEGURANÇA --}}
                        <div class="tab-pane fade" id="pane-seguranca" role="tabpanel">
                            <div class="section-header mb-4">
                                <div class="section-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Segurança</h6>
                                    <small class="text-muted">Deixe em branco para manter a palavra-passe actual</small>
                                </div>
                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Nova Palavra-passe</label>
                                    <input type="password" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Mínimo 6 caracteres"
                                           autocomplete="new-password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">Confirmar</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-control"
                                           placeholder="Repita a nova palavra-passe"
                                           autocomplete="new-password">
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-info border-0 small mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        A palavra-passe deve ter no mínimo 6 caracteres.
                                        Se não quiser alterar, deixe ambos os campos em branco.
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 px-4">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">
                        <a href="{{ route('egresso.perfil') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar Alterações
                        </button>
                    </div>
                </div>

            </div>

        </form>

    </div>
</div>

@endsection

