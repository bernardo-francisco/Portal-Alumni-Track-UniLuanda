@extends('layouts.pdf')

@section('title', 'Relatório de Engajamento')
@section('report_name', 'Engajamento dos Egressos')
@section('footer_right', 'Análise do roadmap')

@section('content')

    <div class="doc-title">Engajamento dos Egressos</div>
    <div class="doc-subtitle">
        Análise de engajamento por curso e unidade
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalEgressos ?? 0 }}</span>
                <span class="stat-label">Total de Egressos</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $ativos ?? 0 }}</span>
                <span class="stat-label">Ativos</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $inativos ?? 0 }}</span>
                <span class="stat-label">Inativos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $taxaEngajamento ?? 0 }}%</span>
                <span class="stat-label">Taxa de Engajamento</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Engajamento por Unidade Orgânica</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 30%;">
            <col style="width: 14%;">
            <col style="width: 14%;">
            <col style="width: 14%;">
            <col style="width: 14%;">
            <col style="width: 14%;">
        </colgroup>

        <thead>
            <tr>
                <th>Unidade</th>
                <th class="text-center">Egressos</th>
                <th class="text-center">Ativos</th>
                <th class="text-center">Conexões</th>
                <th class="text-center">Mensagens</th>
                <th class="text-center">Taxa</th>
            </tr>
        </thead>

        <tbody>
            @forelse($porUnidade ?? [] as $u)
                <tr>
                    <td>{{ $u->nome }}</td>
                    <td class="text-center">{{ $u->total_egressos ?? 0 }}</td>
                    <td class="text-center">{{ $u->total_ativos ?? 0 }}</td>
                    <td class="text-center">{{ $u->total_conexoes ?? 0 }}</td>
                    <td class="text-center">{{ $u->total_mensagens ?? 0 }}</td>
                    <td class="text-center">
                        <strong>{{ $u->taxa_engajamento ?? 0 }}%</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 15px;">
                        Sem dados de engajamento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Top Egressos Mais Ativos</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 25%;">
            <col style="width: 20%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Egresso</th>
                <th>Curso</th>
                <th class="text-center">Conexões</th>
                <th class="text-center">Mensagens</th>
                <th class="text-center">Chamadas</th>
                <th class="text-center">Eventos</th>
                <th class="text-center">Score</th>
            </tr>
        </thead>

        <tbody>
            @forelse($topEgressos ?? [] as $e)

                @php
                    $foto = fotoBase64($e->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($e->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($e->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($e->curso->nome ?? '-', 20, '…') }}</td>
                    <td class="text-center">{{ $e->total_conexoes ?? 0 }}</td>
                    <td class="text-center">{{ $e->total_mensagens ?? 0 }}</td>
                    <td class="text-center">{{ $e->total_chamadas ?? 0 }}</td>
                    <td class="text-center">{{ $e->total_eventos ?? 0 }}</td>
                    <td class="text-center">
                        <strong>{{ $e->score ?? 0 }}</strong>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        Sem dados de engajamento.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection