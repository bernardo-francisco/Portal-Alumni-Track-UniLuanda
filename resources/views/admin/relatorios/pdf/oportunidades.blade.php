@extends('layouts.pdf')

@section('title', 'Relatório de Oportunidades')
@section('report_name', 'Relatório de Oportunidades')
@section('footer_right', 'Análise de oportunidades')

@php
    $labels = [
        'emprego' => 'Emprego',
        'estagio' => 'Estágio',
        'bolsa'   => 'Bolsa',
        'curso'   => 'Curso',
        'evento'  => 'Evento',
    ];
@endphp

@section('content')

    <div class="doc-title">Relatório de Oportunidades</div>
    <div class="doc-subtitle">
        Análise das oportunidades e candidaturas
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalOportunidades }}</span>
                <span class="stat-label">Total Oportunidades</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $oportunidadesAtivas }}</span>
                <span class="stat-label">Ativas</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $oportunidadesExpiradas }}</span>
                <span class="stat-label">Expiradas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $totalCandidaturas }}</span>
                <span class="stat-label">Total Candidaturas</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Status das Candidaturas</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 25%;">
            <col style="width: 25%;">
        </colgroup>

        <thead>
            <tr>
                <th>Status</th>
                <th class="text-center">Quantidade</th>
                <th class="text-center">Percentual</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td><span class="badge badge-warning">Pendente</span></td>
                <td class="text-center">{{ $candidaturasPendentes }}</td>
                <td class="text-center">
                    {{ $totalCandidaturas > 0 ? round(($candidaturasPendentes / $totalCandidaturas) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr>
                <td><span class="badge badge-success">Aprovada</span></td>
                <td class="text-center">{{ $candidaturasAprovadas }}</td>
                <td class="text-center">
                    {{ $totalCandidaturas > 0 ? round(($candidaturasAprovadas / $totalCandidaturas) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr>
                <td><span class="badge badge-danger">Rejeitada</span></td>
                <td class="text-center">{{ $candidaturasRejeitadas }}</td>
                <td class="text-center">
                    {{ $totalCandidaturas > 0 ? round(($candidaturasRejeitadas / $totalCandidaturas) * 100, 1) : 0 }}%
                </td>
            </tr>
            <tr style="background: #f8fafc;">
                <td><strong>Total</strong></td>
                <td class="text-center"><strong>{{ $totalCandidaturas }}</strong></td>
                <td class="text-center"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Oportunidades por Tipo</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 25%;">
            <col style="width: 25%;">
        </colgroup>

        <thead>
            <tr>
                <th>Tipo</th>
                <th class="text-center">Quantidade</th>
                <th class="text-center">Percentual</th>
            </tr>
        </thead>

        <tbody>
            @forelse($porTipo as $tipo)
                <tr>
                    <td><strong>{{ $tipo->tipo_label }}</strong></td>
                    <td class="text-center">{{ $tipo->total }}</td>
                    <td class="text-center">
                        {{ $totalOportunidades > 0 ? round(($tipo->total / $totalOportunidades) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 15px;">
                        Nenhum tipo encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Top 5 Oportunidades com Mais Candidaturas</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 35%;">
            <col style="width: 12%;">
            <col style="width: 18%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Empresa</th>
                <th class="text-center">Candidaturas</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($topOportunidades as $index => $op)
                <tr>
                    <td class="text-center">#{{ $index + 1 }}</td>
                    <td><strong>{{ Str::limit($op->titulo, 35, '…') }}</strong></td>
                    <td>{{ $labels[$op->tipo] ?? $op->tipo }}</td>
                    <td>{{ Str::limit($op->empresa ?? '-', 20, '…') }}</td>
                    <td class="text-center">
                        <span class="badge badge-primary">{{ $op->candidaturas_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($op->is_active)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-danger">Expirada</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma oportunidade encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Oportunidades por Unidade</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 60%;">
            <col style="width: 20%;">
            <col style="width: 20%;">
        </colgroup>

        <thead>
            <tr>
                <th>Unidade</th>
                <th class="text-center">Oportunidades</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($porUnidade as $unidade)
                <tr>
                    <td><strong>{{ $unidade->sigla }} - {{ $unidade->nome }}</strong></td>
                    <td class="text-center">{{ $unidade->total_oportunidades ?? 0 }}</td>
                    <td class="text-center">
                        @if(($unidade->total_oportunidades ?? 0) > 0)
                            <span class="badge badge-success">Com oportunidades</span>
                        @else
                            <span class="badge badge-secondary">Sem oportunidades</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma unidade encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Lista Completa de Oportunidades</div>

    <table class="report-table" style="font-size: 7.5px;">
        <colgroup>
            <col style="width: 28%;">
            <col style="width: 10%;">
            <col style="width: 18%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
        </colgroup>

        <thead>
            <tr>
                <th>Título</th>
                <th>Tipo</th>
                <th>Empresa</th>
                <th>Unidade</th>
                <th class="text-center">Vagas</th>
                <th class="text-center">Status</th>
                <th class="text-center">Cand.</th>
                <th class="text-center">Data</th>
            </tr>
        </thead>

        <tbody>
            @forelse($oportunidades as $op)
                <tr>
                    <td><strong>{{ Str::limit($op->titulo, 30, '…') }}</strong></td>
                    <td>{{ $labels[$op->tipo] ?? $op->tipo }}</td>
                    <td>{{ Str::limit($op->empresa ?? '-', 18, '…') }}</td>
                    <td>{{ $op->unidade->sigla ?? '-' }}</td>
                    <td class="text-center">{{ $op->vagas ?? '-' }}</td>
                    <td class="text-center">
                        @if($op->is_active)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-danger">Expirada</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-primary">{{ $op->candidaturas_count }}</span>
                    </td>
                    <td class="text-center nowrap">{{ $op->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma oportunidade encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Candidaturas Recentes (Últimas 20)</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 25%;">
            <col style="width: 35%;">
            <col style="width: 20%;">
            <col style="width: 20%;">
        </colgroup>

        <thead>
            <tr>
                <th>Egresso</th>
                <th>Oportunidade</th>
                <th class="text-center">Status</th>
                <th class="text-center">Data</th>
            </tr>
        </thead>

        <tbody>
            @forelse($candidaturasRecentes as $cand)

                @php
                    $statusClass = match ($cand->status) {
                        'pendente'  => 'badge-warning',
                        'aprovado'  => 'badge-success',
                        'rejeitado' => 'badge-danger',
                        default     => 'badge-secondary',
                    };
                @endphp

                <tr>
                    <td>{{ Str::limit($cand->egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($cand->oportunidade->titulo ?? '-', 35, '…') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $statusClass }}">{{ ucfirst($cand->status ?? '—') }}</span>
                    </td>
                    <td class="text-center nowrap">{{ $cand->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma candidatura recente.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Recomendações</div>

    <div style="padding: 12px; background: #eff6ff; border-left: 4px solid #0d6efd; border-radius: 4px; font-size: 8.5px; line-height: 1.6;">
        <p>
            <strong>Taxa de conversão:</strong>
            {{ $totalOportunidades > 0 ? round(($totalCandidaturas / $totalOportunidades), 1) : 0 }}
            candidaturas por oportunidade
        </p>

        @php
            $topTipos = $porTipo->sortByDesc('total')->take(2);
            $tiposNomes = $topTipos->pluck('tipo_label')->implode(' e ');
        @endphp

        <p>
            <strong>Distribuição:</strong>
            os tipos mais comuns são {{ $tiposNomes ?: 'nenhum tipo registado' }}.
        </p>

        @if($oportunidadesAtivas > 0)
            <p><strong>Oportunidades ativas:</strong> {{ $oportunidadesAtivas }} oportunidades estão ativas.</p>
        @endif

        @if($candidaturasPendentes > 10)
            <p><strong>Atenção:</strong> {{ $candidaturasPendentes }} candidaturas pendentes. Recomenda-se agilizar a análise.</p>
        @endif
    </div>

@endsection