@extends('layouts.pdf')

@section('title', 'Relatório do Mapa de Localizações')
@section('report_name', 'Relatório do Mapa')
@section('footer_right', 'Documento gerado automaticamente')

@section('content')

    <div class="doc-title">Relatório do Mapa de Localizações</div>
    <div class="doc-subtitle">Documento emitido automaticamente pelo sistema UniLuanda Alumni Track</div>

    {{-- RESUMO --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalLocalizados }}</span>
                <span class="stat-label">Egressos Localizados</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $totalComCoordenadas }}</span>
                <span class="stat-label">Com Coordenadas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalPaises }}</span>
                <span class="stat-label">Países</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $totalProvincias }}</span>
                <span class="stat-label">Províncias</span>
            </td>
        </tr>
    </table>

    {{-- MAPA --}}
    @if($mapaBase64)
        <div class="section-title">Mapa de Distribuição Geográfica</div>
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ $mapaBase64 }}" alt="Mapa" style="max-width: 100%; border: 1px solid #e2e8f0; border-radius: 8px;">
        </div>
    @else
        <div class="section-title">Mapa de Distribuição Geográfica</div>
        <p style="text-align: center; color: #94a3b8; padding: 20px;">
            Não foi possível gerar o mapa (sem coordenadas ou serviço indisponível).
        </p>
    @endif

    {{-- POR PAÍS --}}
    <div class="section-title">Distribuição por País</div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%;">#</th>
                <th>País</th>
                <th class="text-center" style="width: 15%;">Egressos</th>
                <th class="text-center" style="width: 15%;">Percentagem</th>
            </tr>
        </thead>
        <tbody>
            @forelse($porPais as $pais => $total)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td><strong>{{ $pais ?: 'Não informado' }}</strong></td>
                    <td class="text-center fw-bold">{{ $total }}</td>
                    <td class="text-center">
                        {{ $totalLocalizados > 0 ? round(($total / $totalLocalizados) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted" style="padding: 15px;">Sem dados.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- POR PROVÍNCIA --}}
    @if($porProvincia->count() > 0)
        <div class="section-title">Distribuição por Província</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%;">#</th>
                    <th>Província</th>
                    <th class="text-center" style="width: 15%;">Egressos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porProvincia as $prov => $total)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><strong>{{ $prov }}</strong></td>
                        <td class="text-center fw-bold">{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- LISTA COMPLETA --}}
    <div class="section-title">Lista de Localizações</div>
    <table class="report-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 22%;">
            <col style="width: 12%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 9%;">
            <col style="width: 8%;">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Egresso</th>
                <th class="text-center">Nº Processo</th>
                <th>País</th>
                <th>Província</th>
                <th>Cidade</th>
                <th class="text-center">Lat</th>
                <th class="text-center">Lng</th>
            </tr>
        </thead>
        <tbody>
            @forelse($localizacoes as $i => $l)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ Str::limit($l->egresso->nome_completo ?? '-', 30) }}</td>
                    <td class="text-center">{{ $l->egresso->numero_processo ?? '—' }}</td>
                    <td>{{ $l->pais ?? '—' }}</td>
                    <td>{{ $l->provincia ?? '—' }}</td>
                    <td>{{ $l->cidade ?? '—' }}</td>
                    <td class="text-center"><small>{{ $l->latitude ? number_format($l->latitude, 4) : '—' }}</small></td>
                    <td class="text-center"><small>{{ $l->longitude ? number_format($l->longitude, 4) : '—' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted" style="padding: 15px;">Nenhuma localização registada.</td></tr>
            @endforelse
        </tbody>
    </table>

@endsection