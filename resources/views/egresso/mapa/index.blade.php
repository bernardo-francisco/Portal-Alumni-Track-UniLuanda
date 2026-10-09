@extends('layouts.egresso')

@section('title', 'Mapa de Egressos')



@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">🗺️ Mapa de Egressos</h1>
        <p class="text-muted">Visualize a localização dos egressos da UniLuanda</p>
    </div>
    <div>
        <span class="badge bg-primary">{{ $total_localizados ?? 0 }} localizados</span>
        <span class="badge bg-secondary">{{ $total_egressos ?? 0 }} total</span>
    </div>
</div>

<!-- Estatísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-mapa">
            <div class="numero text-primary">{{ $total_localizados ?? 0 }}</div>
            <div class="rotulo">Egressos Localizados</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mapa" style="border-left-color: #22c55e;">
            <div class="numero text-success">{{ $total_conexoes_localizadas ?? 0 }}</div>
            <div class="rotulo">Na Minha Rede</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mapa" style="border-left-color: #f59e0b;">
            <div class="numero text-warning">{{ ($total_localizados ?? 0) - ($total_conexoes_localizadas ?? 0) }}</div>
            <div class="rotulo">Outros Egressos</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-mapa" style="border-left-color: #0ea5e9;">
            <div class="numero text-info">{{ $total_egressos ?? 0 }}</div>
            <div class="rotulo">Total de Egressos</div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4 filtros-card">
    <div class="card-body">
        <form method="GET" action="{{ route('egresso.mapa') }}" id="filtrosForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-search"></i> Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Nome ou processo..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-university"></i> Unidade</label>
                    <select name="unidade_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($unidades as $unidade)
                            <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                {{ $unidade->sigla }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-graduation-cap"></i> Curso</label>
                    <select name="curso_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach($cursos as $curso)
                            <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                {{ Str::limit($curso->nome, 20) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-flag"></i> País</label>
                    <select name="pais" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach($paises as $pais)
                            <option value="{{ $pais }}" {{ request('pais') == $pais ? 'selected' : '' }}>
                                {{ $pais }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-circle"></i> Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 Activo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>🔴 Inactivo</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-filtrar w-100" title="Aplicar filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'unidade_id', 'curso_id', 'pais', 'status']))
                        <a href="{{ route('egresso.mapa') }}" class="btn btn-limpar-filtros" title="Limpar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
        
        <!-- Filtros ativos -->
        @if(request()->anyFilled(['search', 'unidade_id', 'curso_id', 'pais', 'status']))
            <div class="mt-3 pt-2 border-top">
                <small class="text-muted">Filtros ativos:</small>
                @if(request('search'))
                    <span class="badge bg-primary">🔍 {{ request('search') }}</span>
                @endif
                @if(request('unidade_id') && $unidade = $unidades->firstWhere('id', request('unidade_id')))
                    <span class="badge bg-info">🏛️ {{ $unidade->sigla }}</span>
                @endif
                @if(request('curso_id') && $curso = $cursos->firstWhere('id', request('curso_id')))
                    <span class="badge bg-success">🎓 {{ Str::limit($curso->nome, 15) }}</span>
                @endif
                @if(request('pais'))
                    <span class="badge bg-warning">🌍 {{ request('pais') }}</span>
                @endif
                @if(request('status'))
                    <span class="badge {{ request('status') == 'active' ? 'bg-success' : 'bg-danger' }}">
                        {{ request('status') == 'active' ? '🟢 Activo' : '🔴 Inactivo' }}
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Mensagem sobre localização do usuário -->
@if(!isset($minhaLocalizacao) || !$minhaLocalizacao)
    <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
            <div>
                <strong>Você não tem localização registada!</strong>
                <br>
                <small>Para aparecer no mapa com o marcador <span class="badge bg-primary">azul</span>, 
                <a href="{{ route('egresso.localizacao.create') }}" class="alert-link">
                    <i class="fas fa-plus"></i> adicione sua localização atual
                </a>
                </small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@else
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fa-2x"></i>
            <div>
                <strong>Localização registada!</strong>
                <br>
                <small>Você está marcado no mapa com o marcador <span class="badge bg-primary">azul</span> 
                em <strong>{{ $minhaLocalizacao->cidade ?? $minhaLocalizacao->pais }}</strong>
                </small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Busca por processo -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           id="searchEgresso" 
                           class="form-control" 
                           placeholder="Pesquisar egresso pelo número de processo (ex: 2021.1.885)"
                           autocomplete="off">
                    <button class="btn btn-primary" id="btnBuscar">
                        <i class="fas fa-map-marker-alt"></i> Localizar
                    </button>
                    <button class="btn btn-outline-secondary" id="btnLimpar">
                        <i class="fas fa-times"></i> Limpar
                    </button>
                </div>
                <div id="searchResult" class="mt-2" style="display: none;">
                    <div class="alert alert-info d-flex align-items-center gap-3 mb-0">
                        <div id="searchAvatar" style="width: 40px; height: 40px;"></div>
                        <div>
                            <strong id="searchNome"></strong><br>
                            <small id="searchLocalizacao" class="text-muted"></small>
                        </div>
                    </div>
                </div>
                <div id="searchError" class="mt-2" style="display: none;">
                    <div class="alert alert-danger mb-0" id="searchErrorMessage"></div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted">🔍 Digite o número de processo <br> <kbd>Ctrl+F</kbd> para focar</small>
            </div>
        </div>
    </div>
</div>

<!-- Legenda -->
<div class="legenda-mapa mb-3">
    <span class="legenda-item">
        <span class="ponto ponto-eu"></span> Minha localização
    </span>
    <span class="legenda-item">
        <span class="ponto ponto-conexao"></span> Egressos da minha rede
    </span>
    <span class="legenda-item">
        <span class="ponto ponto-outro"></span> Outros egressos
    </span>
    <span class="legenda-item ms-auto">
        <i class="fas fa-mouse-pointer text-muted"></i> Clique no marcador
    </span>
    @if(isset($markers) && count($markers) > 20)
        <span class="legenda-item text-warning">
            <i class="fas fa-layer-group"></i> Marcadores agrupados
        </span>
    @endif
</div>

<!-- Mapa -->
<div class="card p-0">
    <div id="map">
        <div class="map-fallback" id="mapFallback">
            <i class="fas fa-map-marked-alt"></i>
            <h5>Carregando mapa...</h5>
            <p>Aguarde enquanto o mapa está a ser carregado.</p>
            <div class="spinner-border text-primary mt-2" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
    </div>
</div>

<!-- Scripts na Ordem Correta de Dependência -->
<script src="https://unpkg.com/@googlemaps/markerclusterer@2.5.5/dist/index.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleApiKey ?? env('GOOGLE_MAPS_API_KEY') }}&v=weekly&libraries=marker,places&callback=initMap" async defer></script>

<script>
// ============================================
// DADOS DOS MARCADORES (do PHP para JS)
// ============================================
var dadosMarkers = @json($markers ?? []);
var googleMapId = '{{ $googleMapId ?? env('GOOGLE_MAPS_MAP_ID') }}';

console.log('📊 Dados dos marcadores recebidos:', dadosMarkers.length);

// ============================================
// VARIÁVEIS GLOBAIS
// ============================================
let map;
let markers = [];
let markerCluster = null;
let bounds;
let activeInfoWindow = null;

// ============================================
// INICIALIZAR MAPA
// ============================================
function initMap() {
    try {
        if (typeof google === 'undefined' || !google.maps) {
            console.warn('⚠️ Google Maps não carregado adequadamente.');
            mostrarErro('Não foi possível conectar à API do Google Maps.');
            return;
        }

        bounds = new google.maps.LatLngBounds();
        const centerLuanda = { lat: -8.8383, lng: 13.2344 };

        var fallback = document.getElementById('mapFallback');
        if (fallback) fallback.style.display = 'none';

        map = new google.maps.Map(document.getElementById('map'), {
            center: centerLuanda,
            zoom: 4,
            mapId: googleMapId || 'DEMO_MAP_ID',
            mapTypeId: google.maps.MapTypeId.HYBRID,
            tilt: 45,
            heading: 0,
            zoomControl: true,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            scaleControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            minZoom: 2,
            maxZoom: 20,
        });

        map.addListener('click', function() {
            if (activeInfoWindow) {
                activeInfoWindow.close();
                activeInfoWindow = null;
            }
        });

        // Adiciona Marcadores e inicializa Agrupamento
        adicionarMarcadores();

    } catch (error) {
        console.error('❌ Erro durante inicialização do mapa:', error);
        mostrarErro('Erro de renderização: ' + error.message);
    }
}

// ============================================
// ADICIONAR MARCADORES COM CLUSTERER
// ============================================
function adicionarMarcadores() {
    // Limpar marcadores existentes
    markers.forEach(function(m) {
        if (m.setMap) m.setMap(null);
    });
    markers = [];

    if (!dadosMarkers || dadosMarkers.length === 0) {
        console.warn('⚠️ Nenhum ponto para desenhar no mapa.');
        return;
    }

    dadosMarkers.forEach(function(markerData) {
        var cor = markerData.cor || '#6c757d';
        var inicial = (markerData.nome && markerData.nome.length > 0) ? markerData.nome.charAt(0).toUpperCase() : '?';

        // Estilização Customizada do Pin (DOM Element)
        var pinWrapper = document.createElement('div');
        pinWrapper.className = 'custom-pin-container';
        pinWrapper.innerHTML = `
            <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; cursor: pointer; transition: transform 0.2s ease;">
                <div style="position: absolute; width: 40px; height: 40px; background: ${cor}; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                    <span style="color: white; font-weight: bold; font-size: 15px; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">${inicial}</span>
                </div>
                <div style="position: absolute; bottom: -6px; width: 0; height: 0; border-left: 7px solid transparent; border-right: 7px solid transparent; border-top: 10px solid ${cor}; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.2));"></div>
            </div>
        `;

        var markerElement;

        // Suporte para AdvancedMarkerElement com fallback para Marker comum
        if (google.maps.marker && google.maps.marker.AdvancedMarkerElement) {
            markerElement = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: { lat: parseFloat(markerData.lat), lng: parseFloat(markerData.lng) },
                title: markerData.nome,
                content: pinWrapper.firstElementChild
            });
        } else {
            markerElement = new google.maps.Marker({
                map: map,
                position: { lat: parseFloat(markerData.lat), lng: parseFloat(markerData.lng) },
                title: markerData.nome,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: cor,
                    fillOpacity: 1,
                    strokeColor: "#ffffff",
                    strokeWeight: 2,
                    scale: 10,
                }
            });
        }

        // Construção do InfoWindow
        var fotoHtml = markerData.foto ? 
            `<img src="${markerData.foto}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid ${cor};" onerror="this.style.display='none'">` :
            `<div style="width: 40px; height: 40px; border-radius: 50%; background: ${cor}; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 1rem;">${inicial}</div>`;

        var cidadeTexto = markerData.cidade ? markerData.cidade + ', ' : '';
        var btnConectar = '';
        var badgeRede = '';

        if (markerData.tipo === 'eu') {
            badgeRede = '<span class="badge bg-primary" style="font-size:0.65rem;">👤 Você</span>';
        } else if (markerData.conexao) {
            badgeRede = '<span class="badge bg-success" style="font-size:0.65rem;">🤝 Rede</span>';
        } else {
            badgeRede = '<span class="badge bg-secondary" style="font-size:0.65rem;">👤 Outro</span>';
            btnConectar = `
                <a href="/egresso/rede/conectar/${markerData.id}" 
                   style="flex:1; text-align:center; padding:5px 8px; background:#e9ecef; color:#1a1a2e; text-decoration:none; border-radius:6px; font-size:0.7rem; font-weight:500;"
                   onclick="return confirm('Deseja enviar pedido de conexão para ${markerData.nome}?')">
                    <i class="fas fa-user-plus"></i> Conectar
                </a>
            `;
        }

        var infoDiv = document.createElement('div');
        infoDiv.innerHTML = `
            <div style="min-width: 200px; max-width: 280px; padding: 4px 0;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    ${fotoHtml}
                    <div>
                        <strong style="color: #1a56db; font-size: 0.9rem;">${markerData.nome}</strong><br>
                        <small style="color: #666; font-size: 0.7rem;">${markerData.numero || ''}</small><br>
                        ${badgeRede}
                    </div>
                </div>
                <hr style="margin: 4px 0;">
                <div style="font-size: 0.8rem;">
                    <p style="margin: 2px 0;">
                        <i class="fas fa-graduation-cap" style="color: #6c757d;"></i> 
                        ${markerData.curso || 'N/A'}
                    </p>
                    <p style="margin: 2px 0;">
                        <i class="fas fa-map-marker-alt" style="color: ${cor};"></i> 
                        <strong>${cidadeTexto}${markerData.pais || ''}</strong>
                    </p>
                </div>
                <hr style="margin: 6px 0;">
                <div style="display: flex; gap: 5px;">
                    <a href="/egresso/egressos/${markerData.id}" 
                       style="flex:1; text-align:center; padding:5px 10px; background: ${cor}; color: #fff; text-decoration: none; border-radius: 6px; font-size: 0.75rem; font-weight: 500;">
                        <i class="fas fa-eye"></i> Perfil
                    </a>
                    ${btnConectar}
                </div>
            </div>
        `;

        var infoWindow = new google.maps.InfoWindow({
            content: infoDiv,
            maxWidth: 300
        });

        markerElement.addListener('click', function() {
            if (activeInfoWindow) activeInfoWindow.close();
            infoWindow.open(map, markerElement);
            activeInfoWindow = infoWindow;
        });

        bounds.extend(new google.maps.LatLng(markerData.lat, markerData.lng));
        markers.push(markerElement);
    });

    // ============================================
    // AGRUPAMENTO (MARKER CLUSTERER)
    // ============================================
    if (typeof markerClusterer !== 'undefined' && markerClusterer.MarkerClusterer && markers.length > 0) {
        if (markerCluster) {
            markerCluster.clearMarkers();
        }
        
        markerCluster = new markerClusterer.MarkerClusterer({
            map: map,
            markers: markers
        });
        console.log('✅ Agrupamento (Cluster) ativado para', markers.length, 'marcadores.');
    }

    // Ajuste de enquadramento da câmera
    if (markers.length === 1) {
        map.setCenter(bounds.getCenter());
        map.setZoom(13);
    } else if (markers.length > 1) {
        map.fitBounds(bounds);
    }
}

// ============================================
// MOSTRAR MENSAGENS DE ERRO
// ============================================
function mostrarErro(mensagem) {
    var mapDiv = document.getElementById('map');
    if (mapDiv) {
        mapDiv.innerHTML = `
            <div class="map-fallback">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                <h5 style="color: #e8eaf0;">Falha ao carregar o mapa</h5>
                <p style="color: #737a8f; max-width: 400px;">${mensagem}</p>
                <button class="btn-reload" onclick="location.reload()">
                    <i class="fas fa-sync"></i> Recarregar Página
                </button>
            </div>
        `;
    }
}

// ============================================
// LÓGICA DE BUSCA RÁPIDA DE EGRESSO
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const inputSearch = document.getElementById('searchEgresso');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpar = document.getElementById('btnLimpar');
    const searchResult = document.getElementById('searchResult');
    const searchError = document.getElementById('searchError');

    // Atalho Ctrl+F / Cmd+F para focar na busca
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'f') {
            if (inputSearch && document.activeElement !== inputSearch) {
                e.preventDefault();
                inputSearch.focus();
            }
        }
    });

    function executarBusca() {
        const termo = inputSearch.value.trim().toLowerCase();
        if (!termo) return;

        searchResult.style.display = 'none';
        searchError.style.display = 'none';

        const encontrado = dadosMarkers.find(m => 
            (m.numero && m.numero.toLowerCase().includes(termo)) ||
            (m.nome && m.nome.toLowerCase().includes(termo))
        );

        if (encontrado) {
            document.getElementById('searchNome').innerText = encontrado.nome;
            document.getElementById('searchLocalizacao').innerText = (encontrado.cidade ? encontrado.cidade + ', ' : '') + encontrado.pais;
            
            const avatarDiv = document.getElementById('searchAvatar');
            const inicial = encontrado.nome.charAt(0).toUpperCase();
            avatarDiv.innerHTML = encontrado.foto ? 
                `<img src="${encontrado.foto}" class="rounded-circle w-100 h-100" style="object-fit:cover;">` :
                `<div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold" style="background:${encontrado.cor || '#3b82f6'};">${inicial}</div>`;

            searchResult.style.display = 'block';

            if (map) {
                const pos = { lat: parseFloat(encontrado.lat), lng: parseFloat(encontrado.lng) };
                map.panTo(pos);
                map.setZoom(15);
            }
        } else {
            document.getElementById('searchErrorMessage').innerText = `Nenhum egresso encontrado com o processo/termo "${termo}".`;
            searchError.style.display = 'block';
        }
    }

    if (btnBuscar) btnBuscar.addEventListener('click', executarBusca);
    if (inputSearch) {
        inputSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') executarBusca();
        });
    }
    if (btnLimpar) {
        btnLimpar.addEventListener('click', function() {
            inputSearch.value = '';
            searchResult.style.display = 'none';
            searchError.style.display = 'none';
            if (map && bounds && !bounds.isEmpty()) {
                map.fitBounds(bounds);
            }
        });
    }
});
</script>
@endsection