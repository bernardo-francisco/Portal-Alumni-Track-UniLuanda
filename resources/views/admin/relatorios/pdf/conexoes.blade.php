@extends('layouts.pdf')

@section('title', 'Relatório de Conexões')
@section('report_name', 'Rede de Contactos')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $conexoes->count();
    $aceites = $conexoes->where('status', 'aceito')->count();
    $pendentes = $conexoes->where('status', 'pendente')->count();
    $recusadas = $conexoes->where('status', 'recusado')->count();
@endphp

@section('content')

    <div class="doc-title">Rede de Contactos</div>
    <div class="doc-subtitle">
        Análise das conexões entre egressos da UniLuanda
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Pedidos</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $aceites }}</span>
                <span class="stat-label">Aceites</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $recusadas }}</span>
                <span class="stat-label">Recusadas</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Conexões</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 20%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 20%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Solicitante</th>
                <th class="text-center"></th>
                <th class="text-center"></th>
                <th>Destinatário</th>
                <th class="text-center">Data</th>
                <th class="text-center">Status</th>
                <th>Observação</th>
            </tr>
        </thead>

        <tbody>
            @forelse($conexoes as $c)

                @php
                    $fotoSol = fotoBase64($c->solicitante->foto_url ?? null);
                    $fotoDest = fotoBase64($c->destinatario->foto_url ?? null);

                    $badgeClass = match ($c->status) {
                        'aceito'   => 'badge-success',
                        'pendente' => 'badge-warning',
                        'recusado' => 'badge-danger',
                        default    => 'badge-unknown',
                    };
                @endphp

                <tr>
                    <td class="text-center">
                        @if($fotoSol)
                            <span class="foto-wrap">
                                <img src="{{ $fotoSol }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($c->solicitante->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($c->solicitante->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="text-center text-muted">→</td>
                    <td class="text-center">
                        @if($fotoDest)
                            <span class="foto-wrap">
                                <img src="{{ $fotoDest }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($c->destinatario->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($c->destinatario->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="text-center nowrap">{{ $c->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($c->status) }}</span>
                    </td>
                    <td>{{ Str::limit($c->observacao ?? '-', 15, '…') }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma conexão encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection