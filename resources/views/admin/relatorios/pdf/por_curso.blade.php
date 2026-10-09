@extends('layouts.pdf')

@section('title', 'Relatório por Curso')
@section('report_name', 'Relatório por Curso')
@section('footer_right', 'Análise por curso')

@php
    $totalCursos = $cursos->count();
    $totalEgressosGeral = $cursos->sum('egressos_count');
    $totalEmpregadosGeral = $cursos->sum('empregados_count');
    $taxaGeralPorCurso = $totalEgressosGeral > 0
        ? round(($totalEmpregadosGeral / $totalEgressosGeral) * 100, 2)
        : 0;
@endphp

@section('content')

    <div class="doc-title">Relatório por Curso</div>
    <div class="doc-subtitle">
        Análise consolidada dos egressos por curso
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalCursos }}</span>
                <span class="stat-label">Total de Cursos</span>
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
                <span class="stat-value">{{ $taxaGeralPorCurso }}%</span>
                <span class="stat-label">Taxa Geral</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Análise por Curso</div>

    @forelse($cursos as $curso)

        @php
            $taxa = $curso->taxa_empregabilidade ?? 0;
            $badgeClass = $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
            $statusLabel = $taxa >= 70 ? 'Excelente' : ($taxa >= 40 ? 'Regular' : 'Crítico');
        @endphp

        <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 12px; background: #f8f9fa;">
            <table style="width: 100%; margin-bottom: 6px;">
                <tr>
                    <td>
                        <strong style="font-size: 11px; color: #0d6efd;">{{ $curso->nome }}</strong>
                        <div style="font-size: 7.5px; color: #6c757d; margin-top: 2px;">
                            <strong>Unidade:</strong> {{ $curso->unidade->nome ?? 'N/A' }} &bull;
                            <strong>Sigla:</strong> {{ $curso->unidade->sigla ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <span class="badge {{ $badgeClass }}">{{ $taxa }}%</span>
                    </td>
                </tr>
            </table>

            <table class="report-table" style="font-size: 7.5px;">
                <colgroup>
                    <col style="width: 50%;">
                    <col style="width: 50%;">
                </colgroup>

                <thead>
                    <tr>
                        <th>Métrica</th>
                        <th class="text-center">Valor</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Total de Egressos</td>
                        <td class="text-center"><strong>{{ $curso->egressos_count }}</strong></td>
                    </tr>
                    <tr>
                        <td>Empregados</td>
                        <td class="text-center"><strong>{{ $curso->empregados_count }}</strong></td>
                    </tr>
                    <tr>
                        <td>Desempregados</td>
                        <td class="text-center"><strong>{{ $curso->egressos_count - $curso->empregados_count }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            @if(isset($curso->egressos) && $curso->egressos->count() > 0)
                <div style="margin-top: 8px;">
                    <p style="font-size: 8px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                        Egressos do Curso ({{ $curso->egressos->count() }})
                    </p>

                    <table class="report-table" style="font-size: 7px;">
                        <colgroup>
                            <col style="width: 5%;">
                            <col style="width: 30%;">
                            <col style="width: 12%;">
                            <col style="width: 13%;">
                            <col style="width: 20%;">
                            <col style="width: 20%;">
                        </colgroup>

                        <thead>
                            <tr>
                                <th class="text-center">Foto</th>
                                <th>Nome</th>
                                <th>Nº Processo</th>
                                <th class="text-center">Status</th>
                                <th>Localização</th>
                                <th>Emprego</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($curso->egressos->take(10) as $egresso)

                                @php $foto = fotoBase64($egresso->foto_url); @endphp

                                <tr>
                                    <td class="text-center">
                                        @if($foto)
                                            <span class="foto-wrap">
                                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                                            </span>
                                        @else
                                            <span class="foto-wrap">
                                                {{ strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($egresso->nome_completo ?? '-', 25, '…') }}</td>
                                    <td class="nowrap">{{ $egresso->numero_processo ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $egresso->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($egresso->status ?? '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($egresso->localizacaoAtual)
                                            {{ $egresso->localizacaoAtual->cidade }},
                                            {{ $egresso->localizacaoAtual->pais }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($egresso->profissionalAtual->cargo ?? '-', 18, '…') }}</td>
                                </tr>
                            @endforeach

                            @if($curso->egressos->count() > 10)
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="font-style: italic;">
                                        ... e mais {{ $curso->egressos->count() - 10 }} egressos
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @empty
        <div class="text-center text-muted" style="padding: 30px 0;">
            Nenhum curso encontrado.
        </div>
    @endforelse

@endsection