@extends('layouts.egresso')

@section('title', 'Adicionar Localização')

@section('page_title', '📍 Adicionar Localização')
@section('page_subtitle', 'Registe a sua localização atual')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">

        {{-- ============================================================
             INTRO CARD
        ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4 location-intro-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3">

                    <div class="location-intro-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">Onde está agora?</h5>
                        <p class="text-muted small mb-0">
                            Use a <strong>localização automática</strong> ou preencha manualmente.
                            A sua localização ajuda outros egressos a encontrarem-no na rede.
                        </p>
                    </div>

                </div>
            </div>
        </div>


        {{-- ============================================================
             FORMULÁRIO
        ============================================================ --}}
        <form method="POST" action="{{ route('egresso.localizacao.store') }}" id="formLocalizacao">
            @csrf

            {{-- ============================================================
                 BOTÕES DE LOCALIZAÇÃO RÁPIDA
            ============================================================ --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="section-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Preenchimento Automático</h6>
                            <small class="text-muted">Preencha os campos com um clique</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="quick-action-btn quick-action-primary" id="btnLocalizacao">
                                <div class="quick-action-icon">
                                    <span class="location-spinner" id="spinnerLocalizacao">
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </span>
                                    <i class="fas fa-crosshairs" id="iconLocalizacao"></i>
                                </div>
                                <div class="quick-action-text">
                                    <strong id="textLocalizacao">Usar Localização Atual</strong>
                                    <small>Deteta automaticamente via GPS</small>
                                </div>
                            </button>
                        </div>

                        <div class="col-md-6">
                            <button type="button" class="quick-action-btn quick-action-secondary" id="btnBuscarEndereco">
                                <div class="quick-action-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="quick-action-text">
                                    <strong>Buscar por Endereço</strong>
                                    <small>Encontra coordenadas pelo nome</small>
                                </div>
                            </button>
                        </div>
                    </div>

                </div>
            </div>


            <div class="row g-4">

                {{-- ============================================================
                     COLUNA ESQUERDA — ENDEREÇO
                ============================================================ --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-address-card"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Endereço</h6>
                                    <small class="text-muted">Informação da sua localização</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">

                            {{-- País --}}
                            <div class="mb-3">
                                <label for="pais" class="form-label fw-semibold">
                                    <i class="fas fa-globe text-muted me-1"></i>
                                    País <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="pais"
                                       id="pais"
                                       class="form-control @error('pais') is-invalid @enderror"
                                       value="{{ old('pais', 'Angola') }}"
                                       placeholder="Ex: Angola"
                                       required>
                                @error('pais')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Província --}}
                            <div class="mb-3">
                                <label for="provincia" class="form-label fw-semibold">
                                    <i class="fas fa-map text-muted me-1"></i>
                                    Província
                                </label>
                                <input type="text"
                                       name="provincia"
                                       id="provincia"
                                       class="form-control @error('provincia') is-invalid @enderror"
                                       value="{{ old('provincia') }}"
                                       placeholder="Ex: Luanda">
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cidade --}}
                            <div class="mb-3">
                                <label for="cidade" class="form-label fw-semibold">
                                    <i class="fas fa-city text-muted me-1"></i>
                                    Cidade
                                </label>
                                <input type="text"
                                       name="cidade"
                                       id="cidade"
                                       class="form-control @error('cidade') is-invalid @enderror"
                                       value="{{ old('cidade') }}"
                                       placeholder="Ex: Luanda">
                                @error('cidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Endereço --}}
                            <div class="mb-3">
                                <label for="endereco" class="form-label fw-semibold">
                                    <i class="fas fa-home text-muted me-1"></i>
                                    Endereço
                                </label>
                                <input type="text"
                                       name="endereco"
                                       id="endereco"
                                       class="form-control @error('endereco') is-invalid @enderror"
                                       value="{{ old('endereco') }}"
                                       placeholder="Ex: Rua 1, Bairro 2">
                                @error('endereco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>


                {{-- ============================================================
                     COLUNA DIREITA — COORDENADAS
                ============================================================ --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-crosshairs"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Coordenadas</h6>
                                    <small class="text-muted">Localização geográfica</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="latitude" class="form-label fw-semibold">
                                        Latitude
                                    </label>
                                    <input type="text"
                                           name="latitude"
                                           id="latitude"
                                           class="form-control font-monospace @error('latitude') is-invalid @enderror"
                                           value="{{ old('latitude') }}"
                                           placeholder="-8.8383">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="longitude" class="form-label fw-semibold">
                                        Longitude
                                    </label>
                                    <input type="text"
                                           name="longitude"
                                           id="longitude"
                                           class="form-control font-monospace @error('longitude') is-invalid @enderror"
                                           value="{{ old('longitude') }}"
                                           placeholder="13.2344">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Info coordenadas --}}
                            <div class="coords-info-box d-none mb-3" id="coordsInfo">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="text-muted small">
                                        Coordenadas: <code id="coordsLat">---</code>, <code id="coordsLng">---</code>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-link text-danger ms-auto p-0" onclick="limparCoordenadas()">
                                        <i class="fas fa-times me-1"></i> Limpar
                                    </button>
                                </div>
                            </div>

                            {{-- Data + Atual --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="data_desde" class="form-label fw-semibold">
                                        <i class="fas fa-calendar text-muted me-1"></i> Desde
                                    </label>
                                    <input type="date"
                                           name="data_desde"
                                           id="data_desde"
                                           class="form-control @error('data_desde') is-invalid @enderror"
                                           value="{{ old('data_desde', date('Y-m-d')) }}">
                                    @error('data_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox"
                                               name="is_current"
                                               id="is_current"
                                               class="form-check-input"
                                               value="1"
                                               {{ old('is_current', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_current">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Localização atual
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>


            {{-- ============================================================
                 MAPA DE PRÉ-VISUALIZAÇÃO
            ============================================================ --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-map"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Pré-visualização do Mapa</h6>
                            <small class="text-muted">As coordenadas aparecerão aqui</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="map-preview" id="mapPreview">
                        <div class="map-placeholder">
                            <div class="map-placeholder-icon">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Sem localização</h6>
                            <p class="text-muted small mb-0">
                                Preencha as coordenadas ou use a localização automática
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ============================================================
                 AÇÕES
            ============================================================ --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-3">
                    <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center">

                        <a href="{{ route('egresso.localizacao') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>

                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Guardar Localização
                        </button>

                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection

