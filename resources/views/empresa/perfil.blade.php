@extends('layouts.empresa')

@section('title', 'Perfil da Empresa')
@section('page-title', 'Perfil da Empresa')

@section('content')
<div class="row g-3">

    {{-- COLUNA ESQUERDA: Formulário --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-gradient-primary text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i> Editar Dados da Empresa</h6>
            </div>

            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger border-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('empresa.perfil.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nome da Empresa <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                                <input type="text" name="nome" class="form-control border-start-0" required
                                       value="{{ old('nome', $empresa->nome) }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">NIF</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" value="{{ $empresa->nif }}" disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0" value="{{ $empresa->email }}" disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                <input type="text" name="telefone" class="form-control border-start-0"
                                       value="{{ old('telefone', $empresa->telefone) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Website</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-globe text-muted"></i></span>
                                <input type="url" name="website" class="form-control border-start-0"
                                       value="{{ old('website', $empresa->website) }}"
                                       placeholder="https://suaempresa.ao">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sector <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-industry text-muted"></i></span>
                                <select name="sector" class="form-select border-start-0" required>
                                    <option value="">Selecione...</option>
                                    @php
                                        $sectores = ['Tecnologia', 'Banca', 'Saúde', 'Educação', 'Construção', 'Consultoria', 'Outro'];
                                    @endphp
                                    @foreach($sectores as $s)
                                        <option value="{{ $s }}" {{ old('sector', $empresa->sector) == $s ? 'selected' : '' }}>
                                            {{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Localização</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" name="localizacao" class="form-control border-start-0"
                                       value="{{ old('localizacao', $empresa->localizacao) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Província</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-map text-muted"></i></span>
                                <input type="text" name="provincia" class="form-control border-start-0"
                                       value="{{ old('provincia', $empresa->provincia) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="4"
                                      placeholder="Descreva a sua empresa...">{{ old('descricao', $empresa->descricao) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Logo da Empresa</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">JPG, PNG ou JPEG. Máx: 2MB. Deixe vazio para manter o logo atual.</small>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('empresa.dashboard') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i> Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- COLUNA DIREITA --}}
    <div class="col-lg-4">

        {{-- Logo --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center p-4">

                @if($empresa->logo_url)
                    <img src="{{ asset($empresa->logo_url) }}"
                         alt="Logótipo de {{ $empresa->nome }}"
                         class="rounded-3 mb-3"
                         style="max-height: 130px; max-width: 100%; object-fit: contain;">
                @else
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 120px; height: 120px;">
                        <i class="fas fa-building fa-3x"></i>
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $empresa->nome }}</h5>
                <p class="text-muted small mb-3">{{ $empresa->sector }}</p>

                @php
                    $coresStatus = [
                        'pendente'  => ['bg' => 'warning', 'icon' => 'clock'],
                        'aprovado'  => ['bg' => 'success', 'icon' => 'check-circle'],
                        'reprovado' => ['bg' => 'danger', 'icon' => 'times-circle'],
                    ];
                    $status = $coresStatus[$empresa->status_validacao] ?? ['bg' => 'secondary', 'icon' => 'info-circle'];
                @endphp

                <span class="badge bg-{{ $status['bg'] }}-subtle text-{{ $status['bg'] }}-emphasis border border-{{ $status['bg'] }} px-3 py-2">
                    <i class="fas fa-{{ $status['icon'] }} me-1"></i>
                    {{ ucfirst($empresa->status_validacao) }}
                </span>
            </div>
        </div>

        {{-- Info Adicional --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i> Informações</h6>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar text-muted me-2"></i>Registada em:</span>
                    <span class="info-value">{{ $empresa->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-tag text-muted me-2"></i>Tipo:</span>
                    <span class="info-value"><span class="badge bg-secondary">{{ ucfirst($empresa->tipo) }}</span></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-check text-muted me-2"></i>Ativo:</span>
                    <span class="info-value">
                        @if($empresa->ativo)
                            <span class="badge bg-success-subtle text-success">Sim</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Não</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
