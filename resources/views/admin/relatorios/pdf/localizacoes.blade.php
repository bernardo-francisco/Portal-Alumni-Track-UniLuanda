@extends('layouts.pdf')

@section('title', 'Relatório de Localizações')
@section('report_name', 'Relatório de Localizações')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $localizacoes->count();
    $atuais = $localizacoes->where('is_current', true)->count();
    $paises = $localizacoes->pluck('pais')->filter()->unique()->count();
    $comCoords = $localizacoes->whereNotNull('latitude')->whereNotNull('longitude')->count();
@endphp

@section('content')

    <div class="doc-title">Relatório de Localizações</div>
    <div class="doc-subtitle">
        Distribuição geográfica dos egressos da UniLuanda
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Localizações</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $atuais }}</span>
                <span class="stat-label">Atuais</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $paises }}</span>
                <span class="stat-label">Países</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $comCoords }}</span>
                <span class="stat-label">Com Coordenadas</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Localizações</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 18%;">
            <col style="width: 10%;">
            <col style="width: 15%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
            <col style="width: 8%;">
            <col style="width: 5%;">
            <col style="width: 6%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Egresso</th>
                <th>Nº Processo</th>
                <th>Curso</th>
                <th>País</th>
                <th>Província</th>
                <th>Cidade</th>
                <th class="text-center">Desde</th>
                <th class="text-center">Atual</th>
                <th class="text-center">Coords</th>
            </tr>
        </thead>

        <tbody>
            @forelse($localizacoes as $loc)

                @php
                    $egresso = $loc->egresso;
                    $foto = fotoBase64($egresso->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>

                    <td>{{ Str::limit($egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="fw-bold nowrap">{{ $egresso->numero_processo ?? '-' }}</td>
                    <td>{{ Str::limit($egresso->curso->nome ?? '-', 20, '…') }}</td>
                    <td>{{ $loc->pais ?? '-' }}</td>
                    <td>{{ Str::limit($loc->provincia ?? '-', 15, '…') }}</td>
                    <td>{{ Str::limit($loc->cidade ?? '-', 15, '…') }}</td>
                    <td class="text-center nowrap">
                        {{ $loc->data_desde ? date('d/m/Y', strtotime($loc->data_desde)) : '-' }}
                    </td>
                    <td class="text-center">
                        @if($loc->is_current)
                            <span class="badge badge-success">Sim</span>
                        @else
                            <span class="badge badge-secondary">Não</span>
                        @endif
                    </td>
                    <td class="text-center nowrap" style="font-size: 6.5px;">
                        @if($loc->latitude && $loc->longitude)
                            {{ number_format($loc->latitude, 3) }},
                            {{ number_format($loc->longitude, 3) }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma localização encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- MAPA --}}
    @if(!empty($mapaBase64))
        <div class="section-title">Mapa Geral de Localização</div>
        <div style="text-align: center; margin-top: 10px;">
            <img src="{{ $mapaBase64 }}" alt="Mapa" style="max-width: 100%; height: auto;">
            <p style="font-size: 7.5px; color: #6c757d; margin-top: 4px;">
                <span style="color: #dc3545;">●</span> Localização do Egresso
            </p>
        </div>
    @else
        <div class="section-title">Mapa Geral de Localização</div>
        <div style="padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; font-size: 8px; color: #664d03;">
            <strong>⚠️ Mapa não disponível</strong><br>
            {{ $erroDetalhado ?? 'Motivo não especificado.' }}
        </div>
    @endif

@endsection