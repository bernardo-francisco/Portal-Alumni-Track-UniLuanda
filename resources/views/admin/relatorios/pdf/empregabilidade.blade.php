@extends('layouts.pdf')

@section('title', 'Relatório de Empregabilidade')
@section('report_name', 'Relatório de Empregabilidade')
@section('footer_right', 'Análise consolidada')

@section('content')

    <div class="doc-title">Relatório de Empregabilidade</div>
    <div class="doc-subtitle">
        Análise da taxa de empregabilidade dos egressos
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalEgressos }}</span>
                <span class="stat-label">Total de Egressos</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $empregados }}</span>
                <span class="stat-label">Empregados</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $desempregados }}</span>
                <span class="stat-label">Desempregados</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $taxaGeral }}%</span>
                <span class="stat-label">Taxa de Empregabilidade</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Resumo Geral</div>
    <table class="report-table">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 25%;">
            <col style="width: 25%;">
        </colgroup>

        <thead>
            <tr>
                <th>Métrica</th>
                <th class="text-center">Valor</th>
                <th class="text-center">Percentual</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><strong>Total de Egressos</strong></td>
                <td class="text-center">{{ $totalEgressos }}</td>
                <td class="text-center">100%</td>
            </tr>
            <tr>
                <td><strong>Empregados</strong></td>
                <td class="text-center">{{ $empregados }}</td>
                <td class="text-center">{{ $taxaGeral }}%</td>
            </tr>
            <tr>
                <td><strong>Desempregados</strong></td>
                <td class="text-center">{{ $desempregados }}</td>
                <td class="text-center">
                    {{ $totalEgressos > 0 ? round(($desempregados / $totalEgressos) * 100, 2) : 0 }}%
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Taxa de Empregabilidade por Curso</div>

    <table class="report-table">
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
            @forelse($porCurso as $curso)

                @php
                    $taxa = $curso->taxa_empregabilidade ?? 0;
                    $badgeClass = $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
                    $statusLabel = $taxa >= 70 ? 'Excelente' : ($taxa >= 40 ? 'Regular' : 'Crítico');
                @endphp

                <tr>
                    <td><strong>{{ $curso->nome }}</strong></td>
                    <td class="text-center">{{ $curso->egressos_count }}</td>
                    <td class="text-center">{{ $curso->empregados_count }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $taxa }}%</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 15px;">
                        Nenhum curso encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $melhores = $porCurso->sortByDesc('taxa_empregabilidade')->take(3);
        $piores = $porCurso->sortBy('taxa_empregabilidade')->take(3);
    @endphp

    @if($melhores->count() > 0 || $piores->count() > 0)
        <div class="section-title">Classificação dos Cursos</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 50%;">
            </colgroup>

            <thead>
                <tr>
                    <th class="text-center">🏆 Melhores Cursos</th>
                    <th class="text-center">⚠️ Cursos a Melhorar</th>
                </tr>
            </thead>

            <tbody>
                @for($i = 0; $i < max($melhores->count(), $piores->count()); $i++)
                    <tr>
                        <td>
                            @if($melhores->values()->get($i))
                                {{ $i + 1 }}. {{ $melhores->values()->get($i)->nome }}
                                <span class="badge badge-success">{{ $melhores->values()->get($i)->taxa_empregabilidade }}%</span>
                            @endif
                        </td>
                        <td>
                            @if($piores->values()->get($i))
                                {{ $i + 1 }}. {{ $piores->values()->get($i)->nome }}
                                <span class="badge badge-danger">{{ $piores->values()->get($i)->taxa_empregabilidade }}%</span>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    @endif

    <div class="section-title">Recomendações</div>
    <div style="padding: 12px; background: #eff6ff; border-left: 4px solid #0d6efd; border-radius: 4px; font-size: 8.5px; line-height: 1.6;">
        @php $taxaMedia = $porCurso->avg('taxa_empregabilidade') ?? 0; @endphp
        <p><strong>Taxa média de empregabilidade:</strong> {{ round($taxaMedia, 2) }}%</p>

        @if($taxaGeral < 50)
            <p><strong>Atenção:</strong> a taxa geral está abaixo de 50%. Recomenda-se rever os currículos e fortalecer a ligação com o mercado.</p>
        @else
            <p><strong>Boa performance:</strong> a taxa geral está acima de 50%. Continue fortalecendo a relação com o mercado.</p>
        @endif

        @if($piores->count() > 0)
            <p><strong>Cursos críticos:</strong> priorizar revisão curricular e parcerias com empresas.</p>
        @endif

        <p><strong>Networking:</strong> fortalecer o programa de estágios e a rede de contactos com empresas parceiras.</p>
    </div>

@endsection