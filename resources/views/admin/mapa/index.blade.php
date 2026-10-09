@extends('layouts.admin')

@section('title', 'Mapa de Egressos')

@section('page_title', '🗺️ Mapa de Egressos')
@section('page_subtitle', 'Visualize a localização dos egressos no mundo')

@section('content')

@php
    $totalLocalizados = $total_localizados ?? 0;
    $totalEgressos = $total_egressos ?? 0;
    $totalSemLocalizacao = max(0, $totalEgressos - $totalLocalizados);
    $percentualLocalizados = $totalEgressos > 0
        ? round(($totalLocalizados / $totalEgressos) * 100, 1)
        : 0;
@endphp

{{-- ============================================================
     ESTATÍSTICAS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total de Egressos</div>
                <div class="stat-card-value">{{ $totalEgressos }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-database"></i>
                    Registados na plataforma
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Localizados</div>
                <div class="stat-card-value">{{ $totalLocalizados }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-chart-line"></i>
                    {{ $percentualLocalizados }}% do total
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-icon">
                <i class="fas fa-map-pin"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Sem Localização</div>
                <div class="stat-card-value">{{ $totalSemLocalizacao }}</div>
                <div class="stat-card-desc">
                    <i class="fas fa-exclamation-circle"></i>
                    Coordenadas por preencher
                </div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     PESQUISA
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="section-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-search"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">Localizar Egresso</h6>
                <small class="text-muted">Pesquise pelo número de processo</small>
            </div>
        </div>

        <div class="row g-3 align-items-end">
            <div class="col-lg-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-hashtag text-muted"></i>
                    </span>
                    <input type="text"
                           id="searchEgresso"
                           class="form-control border-start-0"
                           placeholder="Ex: 2021.1.885"
                           autocomplete="off">
                    <button class="btn btn-primary" id="btnBuscar">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <span class="d-none d-sm-inline">Localizar</span>
                    </button>
                    <button class="btn btn-outline-secondary" id="btnLimpar" title="Limpar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="col-lg-4 d-flex align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Prima <kbd>Enter</kbd> para pesquisar
                </small>
            </div>
        </div>

        {{-- Resultado --}}
        <div id="searchResult" class="mt-3" style="display: none;">
            <div class="search-result-box">
                <div id="searchAvatar" class="search-result-avatar"></div>
                <div class="search-result-info">
                    <strong id="searchNome" class="d-block"></strong>
                    <small id="searchLocalizacao" class="text-muted"></small>
                </div>
                <div class="search-result-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        {{-- Erro --}}
        <div id="searchError" class="mt-3" style="display: none;">
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                <i class="fas fa-exclamation-circle"></i>
                <span id="searchErrorMessage"></span>
            </div>
        </div>

    </div>
</div>


{{-- ============================================================
     MAPA
============================================================ --}}
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-globe"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Mapa Interativo</h6>
                    <small class="text-muted">
                        {{ $totalLocalizados }} {{ $totalLocalizados === 1 ? 'egresso localizado' : 'egressos localizados' }}
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                    <i class="fas fa-map-marker-alt me-1"></i> Egresso
                </span>
                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                    <i class="fas fa-crosshairs me-1"></i> Resultado da pesquisa
                </span>
            </div>
        </div>
    </div>

    <div id="map" style="height: 580px; width: 100%;"></div>
</div>


{{-- ============================================================
     LISTA DE LOCALIZAÇÕES
============================================================ --}}
@if(isset($localizacoes) && $localizacoes->count() > 0)

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Lista de Localizações</h6>
                        <small class="text-muted">
                            {{ $localizacoes->count() }} {{ $localizacoes->count() === 1 ? 'registo' : 'registos' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Egresso</th>
                            <th>Nº Processo</th>
                            <th>País</th>
                            <th>Cidade</th>
                            <th>Coordenadas</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($localizacoes as $loc)
                            @php
                                $egresso = $loc->egresso;
                                $fotoRaw = $egresso?->foto_url ?? null;
                                $fotoUrl = null;
                                if ($fotoRaw) {
                                    $fotoUrl = filter_var($fotoRaw, FILTER_VALIDATE_URL) ? $fotoRaw : asset($fotoRaw);
                                }

                                $nomeEgresso = $egresso?->nome_completo ?? 'Egresso';
                                $iniciais = collect(explode(' ', trim($nomeEgresso)))
                                    ->filter()
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                    ->take(2)
                                    ->implode('');
                            @endphp

                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($fotoUrl)
                                            <img src="{{ $fotoUrl }}"
                                                 alt="{{ $nomeEgresso }}"
                                                 class="rounded-circle border flex-shrink-0"
                                                 style="width: 38px; height: 38px; object-fit: cover;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="rounded-circle align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                 style="width: 38px; height: 38px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.8rem; display: none;">
                                                {{ $iniciais }}
                                            </div>
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                 style="width: 38px; height: 38px; background: linear-gradient(135deg, #1a56db, #3b82f6); font-size: 0.8rem;">
                                                {{ $iniciais }}
                                            </div>
                                        @endif

                                        <strong class="text-truncate" style="max-width: 220px;">
                                            {{ $nomeEgresso }}
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <code class="text-dark fw-bold">{{ $egresso?->numero_processo ?? '-' }}</code>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-globe text-primary" style="font-size: 0.75rem;"></i>
                                        <span>{{ $loc->pais ?? '-' }}</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-city text-info" style="font-size: 0.75rem;"></i>
                                        <span>{{ $loc->cidade ?? '-' }}</span>
                                    </div>
                                </td>

                                <td>
                                    <small class="text-muted font-monospace">
                                        {{ number_format($loc->latitude, 4) }}, {{ number_format($loc->longitude, 4) }}
                                    </small>
                                </td>

                                <td class="text-end pe-4">
                                    @if($egresso)
                                        <a href="{{ route('admin.egressos.show', $egresso->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver perfil">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@else

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <h6 class="fw-bold mb-1">Nenhuma localização registada</h6>
            <p class="text-muted small mb-0">
                Certifique-se de que os egressos têm latitude e longitude preenchidas.
            </p>
        </div>
    </div>

@endif

@endsection


{{-- ============================================================
     SCRIPTS
============================================================ --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&v=weekly&callback=initMap&loading=async" async defer></script>
<script>
let map;
let markers = [];
let infoWindows = [];
let bounds;

function initMap() {
    bounds = new google.maps.LatLngBounds();
    const center = { lat: -8.8383, lng: 13.2344 };

    try {
        if (!google.maps.marker || !google.maps.marker.AdvancedMarkerElement) {
            console.warn('⚠️ AdvancedMarkerElement não disponível, usando fallback');
            createLegacyMarkers();
            return;
        }

        map = new google.maps.Map(document.getElementById('map'), {
            center: center,
            zoom: 5,
            mapId: '{{ env("GOOGLE_MAPS_MAP_ID") }}',
            mapTypeId: 'hybrid',
            tilt: 45,
            heading: 0,
            zoomControl: true,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT,
                mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain']
            },
            scaleControl: true,
            streetViewControl: true,
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_TOP
            },
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: google.maps.ControlPosition.BOTTOM_RIGHT
            }
        });

        @if(isset($markers) && count($markers) > 0)
            @foreach($markers as $marker)
                const pinElement{{ $loop->index }} = document.createElement('div');
                pinElement{{ $loop->index }}.innerHTML = `
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; cursor: pointer; transition: transform 0.2s ease;">
                        <div style="position: absolute; width: 44px; height: 44px; background: #1a56db; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.4); display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-weight: bold; font-size: 16px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                {{ strtoupper(substr($marker['nome'], 0, 1)) }}
                            </span>
                        </div>
                        <div style="position: absolute; bottom: -8px; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 12px solid #1a56db; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></div>
                    </div>
                `;

                const marker{{ $loop->index }} = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: { lat: {{ $marker['lat'] }}, lng: {{ $marker['lng'] }} },
                    title: "{{ $marker['nome'] }}",
                    content: pinElement{{ $loop->index }}.firstElementChild,
                });

                const infoContent{{ $loop->index }} = document.createElement('div');
                infoContent{{ $loop->index }}.innerHTML = `
                    <div style="min-width: 220px; max-width: 280px; padding: 4px 0;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            @if($marker['foto'])
                                <img src="{{ asset($marker['foto']) }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #1a56db;">
                            @else
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1.1rem;">
                                    {{ strtoupper(substr($marker['nome'], 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <strong style="color: #1a56db; font-size: 0.9rem; display: block;">{{ $marker['nome'] }}</strong>
                                <small style="color: #64748b; font-size: 0.72rem;">{{ $marker['numero'] }}</small>
                            </div>
                        </div>
                        <hr style="margin: 6px 0; border-color: #e2e8f0;">
                        <div style="font-size: 0.82rem;">
                            <p style="margin: 4px 0; color: #334155;">
                                <i class="fas fa-map-marker-alt" style="color: #1a56db;"></i>
                                <strong>{{ $marker['cidade'] ? $marker['cidade'] . ', ' : '' }}{{ $marker['pais'] }}</strong>
                            </p>
                            <p style="margin: 4px 0; color: #64748b; font-size: 0.72rem; font-family: monospace;">
                                {{ number_format($marker['lat'], 4) }}, {{ number_format($marker['lng'], 4) }}
                            </p>
                        </div>
                        <hr style="margin: 8px 0; border-color: #e2e8f0;">
                        <a href="{{ route('admin.egressos.show', $marker['id']) }}"
                           style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fas fa-eye"></i> Ver Perfil
                        </a>
                    </div>
                `;

                const infoWindow{{ $loop->index }} = new google.maps.InfoWindow({
                    content: infoContent{{ $loop->index }}.firstElementChild,
                    maxWidth: 300
                });

                marker{{ $loop->index }}.addListener('click', function() {
                    infoWindows.forEach(iw => iw.close());
                    infoWindow{{ $loop->index }}.open(map, marker{{ $loop->index }});
                });

                bounds.extend(new google.maps.LatLng({{ $marker['lat'] }}, {{ $marker['lng'] }}));
                markers.push(marker{{ $loop->index }});
                infoWindows.push(infoWindow{{ $loop->index }});
            @endforeach

            if (markers.length > 0) {
                map.fitBounds(bounds);
                if (markers.length === 1) {
                    map.setZoom(14);
                }
            }
        @else
            map.setCenter(center);
            map.setZoom(5);
        @endif

    } catch (error) {
        console.error('Erro ao carregar o mapa:', error);
        createLegacyMarkers();
    }
}

function createLegacyMarkers() {
    try {
        const center = { lat: -8.8383, lng: 13.2344 };
        map = new google.maps.Map(document.getElementById('map'), {
            center: center,
            zoom: 5,
            mapTypeId: 'hybrid',
            zoomControl: true,
            mapTypeControl: true,
            mapTypeControlOptions: { position: google.maps.ControlPosition.TOP_RIGHT },
            scaleControl: true,
            streetViewControl: true,
            streetViewControlOptions: { position: google.maps.ControlPosition.RIGHT_TOP },
            fullscreenControl: true
        });

        @if(isset($markers) && count($markers) > 0)
            @foreach($markers as $marker)
                const marker{{ $loop->index }} = new google.maps.Marker({
                    position: { lat: {{ $marker['lat'] }}, lng: {{ $marker['lng'] }} },
                    map: map,
                    title: "{{ $marker['nome'] }}",
                    animation: google.maps.Animation.DROP
                });

                const infoContent{{ $loop->index }} = `
                    <div style="min-width: 220px; max-width: 280px; padding: 10px;">
                        <strong style="color: #1a56db;">{{ $marker['nome'] }}</strong><br>
                        <small style="color: #64748b;">{{ $marker['numero'] }}</small><br>
                        <p style="margin: 6px 0;">📍 <strong>{{ $marker['cidade'] ? $marker['cidade'] . ', ' : '' }}{{ $marker['pais'] }}</strong></p>
                        <a href="{{ route('admin.egressos.show', $marker['id']) }}"
                           style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                           <i class="fas fa-eye"></i> Ver Perfil
                        </a>
                    </div>
                `;

                const infoWindow{{ $loop->index }} = new google.maps.InfoWindow({
                    content: infoContent{{ $loop->index }},
                    maxWidth: 300
                });

                marker{{ $loop->index }}.addListener('click', function() {
                    infoWindows.forEach(iw => iw.close());
                    infoWindow{{ $loop->index }}.open(map, marker{{ $loop->index }});
                });

                bounds.extend(new google.maps.LatLng({{ $marker['lat'] }}, {{ $marker['lng'] }}));
                markers.push(marker{{ $loop->index }});
                infoWindows.push(infoWindow{{ $loop->index }});
            @endforeach

            if (markers.length > 0) {
                map.fitBounds(bounds);
                if (markers.length === 1) {
                    map.setZoom(14);
                }
            }
        @endif
    } catch (error) {
        console.error('Erro no fallback:', error);
    }
}

// ============================================================
// PESQUISA E HIGHLIGHT
// ============================================================
const searchInput = document.getElementById('searchEgresso');
const btnBuscar = document.getElementById('btnBuscar');
const btnLimpar = document.getElementById('btnLimpar');
const searchResult = document.getElementById('searchResult');
const searchError = document.getElementById('searchError');
const searchErrorMessage = document.getElementById('searchErrorMessage');
const searchNome = document.getElementById('searchNome');
const searchLocalizacao = document.getElementById('searchLocalizacao');
const searchAvatar = document.getElementById('searchAvatar');

function buscarEgresso() {
    const numeroProcesso = searchInput.value.trim();

    if (!numeroProcesso) {
        showError('Por favor, digite um número de processo.');
        return;
    }

    btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Buscando...';
    btnBuscar.disabled = true;

    fetch(`/admin/mapa/buscar-egresso?numero_processo=${encodeURIComponent(numeroProcesso)}`)
        .then(response => response.json())
        .then(data => {
            btnBuscar.innerHTML = '<i class="fas fa-map-marker-alt me-1"></i><span class="d-none d-sm-inline">Localizar</span>';
            btnBuscar.disabled = false;

            if (!data.success) {
                showError(data.message);
                return;
            }

            searchError.style.display = 'none';
            searchResult.style.display = 'block';

            searchNome.textContent = `${data.egresso.nome} (${data.egresso.numero_processo})`;

            const localizacaoTexto = data.localizacao.cidade && data.localizacao.cidade !== 'Não informado'
                ? `${data.localizacao.cidade}, ${data.localizacao.pais}`
                : data.localizacao.pais;

            searchLocalizacao.textContent = `📍 ${localizacaoTexto}`;

            if (data.egresso.foto) {
                searchAvatar.innerHTML = `<img src="${data.egresso.foto}" alt="Foto">`;
            } else {
                const initials = data.egresso.nome.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                searchAvatar.innerHTML = `<div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1.1rem;">${initials}</div>`;
            }

            const pos = { lat: data.localizacao.lat, lng: data.localizacao.lng };

            if (map) {
                map.setCenter(pos);
                map.setZoom(17);
                highlightMarker(pos, data.egresso.nome, data.localizacao.cidade, data.localizacao.pais);
            }

            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
        })
        .catch(error => {
            btnBuscar.innerHTML = '<i class="fas fa-map-marker-alt me-1"></i><span class="d-none d-sm-inline">Localizar</span>';
            btnBuscar.disabled = false;
            showError('Erro ao buscar egresso. Tente novamente.');
            console.error('Erro:', error);
        });
}

function removeHighlightMarker() {
    if (window.highlightMarkerInstance) {
        if (typeof window.highlightMarkerInstance.setMap === 'function') {
            window.highlightMarkerInstance.setMap(null);
        } else {
            window.highlightMarkerInstance.map = null;
        }
        window.highlightMarkerInstance = null;
    }
    if (window.highlightInfoWindow) {
        window.highlightInfoWindow.close();
        window.highlightInfoWindow = null;
    }
}

function highlightMarker(position, nome, cidade, pais) {
    removeHighlightMarker();

    try {
        const pinElement = document.createElement('div');
        pinElement.innerHTML = `
            <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; cursor: pointer; animation: pulse 1.5s infinite;">
                <div style="position: absolute; width: 60px; height: 60px; background: #ef4444; border-radius: 50%; border: 4px solid white; box-shadow: 0 0 0 10px rgba(239, 68, 68, 0.3), 0 4px 20px rgba(239, 68, 68, 0.5); display: flex; align-items: center; justify-content: center;">
                    <span style="color: white; font-weight: bold; font-size: 20px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                        ${nome ? nome.charAt(0).toUpperCase() : 'E'}
                    </span>
                </div>
                <div style="position: absolute; bottom: -12px; width: 0; height: 0; border-left: 12px solid transparent; border-right: 12px solid transparent; border-top: 18px solid #ef4444; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></div>
            </div>
            <style>
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.15); }
                    100% { transform: scale(1); }
                }
            </style>
        `;

        window.highlightMarkerInstance = new google.maps.marker.AdvancedMarkerElement({
            map: map,
            position: position,
            content: pinElement.firstElementChild,
            title: nome || 'Egresso',
        });

        const infoContent = document.createElement('div');
        infoContent.innerHTML = `
            <div style="padding: 12px;">
                <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${nome || 'Egresso'}</h5>
                <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${cidade || 'Cidade não informada'}, ${pais || 'País não informado'}</p>
                <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}</p>
            </div>
        `;

        window.highlightInfoWindow = new google.maps.InfoWindow({
            content: infoContent.firstElementChild,
            maxWidth: 300
        });

        window.highlightMarkerInstance.addListener('click', function() {
            window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
        });

        setTimeout(() => {
            if (window.highlightInfoWindow && window.highlightMarkerInstance) {
                window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
            }
        }, 500);

    } catch (error) {
        console.error('Erro ao criar marcador moderno:', error);
        createFallbackMarker(position, nome, cidade, pais);
    }
}

function createFallbackMarker(position, nome, cidade, pais) {
    const icon = {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: '#ef4444',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 4,
        scale: 16,
        anchor: new google.maps.Point(0, 0),
    };

    window.highlightMarkerInstance = new google.maps.Marker({
        position: position,
        map: map,
        title: nome || 'Egresso',
        icon: icon,
        animation: google.maps.Animation.BOUNCE,
        zIndex: 999,
    });

    const infoContent = `
        <div style="padding: 12px;">
            <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${nome || 'Egresso'}</h5>
            <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${cidade || 'Cidade não informada'}, ${pais || 'País não informado'}</p>
            <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}</p>
        </div>
    `;

    window.highlightInfoWindow = new google.maps.InfoWindow({
        content: infoContent,
        maxWidth: 300
    });

    window.highlightMarkerInstance.addListener('click', function() {
        window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
    });

    setTimeout(() => {
        if (window.highlightInfoWindow && window.highlightMarkerInstance) {
            window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
        }
    }, 500);
}

function showError(message) {
    searchError.style.display = 'block';
    searchResult.style.display = 'none';
    searchErrorMessage.textContent = message;
}

function limparPesquisa() {
    searchInput.value = '';
    searchResult.style.display = 'none';
    searchError.style.display = 'none';

    removeHighlightMarker();

    if (map) {
        map.setCenter({ lat: -8.8383, lng: 13.2344 });
        if (markers.length > 0) {
            map.fitBounds(bounds);
            if (markers.length === 1) {
                map.setZoom(14);
            }
        } else {
            map.setZoom(5);
        }
    }

    searchInput.focus();
}

btnBuscar.addEventListener('click', buscarEgresso);
searchInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') buscarEgresso();
});
btnLimpar.addEventListener('click', limparPesquisa);

document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        searchInput.focus();
        searchInput.select();
    }
});
</script>

