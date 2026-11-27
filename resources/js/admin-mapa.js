/* resources/js/admin-mapa.js */

let map;
let markers = [];
let infoWindows = [];
let bounds;

window.initMap = function() {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    // Pega os dados que o Blade injetou
    const data = window.MAPA_DATA || {};
    const markersData = data.markers || [];
    const mapId = data.mapId;

    bounds = new google.maps.LatLngBounds();
    const center = { lat: -8.8383, lng: 13.2344 };

    try {
        if (!google.maps.marker || !google.maps.marker.AdvancedMarkerElement) {
            console.warn('⚠️ AdvancedMarkerElement indisponível, usando legacy.');
            createLegacyMarkers(markersData, center);
            return;
        }

        map = new google.maps.Map(mapElement, {
            center: center,
            zoom: 5,
            mapId: mapId,
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
            streetViewControlOptions: { position: google.maps.ControlPosition.RIGHT_TOP },
            fullscreenControl: true,
            fullscreenControlOptions: { position: google.maps.ControlPosition.BOTTOM_RIGHT }
        });

        // ✅ ADICIONA ESTE BLOCO AQUI:
        map.addListener('click', function() {
            // Fecha todas as janelas de informação abertas ao clicar no mapa
            infoWindows.forEach(iw => iw.close());
            
            // Fecha também a janela do pino de pesquisa (vermelho), se estiver aberta
            if (window.highlightInfoWindow) {
                window.highlightInfoWindow.close();
            }
        });
        // ---------------------------------

        if (markersData.length > 0) {
            markersData.forEach((marker) => {
                const pinElement = document.createElement('div');
                const letra = marker.nome ? marker.nome.charAt(0).toUpperCase() : 'E';
                pinElement.innerHTML = `
                    <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; cursor: pointer; transition: transform 0.2s ease;">
                        <div style="position: absolute; width: 44px; height: 44px; background: #1a56db; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.4); display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-weight: bold; font-size: 16px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                ${letra}
                            </span>
                        </div>
                        <div style="position: absolute; bottom: -8px; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 12px solid #1a56db; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></div>
                    </div>
                `;

                const markerInstance = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: { lat: parseFloat(marker.lat), lng: parseFloat(marker.lng) },
                    title: marker.nome,
                    content: pinElement.firstElementChild,
                });

                const infoContent = document.createElement('div');
                const fotoHtml = marker.foto 
                    ? `<img src="${marker.foto}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid #1a56db;">` 
                    : `<div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #1a56db, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1.1rem;">${letra}</div>`;

                infoContent.innerHTML = `
                    <div style="min-width: 220px; max-width: 280px; padding: 4px 0;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            ${fotoHtml}
                            <div>
                                <strong style="color: #1a56db; font-size: 0.9rem; display: block;">${marker.nome}</strong>
                                <small style="color: #64748b; font-size: 0.72rem;">${marker.numero}</small>
                            </div>
                        </div>
                        <hr style="margin: 6px 0; border-color: #e2e8f0;">
                        <div style="font-size: 0.82rem;">
                            <p style="margin: 4px 0; color: #334155;">
                                <i class="fas fa-map-marker-alt" style="color: #1a56db;"></i>
                                <strong>${marker.cidade ? marker.cidade + ', ' : ''}${marker.pais}</strong>
                            </p>
                            <p style="margin: 4px 0; color: #64748b; font-size: 0.72rem; font-family: monospace;">
                                ${parseFloat(marker.lat).toFixed(4)}, ${parseFloat(marker.lng).toFixed(4)}
                            </p>
                        </div>
                        <hr style="margin: 8px 0; border-color: #e2e8f0;">
                        <a href="/admin/egressos/${marker.id}" style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 600;">
                            <i class="fas fa-eye"></i> Ver Perfil
                        </a>
                    </div>
                `;

                const infoWindow = new google.maps.InfoWindow({ content: infoContent.firstElementChild, maxWidth: 300 });

                markerInstance.addListener('click', function() {
                    infoWindows.forEach(iw => iw.close());
                    infoWindow.open(map, markerInstance);
                });

                bounds.extend(new google.maps.LatLng(parseFloat(marker.lat), parseFloat(marker.lng)));
                markers.push(markerInstance);
                infoWindows.push(infoWindow);
            });

            if (markers.length > 0) {
                map.fitBounds(bounds);
                if (markers.length === 1) map.setZoom(14);
            }
        } else {
            map.setCenter(center);
            map.setZoom(5);
        }
    } catch (error) {
        console.error('Erro ao carregar o mapa moderno:', error);
        createLegacyMarkers(markersData, center);
    }
};

