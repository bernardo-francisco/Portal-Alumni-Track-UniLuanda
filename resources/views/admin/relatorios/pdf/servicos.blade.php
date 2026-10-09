@extends('layouts.pdf')

@section('title', 'Relatório de Serviços')
@section('report_name', 'Pedidos de Serviços')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $servicos->count();
    $pendentes = $servicos->where('status', 'pendente')->count();
    $andamento = $servicos->where('status', 'andamento')->count();
    $atendidos = $servicos->where('status', 'atendido')->count();
    $cancelados = $servicos->where('status', 'cancelado')->count();
@endphp

@section('content')

    <div class="doc-title">Pedidos de Serviços</div>
    <div class="doc-subtitle">
        Análise dos serviços solicitados
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $andamento }}</span>
                <span class="stat-label">Em Andamento</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $atendidos }}</span>
                <span class="stat-label">Atendidos</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Pedidos</div>

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
                <th>Serviço</th>
                <th class="text-center">Data</th>
                <th class="text-center">Status</th>
                <th class="text-center">Atendido em</th>
            </tr>
        </thead>

        <tbody>
            @forelse($servicos as $s)

                @php
                    $foto = fotoBase64($s->egresso->foto_url ?? null);

                    $badgeClass = match ($s->status) {
                        'pendente'  => 'badge-warning',
                        'andamento' => 'badge-info',
                        'atendido'  => 'badge-success',
                        'cancelado' => 'badge-danger',
                        default     => 'badge-unknown',
                    };
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($s->egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($s->egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($s->servico ?? '-', 35, '…') }}</td>
                    <td class="text-center nowrap">{{ $s->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td class="text-center nowrap">
                        {{ $s->atendido_em ? $s->atendido_em->format('d/m/Y') : '-' }}
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 15px;">
                        Nenhum pedido encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection