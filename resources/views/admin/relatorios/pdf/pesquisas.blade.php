@extends('layouts.pdf')

@section('title', 'Relatório de Pesquisas')
@section('report_name', 'Relatório de Pesquisas')
@section('footer_right', 'Documento gerado automaticamente')

@section('content')

    <div class="doc-title">Relatório de Pesquisas</div>
    <div class="doc-subtitle">Documento emitido automaticamente pelo sistema UniLuanda Alumni Track</div>

    {{-- RESUMO --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalPesquisas }}</span>
                <span class="stat-label">Total de Pesquisas</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $pesquisasAtivas }}</span>
                <span class="stat-label">Ativas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalRespostas }}</span>
                <span class="stat-label">Respostas</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $totalParticipantes }}</span>
                <span class="stat-label">Participantes</span>
            </td>
        </tr>
    </table>

    {{-- TOP PERGUNTA --}}
    @if($perguntaTop)
        <div class="section-title">Pergunta com Mais Respostas</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Pergunta</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Respostas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $perguntaTop->pergunta }}</td>
                    <td class="text-center">{{ ucfirst(str_replace('_', ' ', $perguntaTop->tipo)) }}</td>
                    <td class="text-center fw-bold">{{ $perguntaTop->respostas_count }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- LISTA DE PESQUISAS --}}
    <div class="section-title">Lista de Pesquisas</div>
    <table class="report-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 28%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Título</th>
                <th class="text-center">Perguntas</th>
                <th class="text-center">Respostas</th>
                <th class="text-center">Participantes</th>
                <th class="text-center">Início</th>
                <th class="text-center">Fim</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Taxa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesquisas as $i => $p)
                @php
                    $taxa = ($p->perguntas_count > 0 && $p->total_participantes > 0)
                        ? round(($p->respostas_count / ($p->perguntas_count * $p->total_participantes)) * 100)
                        : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ Str::limit($p->titulo, 45) }}</strong>
                        @if($p->descricao)
                            <br><small class="text-muted">{{ Str::limit($p->descricao, 70) }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $p->perguntas_count }}</td>
                    <td class="text-center fw-bold">{{ $p->respostas_count }}</td>
                    <td class="text-center">{{ $p->total_participantes }}</td>
                    <td class="text-center">{{ $p->data_inicio?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-center">{{ $p->data_fim?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $p->ativa ? 'badge-active' : 'badge-inactive' }}">
                            {{ $p->ativa ? 'Ativa' : 'Encerrada' }}
                        </span>
                    </td>
                    <td class="text-center fw-bold">{{ $taxa }}%</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted" style="padding: 15px;">Nenhuma pesquisa encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>

@endsection