function createLegacyMarkers(markersData, center) {
    try {
        map = new google.maps.Map(document.getElementById('map'), {
            center: center, zoom: 5, mapTypeId: 'hybrid', zoomControl: true, mapTypeControl: true,
            mapTypeControlOptions: { position: google.maps.ControlPosition.TOP_RIGHT },
            scaleControl: true, streetViewControl: true,
            streetViewControlOptions: { position: google.maps.ControlPosition.RIGHT_TOP },
            fullscreenControl: true
        });

        // ✅ ADICIONA ESTE BLOCO AQUI:
        map.addListener('click', function() {
            infoWindows.forEach(iw => iw.close());
            if (window.highlightInfoWindow) {
                window.highlightInfoWindow.close();
            }
        });
        // ---------------------------------

        markersData.forEach((marker) => {
            const markerInstance = new google.maps.Marker({
                position: { lat: parseFloat(marker.lat), lng: parseFloat(marker.lng) },
                map: map, title: marker.nome, animation: google.maps.Animation.DROP
            });

            const infoContent = `
                <div style="min-width: 220px; max-width: 280px; padding: 10px;">
                    <strong style="color: #1a56db;">${marker.nome}</strong><br>
                    <small style="color: #64748b;">${marker.numero}</small><br>
                    <p style="margin: 6px 0;">📍 <strong>${marker.cidade ? marker.cidade + ', ' : ''}${marker.pais}</strong></p>
                    <a href="/admin/egressos/${marker.id}" style="display: block; text-align: center; padding: 8px 12px; background: linear-gradient(135deg, #1a56db, #3b82f6); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.8rem;">
                       <i class="fas fa-eye"></i> Ver Perfil
                    </a>
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({ content: infoContent, maxWidth: 300 });

            markerInstance.addListener('click', function() {
                infoWindows.forEach(iw => iw.close());
                infoWindow.open(map, markerInstance);
            });

            bounds.extend(new google.maps.LatLng(parseFloat(marker.lat), parseFloat(marker.lng)));
            markers.push(markerInstance);
            infoWindows.push(infoWindow);
        });

        if (markers.length > 0) {
            map.fitBounds(bounds);
            if (markers.length === 1) map.setZoom(14);
        }
    } catch (error) {
        console.error('Erro no fallback:', error);
    }
}

// ============================================================
// PESQUISA E HIGHLIGHT (O FILTRO)
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchEgresso');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpar = document.getElementById('btnLimpar');
    const searchResult = document.getElementById('searchResult');
    const searchError = document.getElementById('searchError');
    const searchErrorMessage = document.getElementById('searchErrorMessage');
    const searchNome = document.getElementById('searchNome');
    const searchLocalizacao = document.getElementById('searchLocalizacao');
    const searchAvatar = document.getElementById('searchAvatar');

    if (!btnBuscar) return;

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

                // ✅ AQUI ESTAVA O ERRO: Se o mapa não estivesse pronto, falhava. Agora protegemos.
                if (map) {
                    map.setCenter(pos);
                    map.setZoom(17);
                    highlightMarker(pos, data.egresso.nome, data.localizacao.cidade, data.localizacao.pais);
                } else {
                    console.warn('Mapa ainda não carregou, mas os dados foram encontrados.');
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
                map: map, position: position, content: pinElement.firstElementChild, title: nome || 'Egresso',
            });

            const infoContent = document.createElement('div');
            infoContent.innerHTML = `
                <div style="padding: 12px;">
                    <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${nome || 'Egresso'}</h5>
                    <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${cidade || 'Cidade não informada'}, ${pais || 'País não informado'}</p>
                    <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}</p>
                </div>
            `;

            window.highlightInfoWindow = new google.maps.InfoWindow({ content: infoContent.firstElementChild, maxWidth: 300 });

            window.highlightMarkerInstance.addListener('click', function() {
                window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
            });

            setTimeout(() => {
                if (window.highlightInfoWindow && window.highlightMarkerInstance) {
                    window.highlightInfoWindow.open(map, window.highlightMarkerInstance);
                }
            }, 500);

        } catch (error) {
            console.error('Erro no marcador moderno, tentando fallback:', error);
            createFallbackMarker(position, nome, cidade, pais);
        }
    }

    function createFallbackMarker(position, nome, cidade, pais) {
        const icon = {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#ef4444', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 4,
            scale: 16, anchor: new google.maps.Point(0, 0),
        };

        window.highlightMarkerInstance = new google.maps.Marker({
            position: position, map: map, title: nome || 'Egresso', icon: icon, animation: google.maps.Animation.BOUNCE, zIndex: 999,
        });

        const infoContent = `
            <div style="padding: 12px;">
                <h5 style="color: #1a56db; margin-bottom: 6px; font-weight: bold;">🎯 ${nome || 'Egresso'}</h5>
                <p style="margin: 0; font-size: 0.9rem; color: #334155;">📍 ${cidade || 'Cidade não informada'}, ${pais || 'País não informado'}</p>
                <p style="margin: 6px 0 0 0; font-size: 0.78rem; color: #64748b; font-family: monospace;">${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}</p>
            </div>
        `;

        window.highlightInfoWindow = new google.maps.InfoWindow({ content: infoContent, maxWidth: 300 });

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
                if (markers.length === 1) map.setZoom(14);
            } else {
                map.setZoom(5);
            }
        }
        searchInput.focus();
    }

    btnBuscar.addEventListener('click', buscarEgresso);
    searchInput.addEventListener('keypress', function (e) { if (e.key === 'Enter') buscarEgresso(); });
    btnLimpar.addEventListener('click', limparPesquisa);

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});