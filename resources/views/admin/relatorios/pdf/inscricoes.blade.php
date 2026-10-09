@extends('layouts.pdf')

@section('title', 'Relatório de Inscrições')
@section('report_name', 'Inscrições em Eventos')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $inscricoes->count();
    $presentes = $inscricoes->where('presente', true)->count();
    $ausentes = $total - $presentes;
    $taxa = $total > 0 ? round(($presentes / $total) * 100) : 0;
@endphp

@section('content')

    <div class="doc-title">Inscrições em Eventos</div>
    <div class="doc-subtitle">
        Análise das inscrições e presenças em eventos
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Inscrições</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $presentes }}</span>
                <span class="stat-label">Presentes</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $ausentes }}</span>
                <span class="stat-label">Ausentes</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $taxa }}%</span>
                <span class="stat-label">Taxa de Presença</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Inscrições</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 22%;">
            <col style="width: 28%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Egresso</th>
                <th>Evento</th>
                <th class="text-center">Inscrição</th>
                <th class="text-center">Comprovativo</th>
                <th class="text-center">Presença</th>
            </tr>
        </thead>

        <tbody>
            @forelse($inscricoes as $i)

                @php
                    $foto = fotoBase64($i->egresso->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($i->egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($i->egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($i->evento->titulo ?? '-', 35, '…') }}</td>
                    <td class="text-center nowrap">{{ $i->created_at->format('d/m/Y') }}</td>
                    <td class="text-center nowrap">
                        <code style="font-size: 8px;">{{ $i->codigo_comprovativo ?? '-' }}</code>
                    </td>
                    <td class="text-center">
                        @if($i->presente)
                            <span class="badge badge-success">Presente</span>
                        @else
                            <span class="badge badge-warning">Não Registado</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma inscrição encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection