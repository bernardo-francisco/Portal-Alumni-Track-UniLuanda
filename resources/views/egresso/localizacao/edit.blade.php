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
                        Clique no mapa para capturar coordenadas ou use "Atualizar com Localização Atual"
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
                                           value="{{ old('data_desde', optional($localizacao->data_desde)->format('Y-m-d') ?? date('Y-m-d')) }}">
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

                <!-- Mapa Interativo (Largura Total) -->
                <div class="mt-4">
                    <div class="form-section-title">
                        <i class="fas fa-map me-2"></i> Mapa Interativo
                    </div>
                    <div class="map-preview" id="mapPreview" style="height: 420px; position: relative;">
                        <div id="mapaInterativo" style="width: 100%; height: 100%;"></div>
                    </div>
                    <div class="bg-light rounded-3 p-3 mt-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="fas fa-crosshairs text-primary"></i>
                            <span class="text-muted small font-monospace" id="coordsFeedback">
                                Nenhuma coordenada selecionada
                            </span>
                            <span class="badge bg-light text-muted border ms-auto">
                                <i class="fas fa-mouse-pointer me-1"></i> Clique no mapa para marcar
                            </span>
                        </div>
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

@push('scripts')
<script>
(function () {
    'use strict';

    const API_KEY = @json($googleApiKey ?? '');
    const MAP_ID  = @json($googleMapId ?? '');

    // Coordenadas iniciais (do model, ou default Luanda)
    const INIT_LAT = {{ $localizacao->latitude ?? -8.8383 }};
    const INIT_LNG = {{ $localizacao->longitude ?? 13.2344 }};
    const HAS_COORDS = {{ ($localizacao->latitude && $localizacao->longitude) ? 'true' : 'false' }};

    let map = null;
    let marker = null;

    // -------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------
    function setFeedback(lat, lng) {
        const fb = document.getElementById('coordsFeedback');
        if (!fb) return;
        if (lat == null || lng == null) {
            fb.textContent = 'Nenhuma coordenada selecionada';
            fb.classList.add('text-muted');
            fb.classList.remove('text-success');
        } else {
            fb.textContent = `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`;
            fb.classList.add('text-success');
            fb.classList.remove('text-muted');
        }
    }

    function setCoordsInfo(lat, lng) {
        const info = document.getElementById('coordsInfo');
        const cl = document.getElementById('coordsLat');
        const cg = document.getElementById('coordsLng');
        if (!info) return;
        if (lat && lng) {
            if (cl) cl.textContent = Number(lat).toFixed(6);
            if (cg) cg.textContent = Number(lng).toFixed(6);
            info.classList.add('active');
        } else {
            if (cl) cl.textContent = '---';
            if (cg) cg.textContent = '---';
            info.classList.remove('active');
        }
    }

    function aplicarCoordenadas(lat, lng, moverMapa = true) {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (latInput) latInput.value = Number(lat).toFixed(6);
        if (lngInput) lngInput.value = Number(lng).toFixed(6);

        setFeedback(lat, lng);
        setCoordsInfo(lat, lng);

        if (map && moverMapa) map.setCenter({ lat: Number(lat), lng: Number(lng) });

        if (marker) {
            marker.setPosition({ lat: Number(lat), lng: Number(lng) });
        } else if (map) {
            marker = new google.maps.Marker({
                position: { lat: Number(lat), lng: Number(lng) },
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
            });
            marker.addListener('dragend', (e) => {
                aplicarCoordenadas(e.latLng.lat(), e.latLng.lng(), false);
                reverseGeocode(e.latLng.lat(), e.latLng.lng());
            });
        }

        reverseGeocode(Number(lat), Number(lng));
    }

    // -------------------------------------------------------------
    // Geocoding com Fallback (Google -> OpenStreetMap)
    // -------------------------------------------------------------
    function reverseGeocode(lat, lng) {
        const paisInput = document.getElementById('pais');
        const provInput = document.getElementById('provincia');
        const cidInput  = document.getElementById('cidade');
        const endInput  = document.getElementById('endereco');

        // Tenta o Google Maps
        if (window.google && google.maps.Geocoder) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat, lng } }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    const comps = results[0].address_components;
                    const get = (tipo) => {
                        const c = comps.find(c => c.types.includes(tipo));
                        return c ? c.long_name : '';
                    };
                    
                    const pais = get('country');
                    const provincia = get('administrative_area_level_1');
                    const cidade = get('locality') || get('administrative_area_level_2');
                    const rua = get('route');
                    const numero = get('street_number');
                    const endereco = [rua, numero].filter(Boolean).join(', ');

                    // ✅ Força o preenchimento (substitui os valores atuais)
                    if (paisInput && pais) paisInput.value = pais;
                    if (provInput && provincia) provInput.value = provincia;
                    if (cidInput && cidade) cidInput.value = cidade;
                    if (endInput && endereco) endInput.value = endereco;
                    
                    console.log('✅ Preenchido via Google Maps');
                } else {
                    console.warn('Google Geocoding falhou (', status, '). A tentar Nominatim (OSM)...');
                    fallbackNominatim(lat, lng);
                }
            });
        } else {
            fallbackNominatim(lat, lng);
        }
    }

    function fallbackNominatim(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=pt`;

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.address) return;
            
            const end = data.address;
            const paisInput = document.getElementById('pais');
            const provInput = document.getElementById('provincia');
            const cidInput  = document.getElementById('cidade');
            const endInput  = document.getElementById('endereco');

                        if (paisInput && end.country) paisInput.value = end.country;
            if (provInput && end.state) provInput.value = end.state;
            
            // ✅ Tenta apanhar a cidade em vários campos possíveis do OSM
            if (cidInput) {
                const cidadeNome = end.city || end.town || end.village || end.municipality || end.county || end.administrative;
                if (cidadeNome) cidInput.value = cidadeNome;
            }
            
            if (endInput) {
                const rua = end.road || '';
                const numero = end.house_number || '';
                const bairro = end.suburb || end.neighbourhood || '';
                const partes = [rua, numero, bairro].filter(Boolean);
                if (partes.length > 0) endInput.value = partes.join(', ');
            }
            
            console.log('✅ Preenchido via OpenStreetMap (Nominatim) - Modo Edição');
        })
        .catch(err => {
            console.error('❌ Falha no fallback Nominatim:', err);
        });
    }

    // -------------------------------------------------------------
    // Init do mapa
    // -------------------------------------------------------------
    window.initMapaInterativo = function () {
        const el = document.getElementById('mapaInterativo');
        if (!el) return;

        map = new google.maps.Map(el, {
            center: { lat: INIT_LAT, lng: INIT_LNG },
            zoom: HAS_COORDS ? 15 : 12,
            mapId: MAP_ID || undefined,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        // Coloca o marcador se já houver coordenadas
        if (HAS_COORDS) {
            aplicarCoordenadas(INIT_LAT, INIT_LNG);
        } else {
            setFeedback(null, null);
            setCoordsInfo(null, null);
        }

        // Clique no mapa → captura coordenadas
        map.addListener('click', (e) => {
            aplicarCoordenadas(e.latLng.lat(), e.latLng.lng(), false);
        });

        // Sincronizar inputs → mapa
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        [latInput, lngInput].forEach(inp => {
            if (!inp) return;
            inp.addEventListener('change', () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    aplicarCoordenadas(lat, lng);
                }
            });
        });
    };

    // -------------------------------------------------------------
    // Botão "Atualizar com Localização Atual"
    // -------------------------------------------------------------
    window.usarMinhaLocalizacao = function () {
        const spinner = document.getElementById('spinnerLocalizacao');
        const icon    = document.getElementById('iconLocalizacao');
        const text    = document.getElementById('textLocalizacao');

        if (spinner) spinner.style.display = 'inline-block';
        if (icon)    icon.style.display = 'none';
        if (text)    text.textContent = 'A obter localização...';

        const restaurar = () => {
            if (spinner) spinner.style.display = 'none';
            if (icon)    icon.style.display = 'inline-block';
            if (text)    text.textContent = 'Atualizar com Localização Atual';
        };

        if (!navigator.geolocation) {
            alert('O seu browser não suporta geolocalização.');
            restaurar();
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (map) {
                    map.setCenter({ lat, lng });
                    map.setZoom(15);
                }
                aplicarCoordenadas(lat, lng, true);
                restaurar();
            },
            (err) => {
                alert('Não foi possível obter a localização: ' + err.message);
                restaurar();
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    };

    // -------------------------------------------------------------
    // Botão "Buscar por Endereço"
    // -------------------------------------------------------------
    window.buscarPorEndereco = function () {
        const partes = [
            document.getElementById('endereco')?.value,
            document.getElementById('cidade')?.value,
            document.getElementById('provincia')?.value,
            document.getElementById('pais')?.value,
        ].filter(Boolean);

        if (!partes.length) {
            alert('Preencha pelo menos o país ou o endereço para buscar.');
            return;
        }

        const endereco = partes.join(', ');
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: endereco }, function (results, status) {
            if (status !== 'OK' || !results[0]) {
                alert('Endereço não encontrado.');
                return;
            }
            const loc = results[0].geometry.location;
            if (map) {
                map.setCenter(loc);
                map.setZoom(15);
            }
            aplicarCoordenadas(loc.lat(), loc.lng(), false);
        });
    };

    // -------------------------------------------------------------
    // Limpar coordenadas
    // -------------------------------------------------------------
    window.limparCoordenadas = function () {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        if (latInput) latInput.value = '';
        if (lngInput) lngInput.value = '';

        setFeedback(null, null);
        setCoordsInfo(null, null);

        if (marker) { marker.setMap(null); marker = null; }
    };

    // -------------------------------------------------------------
    // Ligar botões
    // -------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnLocalizacao')?.addEventListener('click', window.usarMinhaLocalizacao);
        document.getElementById('btnBuscarEndereco')?.addEventListener('click', window.buscarPorEndereco);
    });

    // -------------------------------------------------------------
    // Carregar Google Maps
    // -------------------------------------------------------------
    if (API_KEY) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${API_KEY}&libraries=places&callback=initMapaInterativo`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    } else {
        document.getElementById('mapaInterativo').innerHTML =
            '<div class="d-flex align-items-center justify-content-center h-100 text-muted">' +
            '<div class="text-center"><i class="fas fa-map-marked-alt fa-3x mb-2 d-block"></i>' +
            '<small>Mapa indisponível (chave API não configurada)</small></div></div>';
    }
})();
</script>
@endpush