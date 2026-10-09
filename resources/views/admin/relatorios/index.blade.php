@extends('layouts.admin')

@section('title', 'Relatórios')

@section('page_title', '📊 Relatórios e Estatísticas')
@section('page_subtitle', 'Análise consolidada dos dados dos egressos')

@section('content')

{{-- ============================================================
     ESTATÍSTICAS PRINCIPAIS
============================================================ --}}
<div class="row g-3 mb-4">

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrap">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $totalEgressos }}</div>
                <div class="stat-label">Total de Egressos</div>
                <small class="stat-desc">Registados na plataforma</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrap">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $empregados }}</div>
                <div class="stat-label">Empregados</div>
                <small class="stat-desc">Com emprego ativo</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrap">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $taxaEmpregabilidade }}%</div>
                <div class="stat-label">Taxa de Empregabilidade</div>
                <small class="stat-desc">Percentagem global</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-card stat-card-info">
            <div class="stat-icon-wrap">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value">{{ $porCurso->count() }}</div>
                <div class="stat-label">Cursos</div>
                <small class="stat-desc">Com egressos registados</small>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     TAXA DE EMPREGABILIDADE POR CURSO
============================================================ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Taxa de Empregabilidade por Curso</h6>
                    <small class="text-muted">Análise detalhada por curso</small>
                </div>
            </div>
            <span class="badge bg-primary rounded-pill px-3">
                {{ $porCurso->count() }} cursos
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 report-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Curso</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Empregados</th>
                        <th class="text-center">Taxa</th>
                        <th class="pe-4" style="min-width: 180px;">Progresso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porCurso as $curso)
                        @php
                            $taxa = $curso->taxa_empregabilidade ?? 0;
                            $corTaxa = $taxa >= 70 ? 'success' : ($taxa >= 40 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="course-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <strong>{{ $curso->nome }}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill">{{ $curso->egressos_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill">{{ $curso->empregados_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $corTaxa }}-subtle text-{{ $corTaxa }}-emphasis border border-{{ $corTaxa }}-subtle fw-bold">
                                    {{ $taxa }}%
                                </span>
                            </td>
                            <td class="pe-4">
                                <div class="progress" style="height: 8px; border-radius: 10px;">
                                    <div class="progress-bar bg-{{ $corTaxa }}"
                                         role="progressbar"
                                         style="width: {{ $taxa }}%; border-radius: 10px;"
                                         aria-valuenow="{{ $taxa }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state-icon mb-2">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <p class="text-muted mb-0">Sem dados por curso.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



{{-- ============================================================
     ÁREAS DE ATUAÇÃO E UNIDADES
============================================================ --}}
<div class="row g-4 mb-4">

    {{-- Áreas de Atuação --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Áreas de Atuação</h6>
                    </div>
                    <span class="badge bg-info rounded-pill px-3">
                        {{ $areasAtuacao->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 report-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Área</th>
                                <th class="text-center">Total</th>
                                <th class="pe-4">Distribuição</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($areasAtuacao as $area)
                                @php
                                    $percentual = $totalEgressos > 0
                                        ? round(($area->total / $totalEgressos) * 100, 1)
                                        : 0;
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="area-dot"></div>
                                            <strong>{{ $area->area_atuacao }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">{{ $area->total }}</span>
                                    </td>
                                    <td class="pe-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                                                <div class="progress-bar bg-info"
                                                     role="progressbar"
                                                     style="width: {{ $percentual }}%; border-radius: 10px;">
                                                </div>
                                            </div>
                                            <small class="text-muted fw-bold" style="min-width: 45px;">
                                                {{ $percentual }}%
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <p class="text-muted mb-0">Sem dados de áreas de atuação.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Unidades Orgânicas --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="section-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-building"></i>
                        </div>
                        <h6 class="mb-0 fw-bold">Por Unidade Orgânica</h6>
                    </div>
                    <span class="badge bg-success rounded-pill px-3">
                        {{ $porUnidade->count() }}
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 report-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Unidade</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Empregados</th>
                                <th class="pe-4 text-center">Taxa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($porUnidade as $unidade)
                                @php
                                    $taxa = $unidade->taxa_empregabilidade ?? 0;
                                    $corTaxa = $taxa >= 70 ? 'success' : ($taxa >= 40 ? 'warning' : 'danger');
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="course-icon bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <strong>{{ $unidade->nome }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill">{{ $unidade->total_egressos }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success rounded-pill">{{ $unidade->total_empregados }}</span>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <span class="badge bg-{{ $corTaxa }}-subtle text-{{ $corTaxa }}-emphasis border border-{{ $corTaxa }}-subtle fw-bold">
                                            {{ $taxa }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <p class="text-muted mb-0">Sem dados por unidade.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>


{{-- ============================================================
     EXPORTAÇÃO DE RELATÓRIOS
============================================================ --}}
@php
    $relatorios = [
        // ---- Principais ----
        [
            'titulo' => 'Egressos',
            'icone'  => 'fa-users',
            'cor'    => 'primary',
            'desc'   => 'Lista completa com fotos',
            'pdf'    => route('admin.relatorios.pdf.egressos'),
            'excel'  => route('admin.relatorios.excel.egressos'),
        ],
        [
            'titulo' => 'Profissionais',
            'icone'  => 'fa-briefcase',
            'cor'    => 'success',
            'desc'   => 'Registos profissionais',
            'pdf'    => route('admin.relatorios.pdf.profissionais'),
            'excel'  => route('admin.relatorios.excel.profissionais'),
        ],
        [
            'titulo' => 'Localizações',
            'icone'  => 'fa-map-marked-alt',
            'cor'    => 'warning',
            'desc'   => 'Distribuição geográfica',
            'pdf'    => route('admin.relatorios.pdf.localizacoes'),
            'excel'  => route('admin.relatorios.excel.localizacoes'),
        ],
        [
            'titulo' => 'Empregabilidade',
            'icone'  => 'fa-chart-line',
            'cor'    => 'danger',
            'desc'   => 'Taxa global e por curso',
            'pdf'    => route('admin.relatorios.pdf.empregabilidade'),
            'excel'  => route('admin.relatorios.excel.empregabilidade'),
        ],
        [
            'titulo' => 'Por Curso',
            'icone'  => 'fa-graduation-cap',
            'cor'    => 'primary',
            'desc'   => 'Análise por curso',
            'pdf'    => route('admin.relatorios.pdf.por-curso'),
            'excel'  => route('admin.relatorios.excel.por-curso'),
        ],
        [
            'titulo' => 'Por Unidade',
            'icone'  => 'fa-building',
            'cor'    => 'success',
            'desc'   => 'Análise por unidade',
            'pdf'    => route('admin.relatorios.pdf.por-unidade'),
            'excel'  => route('admin.relatorios.excel.por-unidade'),
        ],
        [
            'titulo' => 'Oportunidades',
            'icone'  => 'fa-briefcase',
            'cor'    => 'info',
            'desc'   => 'Oportunidades e candidaturas',
            'pdf'    => route('admin.relatorios.pdf.oportunidades'),
            'excel'  => route('admin.relatorios.excel.oportunidades'),
        ],

        // ---- Comunicação ----
        [
            'titulo' => 'Conexões',
            'icone'  => 'fa-handshake',
            'cor'    => 'primary',
            'desc'   => 'Rede de contactos',
            'pdf'    => route('admin.relatorios.pdf.conexoes'),
            'excel'  => route('admin.relatorios.excel.conexoes'),
        ],
        [
            'titulo' => 'Mensagens',
            'icone'  => 'fa-envelope',
            'cor'    => 'info',
            'desc'   => 'Mensagens trocadas',
            'pdf'    => route('admin.relatorios.pdf.mensagens'),
            'excel'  => route('admin.relatorios.excel.mensagens'),
        ],
        [
            'titulo' => 'Chamadas',
            'icone'  => 'fa-phone',
            'cor'    => 'success',
            'desc'   => 'Voz e vídeo',
            'pdf'    => route('admin.relatorios.pdf.chamadas'),
            'excel'  => route('admin.relatorios.excel.chamadas'),
        ],
        [
            'titulo' => 'Áudios',
            'icone'  => 'fa-microphone',
            'cor'    => 'warning',
            'desc'   => 'Mensagens de voz',
            'pdf'    => route('admin.relatorios.pdf.audios'),
            'excel'  => route('admin.relatorios.excel.audios'),
        ],
        [
            'titulo' => 'Notificações',
            'icone'  => 'fa-bell',
            'cor'    => 'danger',
            'desc'   => 'Notificações do sistema',
            'pdf'    => route('admin.relatorios.pdf.notificacoes'),
            'excel'  => route('admin.relatorios.excel.notificacoes'),
        ],

        // ---- Atividades ----
        [
            'titulo' => 'Candidaturas',
            'icone'  => 'fa-file-signature',
            'cor'    => 'primary',
            'desc'   => 'Candidaturas submetidas',
            'pdf'    => route('admin.relatorios.pdf.candidaturas'),
            'excel'  => route('admin.relatorios.excel.candidaturas'),
        ],
        [
            'titulo' => 'Eventos',
            'icone'  => 'fa-calendar',
            'cor'    => 'success',
            'desc'   => 'Eventos organizados',
            'pdf'    => route('admin.relatorios.pdf.eventos'),
            'excel'  => route('admin.relatorios.excel.eventos'),
        ],
        [
            'titulo' => 'Inscrições',
            'icone'  => 'fa-ticket-alt',
            'cor'    => 'info',
            'desc'   => 'Inscrições em eventos',
            'pdf'    => route('admin.relatorios.pdf.inscricoes'),
            'excel'  => route('admin.relatorios.excel.inscricoes'),
        ],
        [
            'titulo' => 'Feedbacks',
            'icone'  => 'fa-comment-dots',
            'cor'    => 'warning',
            'desc'   => 'Feedbacks dos egressos',
            'pdf'    => route('admin.relatorios.pdf.feedbacks'),
            'excel'  => route('admin.relatorios.excel.feedbacks'),
        ],
        [
            'titulo' => 'Serviços',
            'icone'  => 'fa-concierge-bell',
            'cor'    => 'danger',
            'desc'   => 'Pedidos de serviços',
            'pdf'    => route('admin.relatorios.pdf.servicos'),
            'excel'  => route('admin.relatorios.excel.servicos'),
        ],

        // ---- Análise / Roadmap ----
        [
            'titulo' => 'Pesquisas',
            'icone'  => 'fa-poll',
            'cor'    => 'info',
            'desc'   => 'Inquéritos e respostas',
            'pdf'    => route('admin.relatorios.pdf.pesquisas'),
            'excel'  => route('admin.relatorios.excel.pesquisas'),
        ],
        [
            'titulo' => 'Mural',
            'icone'  => 'fa-newspaper',
            'cor'    => 'primary',
            'desc'   => 'Publicações do mural',
            'pdf'    => route('admin.relatorios.pdf.mural'),
            'excel'  => route('admin.relatorios.excel.mural'),
        ],
        [
            'titulo' => 'Mapa',
            'icone'  => 'fa-map-marked-alt',
            'cor'    => 'warning',
            'desc'   => 'Distribuição geográfica',
            'pdf'    => route('admin.relatorios.pdf.mapa'),
            'excel'  => route('admin.relatorios.excel.mapa'),
        ],
        [
            'titulo' => 'Atividade',
            'icone'  => 'fa-chart-pie',
            'cor'    => 'primary',
            'desc'   => 'Atividade geral da plataforma',
            'pdf'    => route('admin.relatorios.pdf.atividade'),
            'excel'  => route('admin.relatorios.excel.atividade'),
        ],
        [
            'titulo' => 'Engajamento',
            'icone'  => 'fa-fire',
            'cor'    => 'danger',
            'desc'   => 'Engajamento dos egressos',
            'pdf'    => route('admin.relatorios.pdf.engajamento'),
            'excel'  => route('admin.relatorios.excel.engajamento'),
        ],

        [
            'titulo' => 'Empresas',
            'icone'  => 'fa-building',
            'cor'    => 'primary',
            'desc'   => 'Empresas registadas e status',
            'pdf'    => route('admin.relatorios.pdf.empresas'),
            'excel'  => route('admin.relatorios.excel.empresas'),
        ],
        [
            'titulo' => 'Completo',
            'icone'  => 'fa-file-alt',
            'cor'    => 'dark',
            'desc'   => 'Todos os dados consolidados',
            'pdf'    => route('admin.relatorios.pdf.completo'),
            'excel'  => route('admin.relatorios.excel.completo'),
        ],
    ];
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex align-items-center gap-2">
            <div class="section-icon bg-dark bg-opacity-10 text-dark">
                <i class="fas fa-download"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">Exportar Relatórios</h6>
                <small class="text-muted">Descarregue em PDF ou Excel</small>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            @foreach($relatorios as $r)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="report-card h-100">
                        <div class="report-icon bg-{{ $r['cor'] }}">
                            <i class="fas {{ $r['icone'] }}"></i>
                        </div>
                        <h6 class="fw-bold mb-1">{{ $r['titulo'] }}</h6>
                        <p class="text-muted small mb-3">{{ $r['desc'] }}</p>

                        <div class="d-grid gap-2">
                            <a href="{{ $r['pdf'] }}"
                               class="btn btn-sm btn-{{ $r['cor'] }}"
                               target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                            <a href="{{ $r['excel'] }}"
                               class="btn btn-sm btn-outline-{{ $r['cor'] }}">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

