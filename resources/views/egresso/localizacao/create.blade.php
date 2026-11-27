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
                            Use a <strong>localização automática</strong>, clique no <strong>mapa interativo</strong>
                            ou preencha manualmente. A sua localização ajuda outros egressos a encontrarem-no na rede.
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
                 MAPA INTERATIVO (substitui o placeholder)
            ============================================================ --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="section-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-map"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Mapa Interativo</h6>
                                <small class="text-muted">Clique no mapa para capturar as coordenadas</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-muted border">
                            <i class="fas fa-mouse-pointer me-1"></i> Clique para marcar
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="map-preview" id="mapPreview" style="height: 420px;">
                        <div id="mapaInterativo" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-2 px-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small">
                        <div>
                            <i class="fas fa-crosshairs text-primary me-1"></i>
                            <span id="coordsFeedback" class="text-muted font-monospace">
                                Nenhuma coordenada selecionada
                            </span>
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

@push('scripts')
<script>
(function () {
    'use strict';

    const API_KEY = @json($googleApiKey ?? '');
    const MAP_ID  = @json($googleMapId ?? '');
    const DEFAULT_LAT = -8.8383;
    const DEFAULT_LNG = 13.2344;

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
            info.classList.remove('d-none');
        } else {
            if (cl) cl.textContent = '---';
            if (cg) cg.textContent = '---';
            info.classList.add('d-none');
        }
    }

    // Preenche inputs + feedback + coordsInfo
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

    // Reverse geocoding: preenche país/província/cidade se estiverem vazios
        // Reverse geocoding com Fallback (Google -> OpenStreetMap)
    function reverseGeocode(lat, lng) {
        const paisInput = document.getElementById('pais');
        const provInput = document.getElementById('provincia');
        const cidInput  = document.getElementById('cidade');
        const endInput  = document.getElementById('endereco');

        // 1. Tenta usar o Google Maps
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

                    // ✅ Força o preenchimento
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
            // Se o Google nem estiver carregado, usa o OSM
            fallbackNominatim(lat, lng);
        }
    }

    // 2. Fallback: API Gratuita do OpenStreetMap
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
            
            console.log('✅ Preenchido via OpenStreetMap (Nominatim)');
        })
        .catch(err => {
            console.error('❌ Falha no fallback Nominatim:', err);
        });
    }

    // -------------------------------------------------------------
    // Init do mapa (chamado pelo callback do Google Maps)
    // -------------------------------------------------------------
    window.initMapaInterativo = function () {
        const el = document.getElementById('mapaInterativo');
        if (!el) return;

        map = new google.maps.Map(el, {
            center: { lat: DEFAULT_LAT, lng: DEFAULT_LNG },
            zoom: 12,
            mapId: MAP_ID || undefined,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
        });

        // Se já existirem coordenadas (old), coloca o marcador
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        if (latInput?.value && lngInput?.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                aplicarCoordenadas(lat, lng);
            }
        }

        // ✅ Clique no mapa → captura coordenadas
        map.addListener('click', (e) => {
            aplicarCoordenadas(e.latLng.lat(), e.latLng.lng(), false);
        });

        // Sincronizar inputs → mapa (se o utilizador escrever manualmente)
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
    // Botão "Usar Localização Atual"
    // -------------------------------------------------------------
    window.usarMinhaLocalizacao = function () {
        const spinner = document.getElementById('spinnerLocalizacao');
        const icon    = document.getElementById('iconLocalizacao');
        const text    = document.getElementById('textLocalizacao');

        if (spinner) spinner.style.display = 'inline-block';
        if (icon)    icon.style.display = 'none';
        if (text)    text.textContent = 'A obter localização...';

        if (!navigator.geolocation) {
            alert('O seu browser não suporta geolocalização.');
            if (spinner) spinner.style.display = 'none';
            if (icon)    icon.style.display = 'inline-block';
            if (text)    text.textContent = 'Usar Localização Atual';
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

                if (spinner) spinner.style.display = 'none';
                if (icon)    icon.style.display = 'inline-block';
                if (text)    text.textContent = 'Usar Localização Atual';
            },
            (err) => {
                alert('Não foi possível obter a localização: ' + err.message);
                if (spinner) spinner.style.display = 'none';
                if (icon)    icon.style.display = 'inline-block';
                if (text)    text.textContent = 'Usar Localização Atual';
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
    // Limpar coordenadas (função global usada no botão)
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
    // Ligar os botões existentes
    // -------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnLocalizacao')?.addEventListener('click', window.usarMinhaLocalizacao);
        document.getElementById('btnBuscarEndereco')?.addEventListener('click', window.buscarPorEndereco);
    });

    // -------------------------------------------------------------
    // Carregar script do Google Maps
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