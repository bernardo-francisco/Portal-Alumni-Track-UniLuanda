@extends('layouts.pdf')

@section('title', 'Relatório de Auditoria')
@section('report_name', 'Auditoria')
@section('header_meta', 'Actividades dos administradores')

@section('content')

    <h1 class="doc-title">Relatório de Auditoria</h1>
    <p class="doc-subtitle">Registo de todas as actividades realizadas pelos administradores</p>

    {{-- Cards de estatísticas --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalAcoes }}</span>
                <span class="stat-label">Total de Acções</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $totalCriticos }}</span>
                <span class="stat-label">Críticas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $totalAvisos }}</span>
                <span class="stat-label">Avisos</span>
            </td>
            <td class="stat-box">
                <span class="stat-value">{{ $totalInfos }}</span>
                <span class="stat-label">Informativas</span>
            </td>
        </tr>
    </table>

    {{-- Top admins --}}
    @if($topAdmins->count() > 0)
        <div class="section-title">Top Administradores por Actividade</div>
        <table class="report-table">
            <colgroup>
                <col style="width: 55%;">
                <col style="width: 15%;">
                <col style="width: 30%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Admin</th>
                    <th class="text-center">Unidade</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAdmins as $a)
                    <tr>
                        <td>{{ $a->admin_nome ?? 'Desconhecido' }}</td>
                        <td class="text-center">
                            @if($a->admin_unidade_sigla)
                                <span class="badge badge-info">{{ $a->admin_unidade_sigla }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center"><span class="badge badge-primary">{{ $a->total }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Top acções --}}
    @if($topAcoes->count() > 0)
        <div class="section-title">Top Acções</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Acção</th>
                    <th class="text-center" style="width: 100px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topAcoes as $a)
                    <tr>
                        <td><code>{{ $a->acao }}</code></td>
                        <td class="text-center"><span class="badge badge-info">{{ $a->total }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Registo detalhado das últimas 100 acções --}}
    <div class="section-block">
        <div class="section-title">
            Registo Detalhado — Últimas {{ $logs->count() }} Acções
        </div>

        <table class="report-table" style="font-size: 7.5px;">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 15%;">
                <col style="width: 8%;">
                <col style="width: 13%;">
                <col style="width: 10%;">
                <col style="width: 25%;">
                <col style="width: 9%;">
                <col style="width: 10%;">
            </colgroup>

            <thead>
                <tr>
                    <th>Data</th>
                    <th>Admin</th>
                    <th class="text-center">Unidade</th>
                    <th>Acção</th>
                    <th>Alvo</th>
                    <th>Descrição</th>
                    <th class="text-center">Severidade</th>
                    <th>IP</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $log)
                    @php
                        $badge = match($log->severidade) {
                            'critico' => 'badge-danger',
                            'aviso'   => 'badge-warning',
                            default   => 'badge-info',
                        };
                    @endphp

                    <tr>
                        <td class="nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ Str::limit($log->admin_nome ?? '—', 20, '…') }}</td>
                        <td class="text-center">
                            @if($log->admin_unidade_sigla)
                                <span class="badge badge-info">{{ $log->admin_unidade_sigla }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td><code style="font-size: 7px;">{{ Str::limit($log->acao, 20, '…') }}</code></td>
                        <td>
                            @if($log->alvo_tipo)
                                {{ $log->alvo_tipo }}
                                @if($log->alvo_id) #{{ $log->alvo_id }} @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ Str::limit($log->descricao ?? '—', 45, '…') }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badge }}">{{ ucfirst($log->severidade) }}</span>
                        </td>
                        <td class="nowrap">{{ $log->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                            Nenhuma actividade registada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="aviso">
        <strong>ℹ️ Nota</strong>
        Este relatório é gerado automaticamente e apresenta as últimas {{ $logs->count() }} acções registadas.
        O acesso é restrito ao administrador master.
    </div>

@endsection