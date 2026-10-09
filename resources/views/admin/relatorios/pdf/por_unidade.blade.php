@extends('layouts.pdf')

@section('title', 'Relatório por Unidade Orgânica')
@section('report_name', 'Relatório por Unidade Orgânica')
@section('footer_right', 'Análise por unidade')

@php
    $totalUnidades = $unidades->count();
    $totalEgressosGeral = $unidades->sum('total_egressos');
    $totalEmpregadosGeral = $unidades->sum('total_empregados');
    $taxaGeralPorUnidade = $totalEgressosGeral > 0
        ? round(($totalEmpregadosGeral / $totalEgressosGeral) * 100, 2)
        : 0;
@endphp

@section('content')

    <div class="doc-title">Relatório por Unidade Orgânica</div>
    <div class="doc-subtitle">
        Análise consolidada dos egressos por unidade orgânica
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalUnidades }}</span>
                <span class="stat-label">Total de Unidades</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalEgressosGeral }}</span>
                <span class="stat-label">Total Egressos</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $totalEmpregadosGeral }}</span>
                <span class="stat-label">Empregados</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $taxaGeralPorUnidade }}%</span>
                <span class="stat-label">Taxa Geral</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Resumo por Unidade</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 35%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 5%;">
        </colgroup>

        <thead>
            <tr>
                <th>Unidade</th>
                <th class="text-center">Total</th>
                <th class="text-center">Empregados</th>
                <th class="text-center">Taxa</th>
                <th class="text-center">Status</th>
                <th class="text-center">Cursos</th>
            </tr>
        </thead>

        <tbody>
            @forelse($unidades as $unidade)

                @php
                    $taxa = $unidade->taxa_empregabilidade ?? 0;
                    $badgeClass = $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
                    $statusLabel = $taxa >= 70 ? 'Excelente' : ($taxa >= 40 ? 'Regular' : 'Crítico');
                @endphp

                <tr>
                    <td><strong>{{ $unidade->sigla }} - {{ $unidade->nome }}</strong></td>
                    <td class="text-center">{{ $unidade->total_egressos }}</td>
                    <td class="text-center">{{ $unidade->total_empregados }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $taxa }}%</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-center">{{ $unidade->cursos->count() }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma unidade encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Análise Detalhada por Unidade</div>

    @forelse($unidades as $unidade)

        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 12px; background: #f8f9fa;">

            <table style="width: 100%; margin-bottom: 6px;">
                <tr>
                    <td>
                        <strong style="font-size: 11px; color: #0d6efd;">
                            {{ $unidade->sigla }} - {{ $unidade->nome }}
                        </strong>
                        <div style="font-size: 7.5px; color: #6c757d; margin-top: 2px;">
                            <strong>Cursos:</strong> {{ $unidade->cursos->count() }} &bull;
                            <strong>Total Egressos:</strong> {{ $unidade->total_egressos }}
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <span class="badge {{ $unidade->taxa_empregabilidade >= 70 ? 'badge-success' : ($unidade->taxa_empregabilidade >= 40 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $unidade->taxa_empregabilidade }}%
                        </span>
                    </td>
                </tr>
            </table>

            <table class="report-table" style="font-size: 7.5px;">
                <colgroup>
                    <col style="width: 40%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                </colgroup>

                <thead>
                    <tr>
                        <th>Curso</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Empregados</th>
                        <th class="text-center">Taxa</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($unidade->cursos as $curso)

                        @php
                            $empCount = $curso->egressos->where('status', 'active')->count();
                            $taxa = $curso->egressos->count() > 0
                                ? round(($empCount / $curso->egressos->count()) * 100, 2)
                                : 0;
                            $badgeClass = $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
                            $statusLabel = $taxa >= 70 ? 'Excelente' : ($taxa >= 40 ? 'Regular' : 'Crítico');
                        @endphp

                        <tr>
                            <td>{{ $curso->nome }}</td>
                            <td class="text-center">{{ $curso->egressos->count() }}</td>
                            <td class="text-center">{{ $empCount }}</td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }}">{{ $taxa }}%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @empty
        <div class="text-center text-muted" style="padding: 30px 0;">
            Nenhuma unidade encontrada.
        </div>
    @endforelse

    @php $ranking = $unidades->sortByDesc('taxa_empregabilidade'); @endphp

    @if($ranking->count() > 0)
        <div class="section-title">Ranking das Unidades</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 50%;">
                <col style="width: 15%;">
                <col style="width: 25%;">
            </colgroup>

            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Unidade</th>
                    <th class="text-center">Taxa</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ranking as $unidade)

                    @php
                        $taxa = $unidade->taxa_empregabilidade ?? 0;
                        $badgeClass = $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
                        $statusLabel = $taxa >= 70 ? 'Excelente' : ($taxa >= 40 ? 'Regular' : 'Crítico');
                    @endphp

                    <tr>
                        <td class="text-center"><strong>#{{ $loop->iteration }}</strong></td>
                        <td><strong>{{ $unidade->sigla }} - {{ $unidade->nome }}</strong></td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $taxa }}%</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

@endsection