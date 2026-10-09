@extends('layouts.pdf')

@section('title', 'Relatório de Pedidos de Serviços')
@section('report_name', 'Relatório de Serviços')
@section('footer_right', 'Documento gerado automaticamente')

@section('content')

    <div class="doc-title">Relatório de Pedidos de Serviços</div>
    <div class="doc-subtitle">Documento emitido automaticamente pelo sistema UniLuanda Alumni Track</div>

    {{-- RESUMO --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalServicos }}</span>
                <span class="stat-label">Total de Pedidos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $andamento }}</span>
                <span class="stat-label">Em Andamento</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $atendidos }}</span>
                <span class="stat-label">Atendidos</span>
            </td>
        </tr>
    </table>

    <table class="stats-table">
        <tr>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $cancelados }}</span>
                <span class="stat-label">Cancelados</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $taxaAtendimento }}%</span>
                <span class="stat-label">Taxa de Atendimento</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $tempoMedio }}h</span>
                <span class="stat-label">Tempo Médio de Atendimento</span>
            </td>
            <td class="stat-box">
                <span class="stat-value">{{ $topServicos->count() }}</span>
                <span class="stat-label">Tipos de Serviço</span>
            </td>
        </tr>
    </table>

    {{-- TOP SERVIÇOS --}}
    @if($topServicos->count() > 0)
        <div class="section-title">Top 5 Serviços Mais Pedidos</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 8%;">#</th>
                    <th>Serviço</th>
                    <th class="text-center" style="width: 15%;">Pedidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topServicos as $servico => $total)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><strong>{{ $servico }}</strong></td>
                        <td class="text-center fw-bold">{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- LISTA COMPLETA --}}
    <div class="section-title">Todos os Pedidos</div>
    <table class="report-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 18%;">
            <col style="width: 16%;">
            <col style="width: 24%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Egresso</th>
                <th>Serviço</th>
                <th>Descrição</th>
                <th class="text-center">Status</th>
                <th class="text-center">Data Pedido</th>
                <th class="text-center">Atendido em</th>
                <th class="text-center">Dias</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servicos as $i => $s)
                @php
                    $dias = $s->atendido_em
                        ? $s->created_at->diffInDays($s->atendido_em)
                        : '—';

                    $badge = match($s->status) {
                        'atendido'  => 'badge-active',
                        'andamento' => 'badge-warning',
                        'cancelado' => 'badge-inactive',
                        default     => 'badge-unknown',
                    };

                    $label = match($s->status) {
                        'atendido'  => 'Atendido',
                        'andamento' => 'Em Andamento',
                        'cancelado' => 'Cancelado',
                        'pendente'  => 'Pendente',
                        default     => ucfirst($s->status),
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ Str::limit($s->egresso->nome_completo ?? '-', 22) }}</strong>
                        <br><small class="text-muted">{{ $s->egresso->numero_processo ?? '' }}</small>
                    </td>
                    <td>{{ Str::limit($s->servico ?? '-', 25) }}</td>
                    <td>{{ Str::limit($s->descricao ?? '-', 60) }}</td>
                    <td class="text-center"><span class="badge {{ $badge }}">{{ $label }}</span></td>
                    <td class="text-center">{{ $s->created_at?->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $s->atendido_em?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-center fw-bold">{{ $dias }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted" style="padding: 15px;">Nenhum pedido de serviço.</td></tr>
            @endforelse
        </tbody>
    </table>

@endsection