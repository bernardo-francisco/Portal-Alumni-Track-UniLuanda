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

@push('scripts')
    {{-- 1. Passar os dados do Blade para o JS de forma segura --}}
    <script>
        window.MAPA_DATA = {
            mapId: "{{ env('GOOGLE_MAPS_MAP_ID') }}",
            markers: @json($markers)
        };
    </script>

    {{-- 2. Carregar o Google Maps (o callback chama o initMap do Vite) --}}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&v=weekly&callback=initMap&loading=async" async defer></script>

    {{-- 3. Carregar o nosso JS via Vite --}}
    @vite('resources/js/admin-mapa.js')
@endpush