@extends('layouts.pdf')

@section('title', 'Relatório de Notificações')
@section('report_name', 'Notificações do Sistema')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $notificacoes->count();
    $lidas = $notificacoes->where('lida', true)->count();
    $naoLidas = $notificacoes->where('lida', false)->count();

    $porTipo = $notificacoes->groupBy('tipo')->map->count();
@endphp

@section('content')

    <div class="doc-title">Notificações do Sistema</div>
    <div class="doc-subtitle">
        Análise das notificações geradas
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $lidas }}</span>
                <span class="stat-label">Lidas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $naoLidas }}</span>
                <span class="stat-label">Não Lidas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $porTipo->count() }}</span>
                <span class="stat-label">Tipos Diferentes</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Distribuição por Tipo</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 70%;">
            <col style="width: 30%;">
        </colgroup>

        <thead>
            <tr>
                <th>Tipo de Notificação</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($porTipo as $tipo => $totalTipo)
                <tr>
                    <td>{{ ucfirst($tipo) }}</td>
                    <td class="text-center"><strong>{{ $totalTipo }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted" style="padding: 15px;">
                        Sem notificações registadas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Últimas Notificações</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 25%;">
            <col style="width: 40%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Destinatário</th>
                <th>Título</th>
                <th class="text-center">Data</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($notificacoes->take(50) as $notif)

                @php
                    $foto = fotoBase64($notif->egresso->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($notif->egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($notif->egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($notif->titulo ?? '-', 50, '…') }}</td>
                    <td class="text-center nowrap">{{ $notif->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        @if($notif->lida)
                            <span class="badge badge-success">Lida</span>
                        @else
                            <span class="badge badge-warning">Não Lida</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma notificação encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection