@extends('layouts.egresso')

@section('title', isset($profissional) ? 'Editar Registo Proficionais - Egresso' : 'Adicionar Registo Proficional - Egresso')

@section('content')
    

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('egresso.dashboard') }}">
                <i class="fas fa-graduation-cap"></i> UniLuanda Alumni
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ isset($profissional) ? 'Editar' : 'Adicionar' }} Registo Profissional</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ isset($profissional) ? route('egresso.profissional.update', $profissional->id) : route('egresso.profissional.store') }}">
                            @csrf
                            @if(isset($profissional)) @method('PUT') @endif

                            <div class="mb-3">
                                <label>Cargo</label>
                                <input type="text" name="cargo" class="form-control" value="{{ old('cargo', $profissional->cargo ?? '') }}" placeholder="Ex: Engenheiro de Software">
                            </div>

                            <div class="mb-3">
                                <label>Empregador</label>
                                <input type="text" name="empregador" class="form-control" value="{{ old('empregador', $profissional->empregador ?? '') }}" placeholder="Ex: Empresa ABC">
                            </div>

                            <div class="mb-3">
                                <label>Sector</label>
                                <input type="text" name="sector" class="form-control" value="{{ old('sector', $profissional->sector ?? '') }}" placeholder="Ex: Tecnologia">
                            </div>

                            <div class="mb-3">
                                <label>Tipo de Emprego</label>
                                <select name="tipo_emprego" class="form-select">
                                    <option value="full_time" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'full_time' ? 'selected' : '' }}>Tempo Inteiro</option>
                                    <option value="part_time" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'part_time' ? 'selected' : '' }}>Tempo Parcial</option>
                                    <option value="freelance" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'freelance' ? 'selected' : '' }}>Freelance</option>
                                    <option value="self_employed" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'self_employed' ? 'selected' : '' }}>Autónomo</option>
                                    <option value="unemployed" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'unemployed' ? 'selected' : '' }}>Desempregado</option>
                                    <option value="student" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'student' ? 'selected' : '' }}>A Estudar</option>
                                    <option value="unknown" {{ old('tipo_emprego', $profissional->tipo_emprego ?? '') == 'unknown' ? 'selected' : '' }}>Desconhecido</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Data de Início</label>
                                <input type="date" name="data_inicio" class="form-control" value="{{ old('data_inicio', $profissional->data_inicio ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label>Data de Fim (deixe em branco se for atual)</label>
                                <input type="date" name="data_fim" class="form-control" value="{{ old('data_fim', $profissional->data_fim ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label>LinkedIn</label>
                                <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $profissional->linkedin_url ?? '') }}" placeholder="https://linkedin.com/in/perfil">
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_current" class="form-check-input" id="is_current" value="1" {{ old('is_current', $profissional->is_current ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_current">Emprego atual</label>
                            </div>

                            <div class="mb-3">
                                <label>Observações</label>
                                <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais...">{{ old('observacoes', $profissional->observacoes ?? '') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('egresso.profissional') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection