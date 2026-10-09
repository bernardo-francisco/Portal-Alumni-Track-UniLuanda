@extends('layouts.pdf')

@section('title', 'Relatório de Atividade')
@section('report_name', 'Atividade Geral da Plataforma')
@section('footer_right', 'Análise do roadmap')

@php
    $atividade = $atividade ?? [];
@endphp

@section('content')

    <div class="doc-title">Atividade Geral da Plataforma</div>
    <div class="doc-subtitle">
        Análise consolidada de toda a atividade
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalEgressos ?? 0 }}</span>
                <span class="stat-label">Egressos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalMensagens ?? 0 }}</span>
                <span class="stat-label">Mensagens</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $totalChamadas ?? 0 }}</span>
                <span class="stat-label">Chamadas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $totalConexoes ?? 0 }}</span>
                <span class="stat-label">Conexões</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Resumo por Módulo</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 25%;">
            <col style="width: 25%;">
        </colgroup>

        <thead>
            <tr>
                <th>Módulo</th>
                <th class="text-center">Total</th>
                <th class="text-center">Últimos 30 dias</th>
            </tr>
        </thead>

        <tbody>
            @php
                $modulos = [
                    ['label' => 'Egressos Registados',        'total' => $totalEgressos ?? 0,    'recente' => $egressosRecentes ?? 0],
                    ['label' => 'Conexões Aceites',           'total' => $totalConexoes ?? 0,    'recente' => $conexoesRecentes ?? 0],
                    ['label' => 'Mensagens Trocadas',         'total' => $totalMensagens ?? 0,   'recente' => $mensagensRecentes ?? 0],
                    ['label' => 'Chamadas Realizadas',        'total' => $totalChamadas ?? 0,    'recente' => $chamadasRecentes ?? 0],
                    ['label' => 'Áudios Enviados',            'total' => $totalAudios ?? 0,      'recente' => $audiosRecentes ?? 0],
                    ['label' => 'Notificações Geradas',       'total' => $totalNotificacoes ?? 0,'recente' => $notificacoesRecentes ?? 0],
                    ['label' => 'Candidaturas Submetidas',    'total' => $totalCandidaturas ?? 0,'recente' => $candidaturasRecentes ?? 0],
                    ['label' => 'Eventos Criados',            'total' => $totalEventos ?? 0,     'recente' => $eventosRecentes ?? 0],
                    ['label' => 'Inscrições em Eventos',      'total' => $totalInscricoes ?? 0,  'recente' => $inscricoesRecentes ?? 0],
                    ['label' => 'Feedbacks Submetidos',       'total' => $totalFeedbacks ?? 0,   'recente' => $feedbacksRecentes ?? 0],
                    ['label' => 'Serviços Solicitados',       'total' => $totalServicos ?? 0,    'recente' => $servicosRecentes ?? 0],
                ];
            @endphp

            @foreach($modulos as $m)
                <tr>
                    <td>{{ $m['label'] }}</td>
                    <td class="text-center"><strong>{{ number_format($m['total']) }}</strong></td>
                    <td class="text-center">{{ number_format($m['recente']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection