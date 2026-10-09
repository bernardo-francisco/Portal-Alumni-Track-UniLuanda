@extends('layouts.egresso')

@section('title', 'Editar Localização - Egresso')

@section('page_title', '✏️ Editar Localização')
@section('page_subtitle', 'Actualize a sua localização')


@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">✏️ Editar Localização</h1>
            <p class="text-muted">Actualize a sua localização</p>
        </div>
        <a href="{{ route('egresso.localizacao') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 card-shadow">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Localização
                    </h5>
                    <small class="text-muted">Actualize os dados da sua localização</small>
                </div>
                @if($localizacao->is_current)
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i> Localização Atual
                    </span>
                @else
                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                        <i class="fas fa-clock me-1"></i> Localização Anterior
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body px-4 pb-4">
            <form method="POST" action="{{ route('egresso.localizacao.update', $localizacao->id) }}" id="formLocalizacao">
                @csrf
                @method('PUT')

                <!-- Botão de Localização Automática (para atualizar) -->
                <div class="mb-4">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary rounded-pill px-4 btn-location" id="btnLocalizacao">
                            <span class="location-spinner" id="spinnerLocalizacao">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                            </span>
                            <i class="fas fa-crosshairs me-2" id="iconLocalizacao"></i>
                            <span id="textLocalizacao">Atualizar com Localização Atual</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-location" id="btnBuscarEndereco">
                            <i class="fas fa-search me-2"></i> Buscar por Endereço
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i> 
                        Clique em "Atualizar com Localização Atual" para preencher com a sua localização atual
                    </small>
                </div>

                <!-- Grid de Campos - Layout Horizontal -->
                <div class="row g-3">
                    <!-- Coluna Esquerda - Endereço -->
                    <div class="col-lg-6">
                        <div class="form-section-title">
                            <i class="fas fa-address-card me-2"></i> Endereço
                        </div>

                        <!-- País -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">País <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="pais" 
                                   id="pais"
                                   class="form-control @error('pais') is-invalid @enderror" 
                                   value="{{ old('pais', $localizacao->pais) }}" 
                                   required>
                            @error('pais')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Província -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Província</label>
                            <input type="text" 
                                   name="provincia" 
                                   id="provincia"
                                   class="form-control @error('provincia') is-invalid @enderror" 
                                   value="{{ old('provincia', $localizacao->provincia) }}">
                            @error('provincia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cidade -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Cidade</label>
                            <input type="text" 
                                   name="cidade" 
                                   id="cidade"
                                   class="form-control @error('cidade') is-invalid @enderror" 
                                   value="{{ old('cidade', $localizacao->cidade) }}">
                            @error('cidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Endereço -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Endereço</label>
                            <input type="text" 
                                   name="endereco" 
                                   id="endereco"
                                   class="form-control @error('endereco') is-invalid @enderror" 
                                   value="{{ old('endereco', $localizacao->endereco) }}">
                            @error('endereco')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Coluna Direita - Coordenadas -->
                    <div class="col-lg-6">
                        <div class="form-section-title">
                            <i class="fas fa-globe me-2"></i> Coordenadas
                        </div>

                        <!-- Latitude -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Latitude</label>
                            <input type="text" 
                                   name="latitude" 
                                   id="latitude"
                                   class="form-control @error('latitude') is-invalid @enderror" 
                                   value="{{ old('latitude', $localizacao->latitude) }}" 
                                   placeholder="Ex: -8.8383">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Longitude -->
                        <div class="form-group mb-3">
                            <label class="fw-semibold">Longitude</label>
                            <input type="text" 
                                   name="longitude" 
                                   id="longitude"
                                   class="form-control @error('longitude') is-invalid @enderror" 
                                   value="{{ old('longitude', $localizacao->longitude) }}" 
                                   placeholder="Ex: 13.2344">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Data e Current -->
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="fw-semibold">Desde</label>
                                    <input type="date" 
                                           name="data_desde" 
                                           class="form-control @error('data_desde') is-invalid @enderror" 
                                           value="{{ old('data_desde', $localizacao->data_desde ?? date('Y-m-d')) }}">
                                    @error('data_desde')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group d-flex align-items-center" style="height: 100%; padding-top: 8px;">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="is_current" 
                                               id="is_current" 
                                               class="form-check-input" 
                                               value="1" 
                                               {{ old('is_current', $localizacao->is_current) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_current">
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Atual
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coordenadas Info -->
                        <div class="coords-info {{ old('latitude', $localizacao->latitude) && old('longitude', $localizacao->longitude) ? 'active' : '' }}" id="coordsInfo">
                            <div class="bg-light rounded-3 p-3 mt-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-map-pin text-primary"></i>
                                    <span class="text-muted">
                                        <strong>Coordenadas:</strong> 
                                        <span id="coordsLat">{{ old('latitude', $localizacao->latitude) ?: '---' }}</span>, 
                                        <span id="coordsLng">{{ old('longitude', $localizacao->longitude) ?: '---' }}</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-auto" onclick="limparCoordenadas()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Pré-visualização (Largura Total) -->
                <div class="mt-4">
                    <div class="form-section-title">
                        <i class="fas fa-map me-2"></i> Pré-visualização do Mapa
                    </div>
                    <div class="map-preview" id="mapPreview">
                        @if($localizacao->latitude && $localizacao->longitude)
                            @php
                                $lat = $localizacao->latitude;
                                $lng = $localizacao->longitude;
                            @endphp
                            <iframe 
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lng-0.05 }}%2C{{ $lat-0.05 }}%2C{{ $lng+0.05 }}%2C{{ $lat+0.05 }}&amp;layer=mapnik&amp;marker={{ $lat }}%2C{{ $lng }}"
                                allowfullscreen>
                            </iframe>
                            <div class="map-overlay">
                                <i class="fas fa-map-pin text-primary"></i> {{ number_format($lat, 4) }}, {{ number_format($lng, 4) }}
                            </div>
                        @else
                            <i class="fas fa-map-marked-alt fa-3x mb-2"></i>
                            <p class="mb-0">Pré-visualização do mapa</p>
                            <small class="text-muted">As coordenadas aparecerão aqui</small>
                        @endif
                    </div>
                </div>

                <!-- Botões -->
                <div class="d-flex gap-3 mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-save me-2"></i> Actualizar
                    </button>
                    <a href="{{ route('egresso.localizacao') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection