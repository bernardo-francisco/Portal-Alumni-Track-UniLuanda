@extends('layouts.egresso')

@section('title', 'Adicionar Registo Profissional - Egresso')

@section('content')

<div class="container-fluid py-4">

    <!-- ============================================================
         HEADER
    ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-0">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                Adicionar Registo Profissional
            </h1>
            <p class="text-muted small mb-0">
                Preencha os dados da sua experiência profissional
            </p>
        </div>
        <a href="{{ route('egresso.profissional') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
    </div>

    <!-- ============================================================
         FORMULÁRIO - LAYOUT HORIZONTAL
    ============================================================ -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <form method="POST" action="{{ route('egresso.profissional.store') }}">
                @csrf

                <!-- Linha 1: Cargo + Empregador -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="cargo" class="form-label fw-semibold">
                            <i class="fas fa-briefcase text-primary me-1"></i>
                            Cargo <span class="text-muted">(opcional)</span>
                        </label>
                        <input type="text" 
                               name="cargo" 
                               id="cargo"
                               class="form-control form-control-lg rounded-3 @error('cargo') is-invalid @enderror" 
                               value="{{ old('cargo') }}" 
                               placeholder="Ex: Engenheiro de Software">
                        @error('cargo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="empregador" class="form-label fw-semibold">
                            <i class="fas fa-building text-primary me-1"></i>
                            Empregador <span class="text-muted">(opcional)</span>
                        </label>
                        <input type="text" 
                               name="empregador" 
                               id="empregador"
                               class="form-control form-control-lg rounded-3 @error('empregador') is-invalid @enderror" 
                               value="{{ old('empregador') }}" 
                               placeholder="Ex: Empresa ABC">
                        @error('empregador')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Linha 2: Sector + Tipo de Emprego -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sector" class="form-label fw-semibold">
                            <i class="fas fa-tags text-primary me-1"></i>
                            Sector <span class="text-muted">(opcional)</span>
                        </label>
                        <input type="text" 
                               name="sector" 
                               id="sector"
                               class="form-control form-control-lg rounded-3 @error('sector') is-invalid @enderror" 
                               value="{{ old('sector') }}" 
                               placeholder="Ex: Tecnologia, Saúde">
                        @error('sector')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tipo_emprego" class="form-label fw-semibold">
                            <i class="fas fa-clock text-primary me-1"></i>
                            Tipo de Emprego <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_emprego" 
                                id="tipo_emprego"
                                class="form-select form-select-lg rounded-3 @error('tipo_emprego') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            <option value="full_time" {{ old('tipo_emprego') == 'full_time' ? 'selected' : '' }}>Tempo Inteiro</option>
                            <option value="part_time" {{ old('tipo_emprego') == 'part_time' ? 'selected' : '' }}>Tempo Parcial</option>
                            <option value="freelance" {{ old('tipo_emprego') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                            <option value="self_employed" {{ old('tipo_emprego') == 'self_employed' ? 'selected' : '' }}>Autónomo</option>
                            <option value="unemployed" {{ old('tipo_emprego') == 'unemployed' ? 'selected' : '' }}>Desempregado</option>
                            <option value="student" {{ old('tipo_emprego') == 'student' ? 'selected' : '' }}>A Estudar</option>
                            <option value="unknown" {{ old('tipo_emprego') == 'unknown' ? 'selected' : '' }}>Desconhecido</option>
                        </select>
                        @error('tipo_emprego')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Linha 3: Data Início + Data Fim -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                            Data de Início
                        </label>
                        <input type="date" 
                               name="data_inicio" 
                               id="data_inicio"
                               class="form-control form-control-lg rounded-3 @error('data_inicio') is-invalid @enderror" 
                               value="{{ old('data_inicio') }}">
                        @error('data_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="data_fim" class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                            Data de Fim
                        </label>
                        <input type="date" 
                               name="data_fim" 
                               id="data_fim"
                               class="form-control form-control-lg rounded-3 @error('data_fim') is-invalid @enderror" 
                               value="{{ old('data_fim') }}">
                        <small class="text-muted">Deixe em branco se for atual</small>
                        @error('data_fim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Linha 4: LinkedIn + Emprego Atual (Switch) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="linkedin_url" class="form-label fw-semibold">
                            <i class="fab fa-linkedin text-primary me-1"></i>
                            LinkedIn <span class="text-muted">(opcional)</span>
                        </label>
                        <input type="url" 
                               name="linkedin_url" 
                               id="linkedin_url"
                               class="form-control form-control-lg rounded-3 @error('linkedin_url') is-invalid @enderror" 
                               value="{{ old('linkedin_url') }}" 
                               placeholder="https://linkedin.com/in/seu-perfil">
                        @error('linkedin_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" 
                                   name="is_current" 
                                   id="is_current"
                                   class="form-check-input" 
                                   value="1"
                                   style="width: 40px; height: 20px; cursor: pointer;"
                                   {{ old('is_current') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold ms-2" for="is_current">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Emprego Atual
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ============================================================
                     BOTÕES - TAMANHO NORMAL
                ============================================================ -->
                <div class="d-flex gap-3">
                    <a href="{{ route('egresso.profissional') }}" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-save me-2"></i>
                        Salvar Registo
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection