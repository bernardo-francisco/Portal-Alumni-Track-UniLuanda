@extends('layouts.pdf')

@section('title', 'Relatório Completo — UniLuanda')
@section('report_name', 'Relatório Completo Consolidado')
@section('footer_right', 'Documento consolidado — uso interno')

@push('styles')
<style>
    /* ============================================================
       CONTROLO DE QUEBRAS DE PÁGINA (DomPDF)
    ============================================================ */
    .section-title {
        page-break-after: avoid;   /* não deixa o título sozinho no fim */
        page-break-inside: avoid;
        margin-top: 10px;
    }

    .section-block {
        page-break-inside: avoid;  /* nunca dividir uma secção ao meio */
        margin-bottom: 12px;
    }

    .report-table tr {
        page-break-inside: avoid;  /* nunca dividir uma linha ao meio */
    }

    .report-table thead {
        display: table-header-group; /* repete o header em cada página nova */
    }

    .report-table {
        page-break-inside: auto;   /* tabelas grandes PODEM quebrar, mas sem partir linhas */
    }

    /* Bloco final nunca fica sozinho */
    .end-note {
        page-break-inside: avoid;
        margin-top: 20px;
    }
</style>
@endpush

@section('content')

    {{-- ============================================================
         CAPA / TÍTULO PRINCIPAL
    ============================================================ --}}
    <div class="doc-title">Relatório Completo Consolidado</div>
    <div class="doc-subtitle">
        Universidade de Luanda — Sistema Alumni Track &middot; {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- ============================================================
         1. RESUMO EXECUTIVO
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">1. Resumo Executivo</div>

        <table class="stats-table">
            <tr>
                <td class="stat-box">
                    <span class="stat-value">{{ $totalEgressos }}</span>
                    <span class="stat-label">Egressos Registados</span>
                </td>
                <td class="stat-box stat-box-success">
                    <span class="stat-value">{{ $empregados }}</span>
                    <span class="stat-label">Empregados</span>
                </td>
                <td class="stat-box stat-box-info">
                    <span class="stat-value">{{ $taxaEmpregabilidade }}%</span>
                    <span class="stat-label">Taxa de Empregabilidade</span>
                </td>
                <td class="stat-box stat-box-warning">
                    <span class="stat-value">{{ $totalPaises }}</span>
                    <span class="stat-label">Países Alcançados</span>
                </td>
            </tr>
        </table>

        <table class="stats-table">
            <tr>
                <td class="stat-box stat-box-info">
                    <span class="stat-value">{{ $totalConexoes }}</span>
                    <span class="stat-label">Conexões na Rede</span>
                </td>
                <td class="stat-box stat-box-info">
                    <span class="stat-value">{{ $totalMensagens }}</span>
                    <span class="stat-label">Mensagens Trocadas</span>
                </td>
                <td class="stat-box stat-box-info">
                    <span class="stat-value">{{ $totalChamadas }}</span>
                    <span class="stat-label">Chamadas Realizadas</span>
                </td>
                <td class="stat-box stat-box-info">
                    <span class="stat-value">{{ $totalAudios }}</span>
                    <span class="stat-label">Áudios Enviados</span>
                </td>
            </tr>
        </table>

        <table class="stats-table">
            <tr>
                <td class="stat-box stat-box-warning">
                    <span class="stat-value">{{ $totalCandidaturas }}</span>
                    <span class="stat-label">Candidaturas</span>
                </td>
                <td class="stat-box stat-box-warning">
                    <span class="stat-value">{{ $totalOportunidades }}</span>
                    <span class="stat-label">Oportunidades</span>
                </td>
                <td class="stat-box stat-box-warning">
                    <span class="stat-value">{{ $totalEventos }}</span>
                    <span class="stat-label">Eventos</span>
                </td>
                <td class="stat-box stat-box-warning">
                    <span class="stat-value">{{ $totalInscricoes }}</span>
                    <span class="stat-label">Inscrições</span>
                </td>
            </tr>
        </table>

        <table class="stats-table">
            <tr>
                <td class="stat-box stat-box-success">
                    <span class="stat-value">{{ $totalFeedbacks }}</span>
                    <span class="stat-label">Feedbacks</span>
                </td>
                <td class="stat-box stat-box-success">
                    <span class="stat-value">{{ $totalServicos }}</span>
                    <span class="stat-label">Serviços Solicitados</span>
                </td>
                <td class="stat-box stat-box-success">
                    <span class="stat-value">{{ $totalNotificacoes }}</span>
                    <span class="stat-label">Notificações</span>
                </td>
                <td class="stat-box stat-box-success">
                    <span class="stat-value">{{ $totalProfissionais }}</span>
                    <span class="stat-label">Registos Profissionais</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         2. EGRESSOS POR UNIDADE ORGÂNICA
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">2. Egressos por Unidade Orgânica</div>

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
                    <th>Unidade</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Empregados</th>
                    <th class="text-center">Taxa</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($porUnidade as $unidade)
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 15px;">
                            Nenhuma unidade encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================================================
         3. STATUS DOS EGRESSOS
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">3. Status dos Egressos</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 25%;">
                <col style="width: 25%;">
            </colgroup>

            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Percentual</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $statusLabels = [
                        'active'       => 'Ativo',
                        'inactive'     => 'Inativo',
                        'blocked'      => 'Bloqueado',
                        'lost_contact' => 'Sem Contacto',
                    ];
                @endphp

                @forelse($porStatus as $status)
                    <tr>
                        <td>
                            <span class="badge {{ $status->status == 'active' ? 'badge-active' : 'badge-inactive' }}">
                                {{ $statusLabels[$status->status] ?? ucfirst($status->status) }}
                            </span>
                        </td>
                        <td class="text-center"><strong>{{ $status->total }}</strong></td>
                        <td class="text-center">
                            {{ $totalEgressos > 0 ? round(($status->total / $totalEgressos) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted" style="padding: 15px;">
                            Sem dados de status.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================================================
         4. SITUAÇÃO PROFISSIONAL (só se tiver dados)
    ============================================================ --}}
    @if($porTipoEmprego->count() > 0)
        <div class="section-block">
            <div class="section-title">4. Situação Profissional</div>

            <table class="report-table">
                <colgroup>
                    <col style="width: 50%;">
                    <col style="width: 25%;">
                    <col style="width: 25%;">
                </colgroup>

                <thead>
                    <tr>
                        <th>Tipo de Emprego</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Percentual</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $empLabels = [
                            'full_time'     => 'Tempo Inteiro',
                            'part_time'     => 'Tempo Parcial',
                            'freelance'     => 'Freelance',
                            'self_employed' => 'Autónomo',
                            'unemployed'    => 'Desempregado',
                            'student'       => 'A Estudar',
                            'unknown'       => 'Desconhecido',
                        ];
                        $totalProf = $porTipoEmprego->sum('total');
                    @endphp

                    @foreach($porTipoEmprego as $emprego)
                        <tr>
                            <td>{{ $empLabels[$emprego->tipo_emprego] ?? $emprego->tipo_emprego }}</td>
                            <td class="text-center"><strong>{{ $emprego->total }}</strong></td>
                            <td class="text-center">
                                {{ $totalProf > 0 ? round(($emprego->total / $totalProf) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="page-break"></div>

    {{-- ============================================================
         5. DISTRIBUIÇÃO GEOGRÁFICA (só se tiver dados)
    ============================================================ --}}
    @if($porPais->count() > 0 || !empty($mapaBase64))
        <div class="section-block">
            <div class="section-title">5. Distribuição Geográfica</div>

            @if(!empty($mapaBase64))
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="{{ $mapaBase64 }}" alt="Mapa" style="max-width: 100%; height: auto;">
                    <p style="font-size: 7.5px; color: #6c757d; margin-top: 4px;">
                        <span style="color: #dc3545;">●</span> Localização do Egresso
                    </p>
                </div>
            @endif

            @if($porPais->count() > 0)
                <table class="report-table">
                    <colgroup>
                        <col style="width: 50%;">
                        <col style="width: 25%;">
                        <col style="width: 25%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th>País</th>
                            <th class="text-center">Total de Egressos</th>
                            <th class="text-center">Percentual</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($porPais as $pais)
                            <tr>
                                <td>{{ $pais->pais }}</td>
                                <td class="text-center"><strong>{{ $pais->total }}</strong></td>
                                <td class="text-center">
                                    {{ $totalEgressos > 0 ? round(($pais->total / $totalEgressos) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- ============================================================
         6. EMPREGABILIDADE POR CURSO
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">6. Empregabilidade por Curso</div>

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
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         7. RANKING DE UNIDADES
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">7. Ranking de Unidades</div>

        @php $ranking = $porUnidade->sortByDesc('taxa_empregabilidade'); @endphp

        <table class="report-table">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 50%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
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
                @forelse($ranking as $unidade)
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
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                            Sem unidades para rankear.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================================================
         8. OPORTUNIDADES E CANDIDATURAS
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">8. Oportunidades e Candidaturas</div>

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
                    <td><strong>Total de Oportunidades</strong></td>
                    <td class="text-center">{{ $totalOportunidades }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Oportunidades Ativas</td>
                    <td class="text-center">{{ $oportunidadesAtivas }}</td>
                    <td class="text-center">
                        {{ $totalOportunidades > 0 ? round(($oportunidadesAtivas / $totalOportunidades) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Oportunidades Expiradas</td>
                    <td class="text-center">{{ $oportunidadesExpiradas }}</td>
                    <td class="text-center">
                        {{ $totalOportunidades > 0 ? round(($oportunidadesExpiradas / $totalOportunidades) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Total de Candidaturas</strong></td>
                    <td class="text-center">{{ $totalCandidaturas }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Candidaturas Pendentes</td>
                    <td class="text-center">{{ $candidaturasPendentes }}</td>
                    <td class="text-center">
                        {{ $totalCandidaturas > 0 ? round(($candidaturasPendentes / $totalCandidaturas) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Candidaturas Aprovadas</td>
                    <td class="text-center">{{ $candidaturasAprovadas }}</td>
                    <td class="text-center">
                        {{ $totalCandidaturas > 0 ? round(($candidaturasAprovadas / $totalCandidaturas) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>Candidaturas Rejeitadas</td>
                    <td class="text-center">{{ $candidaturasRejeitadas }}</td>
                    <td class="text-center">
                        {{ $totalCandidaturas > 0 ? round(($candidaturasRejeitadas / $totalCandidaturas) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         9. TOP 10 OPORTUNIDADES (só se tiver dados)
    ============================================================ --}}
    @if($topOportunidades->count() > 0)
        <div class="section-block">
            <div class="section-title">9. Top 10 Oportunidades com Mais Candidaturas</div>

            <table class="report-table">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 40%;">
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                </colgroup>

                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Empresa</th>
                        <th class="text-center">Cand.</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($topOportunidades as $index => $op)
                        <tr>
                            <td class="text-center">#{{ $index + 1 }}</td>
                            <td><strong>{{ Str::limit($op->titulo, 35, '…') }}</strong></td>
                            <td>{{ ucfirst($op->tipo ?? '-') }}</td>
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============================================================
         10. EVENTOS E INSCRIÇÕES
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">10. Eventos e Inscrições</div>

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
                    <td><strong>Total de Eventos</strong></td>
                    <td class="text-center">{{ $totalEventos }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Eventos Ativos</td>
                    <td class="text-center">{{ $eventosAtivos }}</td>
                    <td class="text-center">
                        {{ $totalEventos > 0 ? round(($eventosAtivos / $totalEventos) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Total de Inscrições</strong></td>
                    <td class="text-center">{{ $totalInscricoes }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Presenças Confirmadas</td>
                    <td class="text-center">{{ $inscricoesPresentes }}</td>
                    <td class="text-center">
                        {{ $totalInscricoes > 0 ? round(($inscricoesPresentes / $totalInscricoes) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ============================================================
         11. COMUNICAÇÃO E ENGAJAMENTO
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">11. Comunicação e Engajamento</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 40%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
            </colgroup>

            <thead>
                <tr>
                    <th>Módulo</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Ativos</th>
                    <th class="text-center">%</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td><strong>Conexões</strong></td>
                    <td class="text-center">{{ $totalConexoes }}</td>
                    <td class="text-center">{{ $conexoesAceites }}</td>
                    <td class="text-center">
                        {{ $totalConexoes > 0 ? round(($conexoesAceites / $totalConexoes) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Mensagens</strong></td>
                    <td class="text-center">{{ $totalMensagens }}</td>
                    <td class="text-center">{{ $mensagensLidas }}</td>
                    <td class="text-center">
                        {{ $totalMensagens > 0 ? round(($mensagensLidas / $totalMensagens) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Chamadas</strong></td>
                    <td class="text-center">{{ $totalChamadas }}</td>
                    <td class="text-center">{{ $chamadasVideo }}</td>
                    <td class="text-center">
                        {{ $totalChamadas > 0 ? round(($chamadasVideo / $totalChamadas) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Áudios</strong></td>
                    <td class="text-center">{{ $totalAudios }}</td>
                    <td class="text-center">{{ $audiosLidos }}</td>
                    <td class="text-center">
                        {{ $totalAudios > 0 ? round(($audiosLidos / $totalAudios) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Notificações</strong></td>
                    <td class="text-center">{{ $totalNotificacoes }}</td>
                    <td class="text-center">{{ $notificacoesLidas }}</td>
                    <td class="text-center">
                        {{ $totalNotificacoes > 0 ? round(($notificacoesLidas / $totalNotificacoes) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         12. FEEDBACKS E SERVIÇOS
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">12. Feedbacks e Serviços</div>

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
                    <td><strong>Total de Feedbacks</strong></td>
                    <td class="text-center">{{ $totalFeedbacks }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Feedbacks Aprovados</td>
                    <td class="text-center">{{ $feedbacksAprovados }}</td>
                    <td class="text-center">
                        {{ $totalFeedbacks > 0 ? round(($feedbacksAprovados / $totalFeedbacks) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td><strong>Total de Serviços</strong></td>
                    <td class="text-center">{{ $totalServicos }}</td>
                    <td class="text-center">100%</td>
                </tr>
                <tr>
                    <td>Serviços Atendidos</td>
                    <td class="text-center">{{ $servicosAtendidos }}</td>
                    <td class="text-center">
                        {{ $totalServicos > 0 ? round(($servicosAtendidos / $totalServicos) * 100, 1) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ============================================================
         13. ATIVIDADE RECENTE (ÚLTIMOS 30 DIAS)
    ============================================================ --}}
    <div class="section-block">
        <div class="section-title">13. Atividade Recente (Últimos 30 dias)</div>

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
                <tr>
                    <td>Egressos Registados</td>
                    <td class="text-center">{{ $totalEgressos }}</td>
                    <td class="text-center">{{ $egressosRecentes }}</td>
                </tr>
                <tr>
                    <td>Conexões Aceites</td>
                    <td class="text-center">{{ $totalConexoes }}</td>
                    <td class="text-center">{{ $conexoesRecentes }}</td>
                </tr>
                <tr>
                    <td>Mensagens Trocadas</td>
                    <td class="text-center">{{ $totalMensagens }}</td>
                    <td class="text-center">{{ $mensagensRecentes }}</td>
                </tr>
                <tr>
                    <td>Chamadas Realizadas</td>
                    <td class="text-center">{{ $totalChamadas }}</td>
                    <td class="text-center">{{ $chamadasRecentes }}</td>
                </tr>
                <tr>
                    <td>Áudios Enviados</td>
                    <td class="text-center">{{ $totalAudios }}</td>
                    <td class="text-center">{{ $audiosRecentes }}</td>
                </tr>
                <tr>
                    <td>Notificações Geradas</td>
                    <td class="text-center">{{ $totalNotificacoes }}</td>
                    <td class="text-center">{{ $notificacoesRecentes }}</td>
                </tr>
                <tr>
                    <td>Candidaturas Submetidas</td>
                    <td class="text-center">{{ $totalCandidaturas }}</td>
                    <td class="text-center">{{ $candidaturasRecentes }}</td>
                </tr>
                <tr>
                    <td>Eventos Criados</td>
                    <td class="text-center">{{ $totalEventos }}</td>
                    <td class="text-center">{{ $eventosRecentes }}</td>
                </tr>
                <tr>
                    <td>Inscrições em Eventos</td>
                    <td class="text-center">{{ $totalInscricoes }}</td>
                    <td class="text-center">{{ $inscricoesRecentes }}</td>
                </tr>
                <tr>
                    <td>Feedbacks Submetidos</td>
                    <td class="text-center">{{ $totalFeedbacks }}</td>
                    <td class="text-center">{{ $feedbacksRecentes }}</td>
                </tr>
                <tr>
                    <td>Serviços Solicitados</td>
                    <td class="text-center">{{ $totalServicos }}</td>
                    <td class="text-center">{{ $servicosRecentes }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- ============================================================
         14. LISTA DE EGRESSOS (AMOSTRA) — só se tiver dados
    ============================================================ --}}
    @if($egressos->count() > 0)
        <div class="section-block">
            <div class="section-title">14. Lista de Egressos (amostra de {{ $egressos->count() }})</div>

            <table class="report-table" style="font-size: 7.5px;">
                <colgroup>
                    <col style="width: 5%;">
                    <col style="width: 12%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 8%;">
                    <col style="width: 10%;">
                    <col style="width: 15%;">
                    <col style="width: 10%;">
                </colgroup>

                <thead>
                    <tr>
                        <th class="text-center">Foto</th>
                        <th>Nº Processo</th>
                        <th>Nome</th>
                        <th>Curso</th>
                        <th class="text-center">Unid.</th>
                        <th class="text-center">Status</th>
                        <th>Localização</th>
                        <th>Emprego</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($egressos as $egresso)
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
                            <td class="fw-bold nowrap">{{ $egresso->numero_processo ?? '-' }}</td>
                            <td>{{ Str::limit($egresso->nome_completo ?? '-', 28, '…') }}</td>
                            <td>{{ Str::limit($egresso->curso->nome ?? '-', 25, '…') }}</td>
                            <td class="text-center">{{ $egresso->curso->unidade->sigla ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $egresso->status == 'active' ? 'badge-active' : 'badge-inactive' }}">
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
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============================================================
         FIM — NOTA FINAL
    ============================================================ --}}
    <div class="end-note" style="padding: 12px; background: #f8f9fa; border-left: 4px solid #0d6efd; border-radius: 4px; font-size: 8px; color: #6c757d; text-align: center;">
        <strong>— FIM DO RELATÓRIO COMPLETO —</strong><br>
        Documento gerado automaticamente pelo sistema UniLuanda Alumni Track<br>
        {{ now()->format('d/m/Y \à\s H:i') }}
    </div>

@endsection