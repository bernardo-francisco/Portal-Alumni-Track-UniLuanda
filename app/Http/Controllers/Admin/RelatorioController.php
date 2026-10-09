<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\BaseExport;
use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use App\Models\Profissional;
use App\Models\Localizacao;
use App\Models\Oportunidade;
use App\Models\Candidatura;
use App\Models\Conexao;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\Evento;
use App\Models\InscricaoEvento;
use App\Models\Feedback;
use App\Models\Servico;
use App\Models\Pesquisa;
use App\Models\PesquisaPergunta;
use App\Models\PesquisaResposta;
use App\Models\MuralNoticia;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CompletoExport;

class RelatorioController extends Controller
{
    // ================================================================
    // PÁGINA PRINCIPAL
    // ================================================================
    public function index()
    {
        // ============================================================
        // EGRESSOS
        // ============================================================
        $totalEgressos = Egresso::whereHas('user', function($q) {
            $q->where('is_admin', false);
        })->count();

        $empregados = Egresso::whereHas('user', function($q) {
            $q->where('is_admin', false);
        })->where('status', 'active')->count();

        $taxaEmpregabilidade = $totalEgressos > 0 ? round(($empregados / $totalEgressos) * 100, 2) : 0;

        // ============================================================
        // EMPRESAS ✅ NOVO
        // ============================================================
        $totalEmpresas = Empresa::count();

        $empresasAprovadas = Empresa::where('status_validacao', 'aprovado')->count();
        $empresasPendentes = Empresa::where('status_validacao', 'pendente')->count();
        $empresasReprovadas = Empresa::where('status_validacao', 'reprovado')->count();

        $taxaAprovacaoEmpresas = $totalEmpresas > 0
            ? round(($empresasAprovadas / $totalEmpresas) * 100, 2)
            : 0;

        // ============================================================
        // POR CURSO
        // ============================================================
        $porCurso = Curso::withCount(['egressos' => function($q) {
            $q->whereHas('user', function($u) { $u->where('is_admin', false); });
        }])->get()->map(function($curso) {
            $empregadosCount = Egresso::where('curso_id', $curso->id)
                ->whereHas('user', fn($q) => $q->where('is_admin', false))
                ->where('status', 'active')
                ->count();

            $curso->empregados_count = $empregadosCount;
            $curso->taxa_empregabilidade = $curso->egressos_count > 0
                ? round(($empregadosCount / $curso->egressos_count) * 100, 2)
                : 0;
            return $curso;
        });

        // ============================================================
        // POR UNIDADE
        // ============================================================
        $porUnidade = UnidadeOrganica::with(['cursos.egressos' => function($q) {
            $q->whereHas('user', fn($u) => $u->where('is_admin', false));
        }])->get()->map(function($unidade) {
            $total = $unidade->cursos->sum(fn($c) =>
                $c->egressos->filter(fn($e) => !$e->user?->is_admin)->count()
            );
            $empregadosU = $unidade->cursos->sum(fn($c) =>
                Egresso::where('curso_id', $c->id)
                    ->whereHas('user', fn($q) => $q->where('is_admin', false))
                    ->where('status', 'active')
                    ->count()
            );

            $unidade->total_egressos = $total;
            $unidade->total_empregados = $empregadosU;
            $unidade->taxa_empregabilidade = $total > 0 ? round(($empregadosU / $total) * 100, 2) : 0;
            return $unidade;
        });

        // ============================================================
        // ÁREAS DE ATUAÇÃO
        // ============================================================
        $areasAtuacao = Egresso::whereNotNull('area_atuacao')
            ->whereHas('user', fn($q) => $q->where('is_admin', false))
            ->select('area_atuacao', DB::raw('count(*) as total'))
            ->groupBy('area_atuacao')
            ->orderBy('total', 'desc')
            ->get();

        // ============================================================
        // TOP EMPRESAS
        // ============================================================
        $topEmpresas = Empresa::withCount('oportunidades')
            ->orderByDesc('oportunidades_count')
            ->take(5)
            ->get();

        // ============================================================
        // RETURN
        // ============================================================
        return view('admin.relatorios.index', compact(
            'totalEgressos',
            'empregados',
            'taxaEmpregabilidade',
            'porCurso',
            'porUnidade',
            'areasAtuacao',
            'totalEmpresas',
            'empresasAprovadas',
            'empresasPendentes',
            'empresasReprovadas',
            'taxaAprovacaoEmpresas',
            'topEmpresas'
        ));
    }

    // ================================================================
    // HELPER — Excel
    // ================================================================
    private function excelDownload(string $filename, array $headings, iterable $rows, string $title = 'Relatório')
    {
        $rowsArray = is_array($rows) ? $rows : iterator_to_array($rows);

        return Excel::download(
            new BaseExport($headings, $rowsArray, $title),
            $filename . '_' . date('Y-m-d') . '.xlsx'
        );
    }

    // ================================================================
    // 1. EGRESSOS
    // ================================================================
    public function pdfEgressos()
    {
        $egressos = Egresso::with(['curso.unidade', 'localizacaoAtual', 'profissionalAtual'])
            ->whereHas('user', fn($q) => $q->where('is_admin', false))
            ->orderBy('nome_completo')
            ->get();

        return Pdf::loadView('admin.relatorios.pdf.egressos', compact('egressos'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_egressos_' . date('Y-m-d') . '.pdf');
    }

    public function excelEgressos()
    {
        $egressos = Egresso::with(['curso.unidade', 'localizacaoAtual', 'profissionalAtual'])
            ->whereHas('user', fn($q) => $q->where('is_admin', false))
            ->orderBy('nome_completo')
            ->get();

        $headers = ['Nº Processo', 'Nome', 'Email', 'Curso', 'Unidade', 'Ano Formatura', 'Status', 'Localização'];

        $rows = $egressos->map(fn($e) => [
            $e->numero_processo ?? '',
            $e->nome_completo ?? '',
            $e->email ?? '',
            $e->curso->nome ?? '',
            $e->curso->unidade->sigla ?? '',
            $e->ano_formatura ?? '',
            $e->status ?? '',
            $e->localizacaoAtual ? "{$e->localizacaoAtual->cidade}, {$e->localizacaoAtual->pais}" : '',
        ])->toArray();

        return $this->excelDownload('relatorio_egressos', $headers, $rows, 'Egressos');
    }

    // ================================================================
    // 2. PROFISSIONAIS
    // ================================================================
    public function pdfProfissionais()
    {
        $profissionais = Profissional::with(['egresso' => fn($q) => $q->select('id', 'nome_completo', 'numero_processo', 'foto_url')])
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->orderBy('data_inicio', 'desc')
            ->get();

        return Pdf::loadView('admin.relatorios.pdf.profissionais', compact('profissionais'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_profissionais_' . date('Y-m-d') . '.pdf');
    }

    public function excelProfissionais()
    {
        $profissionais = Profissional::with('egresso')
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->orderBy('data_inicio', 'desc')
            ->get();

        $headers = ['Egresso', 'Nº Processo', 'Cargo', 'Empregador', 'Tipo', 'Sector', 'Início', 'Atual'];

        $rows = $profissionais->map(fn($p) => [
            $p->egresso->nome_completo ?? '',
            $p->egresso->numero_processo ?? '',
            $p->cargo ?? '',
            $p->empregador ?? '',
            $p->tipo_emprego ?? '',
            $p->sector ?? '',
            $p->data_inicio ? date('d/m/Y', strtotime($p->data_inicio)) : '',
            $p->is_current ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_profissionais', $headers, $rows, 'Profissionais');
    }

    // ================================================================
    // 3. LOCALIZAÇÕES
    // ================================================================
    public function pdfLocalizacoes()
    {
        $localizacoes = Localizacao::with(['egresso.curso'])
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->get();

        $comCoordenadas = $localizacoes->filter(fn($loc) => !is_null($loc->latitude) && !is_null($loc->longitude));

        $mapaBase64 = null;
        $erroDetalhado = null;

        if ($comCoordenadas->count() > 0) {
            $pontos = []; $lats = []; $lons = [];
            foreach ($comCoordenadas as $loc) {
                $lat = (float) trim($loc->latitude);
                $lon = (float) trim($loc->longitude);
                $lats[] = $lat; $lons[] = $lon;
                $pontos[] = "{$lon},{$lat},pm2rdm";
            }
            $stringPontos = implode('~', $pontos);

            if ($comCoordenadas->count() === 1) {
                $mapUrl = "https://static-maps.yandex.ru/1.x/?l=map&lang=pt_RU&z=10&size=650,250&pt={$stringPontos}";
            } else {
                $minLat = min($lats) - 1.5; $maxLat = max($lats) + 1.5;
                $minLon = min($lons) - 2.0; $maxLon = max($lons) + 2.0;
                $bbox = "{$minLon},{$minLat}~{$maxLon},{$maxLat}";
                $mapUrl = "https://static-maps.yandex.ru/1.x/?l=map&lang=pt_RU&size=650,250&bbox={$bbox}&pt={$stringPontos}";
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $mapUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0',
                CURLOPT_TIMEOUT => 15,
            ]);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $imageData && strlen($imageData) > 500) {
                $mapaBase64 = 'data:image/png;base64,' . base64_encode($imageData);
            } else {
                $erroDetalhado = "Erro ao carregar mapa.";
            }
        }

        return Pdf::loadView('admin.relatorios.pdf.localizacoes', compact('localizacoes', 'mapaBase64', 'comCoordenadas', 'erroDetalhado'))
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->stream('relatorio_localizacoes.pdf');
    }

    public function excelLocalizacoes()
    {
        $localizacoes = Localizacao::with('egresso')
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->orderBy('pais')
            ->get();

        $headers = ['Egresso', 'Nº Processo', 'País', 'Província', 'Cidade', 'Desde', 'Atual'];

        $rows = $localizacoes->map(fn($l) => [
            $l->egresso->nome_completo ?? '',
            $l->egresso->numero_processo ?? '',
            $l->pais ?? '',
            $l->provincia ?? '',
            $l->cidade ?? '',
            $l->data_desde ? date('d/m/Y', strtotime($l->data_desde)) : '',
            $l->is_current ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_localizacoes', $headers, $rows, 'Localizações');
    }

    // ================================================================
    // 4. EMPREGABILIDADE
    // ================================================================
    public function pdfEmpregabilidade()
    {
        [$totalEgressos, $empregados, $desempregados, $taxaGeral, $porCurso] = $this->dadosEmpregabilidade();

        return Pdf::loadView('admin.relatorios.pdf.empregabilidade', compact(
            'totalEgressos', 'empregados', 'desempregados', 'taxaGeral', 'porCurso'
        ))
        ->setOption('isPhpEnabled', true)
        ->setOption('isRemoteEnabled', true)
        ->setPaper('a4', 'landscape')
        ->download('relatorio_empregabilidade_' . date('Y-m-d') . '.pdf');
    }

    public function excelEmpregabilidade()
    {
        [, , , , $porCurso] = $this->dadosEmpregabilidade();

        $headers = ['Curso', 'Total Egressos', 'Empregados', 'Taxa (%)'];

        $rows = $porCurso->map(fn($c) => [
            $c->nome,
            $c->egressos_count,
            $c->empregados_count,
            $c->taxa_empregabilidade,
        ])->toArray();

        return $this->excelDownload('relatorio_empregabilidade', $headers, $rows, 'Empregabilidade');
    }

    private function dadosEmpregabilidade(): array
    {
        $totalEgressos = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))->count();
        $empregados = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))->where('status', 'active')->count();
        $desempregados = $totalEgressos - $empregados;
        $taxaGeral = $totalEgressos > 0 ? round(($empregados / $totalEgressos) * 100, 2) : 0;

        $porCurso = Curso::withCount(['egressos' => function($q) {
            $q->whereHas('user', fn($u) => $u->where('is_admin', false));
        }])->get()->map(function($curso) {
            $empregadosCount = Egresso::where('curso_id', $curso->id)
                ->whereHas('user', fn($q) => $q->where('is_admin', false))
                ->where('status', 'active')
                ->count();
            $curso->empregados_count = $empregadosCount;
            $curso->taxa_empregabilidade = $curso->egressos_count > 0
                ? round(($empregadosCount / $curso->egressos_count) * 100, 2)
                : 0;
            return $curso;
        });

        return [$totalEgressos, $empregados, $desempregados, $taxaGeral, $porCurso];
    }

    // ================================================================
    // 5. POR CURSO
    // ================================================================
    public function pdfPorCurso($cursoId = null)
    {
        $cursos = $this->dadosPorCurso($cursoId);

        return Pdf::loadView('admin.relatorios.pdf.por_curso', compact('cursos'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_por_curso_' . date('Y-m-d') . '.pdf');
    }

    public function excelPorCurso($cursoId = null)
    {
        $cursos = $this->dadosPorCurso($cursoId);

        $headers = ['Curso', 'Unidade', 'Total', 'Empregados', 'Desempregados', 'Taxa (%)'];

        $rows = $cursos->map(fn($c) => [
            $c->nome,
            $c->unidade->sigla ?? '',
            $c->egressos_count,
            $c->empregados_count,
            $c->egressos_count - $c->empregados_count,
            $c->taxa_empregabilidade,
        ])->toArray();

        return $this->excelDownload('relatorio_por_curso', $headers, $rows, 'Por Curso');
    }

    private function dadosPorCurso($cursoId = null)
    {
        return Curso::with(['egressos' => function($q) {
                $q->whereHas('user', fn($u) => $u->where('is_admin', false))
                  ->with(['localizacaoAtual', 'profissionalAtual']);
            }, 'unidade'])
            ->withCount(['egressos' => function($q) {
                $q->whereHas('user', fn($u) => $u->where('is_admin', false));
            }])
            ->when($cursoId, fn($q) => $q->where('id', $cursoId))
            ->get()
            ->map(function($curso) {
                $empregadosCount = Egresso::where('curso_id', $curso->id)
                    ->whereHas('user', fn($q) => $q->where('is_admin', false))
                    ->where('status', 'active')
                    ->count();
                $curso->empregados_count = $empregadosCount;
                $curso->taxa_empregabilidade = $curso->egressos_count > 0
                    ? round(($empregadosCount / $curso->egressos_count) * 100, 2)
                    : 0;
                return $curso;
            });
    }

    // ================================================================
    // 6. POR UNIDADE
    // ================================================================
    public function pdfPorUnidade()
    {
        $unidades = $this->dadosPorUnidade();

        return Pdf::loadView('admin.relatorios.pdf.por_unidade', compact('unidades'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_por_unidade_' . date('Y-m-d') . '.pdf');
    }

    public function excelPorUnidade()
    {
        $unidades = $this->dadosPorUnidade();

        $headers = ['Unidade', 'Sigla', 'Total', 'Empregados', 'Taxa (%)', 'Nº Cursos'];

        $rows = $unidades->map(fn($u) => [
            $u->nome,
            $u->sigla,
            $u->total_egressos,
            $u->total_empregados,
            $u->taxa_empregabilidade,
            $u->cursos->count(),
        ])->toArray();

        return $this->excelDownload('relatorio_por_unidade', $headers, $rows, 'Por Unidade');
    }

    private function dadosPorUnidade()
    {
        return UnidadeOrganica::with(['cursos' => function($q) {
                $q->with(['egressos' => function($q2) {
                    $q2->whereHas('user', fn($u) => $u->where('is_admin', false))
                       ->with(['localizacaoAtual', 'profissionalAtual']);
                }]);
            }])
            ->get()
            ->map(function($unidade) {
                $total = $unidade->cursos->sum(fn($curso) =>
                    $curso->egressos->filter(fn($e) => !$e->user?->is_admin)->count()
                );
                $empregados = $unidade->cursos->sum(fn($curso) =>
                    $curso->egressos->filter(fn($e) => !$e->user?->is_admin && $e->status === 'active')->count()
                );
                $unidade->total_egressos = $total;
                $unidade->total_empregados = $empregados;
                $unidade->taxa_empregabilidade = $total > 0 ? round(($empregados / $total) * 100, 2) : 0;
                return $unidade;
            });
    }

    // ================================================================
    // 7. OPORTUNIDADES
    // ================================================================
    public function pdfOportunidades()
    {
        $d = $this->dadosOportunidades();

        return Pdf::loadView('admin.relatorios.pdf.oportunidades', $d)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_oportunidades_' . date('Y-m-d') . '.pdf');
    }

    public function excelOportunidades()
    {
        $d = $this->dadosOportunidades();

        $headers = ['Título', 'Tipo', 'Empresa', 'Unidade', 'Vagas', 'Status', 'Candidaturas', 'Data'];

        $rows = $d['oportunidades']->map(fn($op) => [
            $op->titulo,
            $op->tipo,
            $op->empresa ?? '',
            $op->unidade->sigla ?? '',
            $op->vagas ?? '',
            $op->is_active ? 'Ativa' : 'Expirada',
            $op->candidaturas_count,
            $op->created_at->format('d/m/Y'),
        ])->toArray();

        return $this->excelDownload('relatorio_oportunidades', $headers, $rows, 'Oportunidades');
    }

    private function dadosOportunidades(): array
    {
        $labels = ['emprego' => 'Emprego', 'estagio' => 'Estágio', 'bolsa' => 'Bolsa', 'curso' => 'Curso', 'evento' => 'Evento'];

        $porTipo = Oportunidade::select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->get()
            ->map(function($item) use ($labels) {
                $item->tipo_label = $labels[$item->tipo] ?? $item->tipo;
                return $item;
            });

        $porUnidade = UnidadeOrganica::withCount(['oportunidades' => fn($q) => $q->where('is_active', true)])
            ->get()
            ->map(function($unidade) {
                $unidade->total_oportunidades = $unidade->oportunidades_count;
                return $unidade;
            });

        return [
            'totalOportunidades'      => Oportunidade::count(),
            'oportunidadesAtivas'     => Oportunidade::where('is_active', true)->count(),
            'oportunidadesExpiradas'  => Oportunidade::where('is_active', false)->count(),
            'totalCandidaturas'       => Candidatura::count(),
            'candidaturasPendentes'   => Candidatura::where('status', 'pendente')->count(),
            'candidaturasAprovadas'   => Candidatura::where('status', 'aprovado')->count(),
            'candidaturasRejeitadas'  => Candidatura::where('status', 'rejeitado')->count(),
            'porTipo'                 => $porTipo,
            'porUnidade'              => $porUnidade,
            'topOportunidades'        => Oportunidade::withCount('candidaturas')->orderBy('candidaturas_count', 'desc')->take(5)->get(),
            'oportunidades'           => Oportunidade::with(['unidade'])->withCount('candidaturas')->orderBy('created_at', 'desc')->get(),
            'candidaturasRecentes'    => Candidatura::with(['oportunidade', 'egresso'])->orderBy('created_at', 'desc')->take(20)->get(),
        ];
    }

    // ================================================================
    // 8. COMPLETO
    // ================================================================
    public function pdfCompleto()
    {
        $data = $this->dadosCompleto();

        return Pdf::loadView('admin.relatorios.pdf.completo', $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_completo_' . date('Y-m-d') . '.pdf');
    }
public function excelCompleto()
{
    $dados = $this->dadosCompleto();

    return Excel::download(
        new \App\Exports\CompletoExport($dados),
        'relatorio_completo_' . date('Y-m-d') . '.xlsx'
    );
}

    private function dadosCompleto(): array
    {
        $hoje = now();
        $ha30Dias = $hoje->copy()->subDays(30);

        $totalEgressos = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))->count();
        $empregados = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))
            ->where('status', 'active')->count();
        $taxaEmpregabilidade = $totalEgressos > 0 ? round(($empregados / $totalEgressos) * 100, 2) : 0;

        $porUnidade = $this->dadosPorUnidade();

        $porStatus = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $porTipoEmprego = Profissional::select('tipo_emprego', DB::raw('count(*) as total'))
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->groupBy('tipo_emprego')
            ->get();

        $porPais = Localizacao::select('pais', DB::raw('count(distinct egresso_id) as total'))
            ->whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->groupBy('pais')
            ->orderBy('total', 'desc')
            ->get();

        $totalPaises = $porPais->count();

        $porCurso = $this->dadosPorCurso();

        $totalOportunidades = Oportunidade::count();
        $oportunidadesAtivas = Oportunidade::where('is_active', true)->count();
        $oportunidadesExpiradas = $totalOportunidades - $oportunidadesAtivas;

        $totalCandidaturas = Candidatura::count();
        $candidaturasPendentes = Candidatura::where('status', 'pendente')->count();
        $candidaturasAprovadas = Candidatura::where('status', 'aprovado')->count();
        $candidaturasRejeitadas = Candidatura::where('status', 'rejeitado')->count();
        $candidaturasRecentes = Candidatura::where('created_at', '>=', $ha30Dias)->count();

        $topOportunidades = Oportunidade::withCount('candidaturas')
            ->orderBy('candidaturas_count', 'desc')
            ->take(10)
            ->get();

        $totalEventos = Evento::count();
        $eventosAtivos = Evento::where('is_active', true)->count();
        $eventosRecentes = Evento::where('created_at', '>=', $ha30Dias)->count();

        $totalInscricoes = InscricaoEvento::count();
        $inscricoesPresentes = InscricaoEvento::where('presente', true)->count();
        $inscricoesRecentes = InscricaoEvento::where('created_at', '>=', $ha30Dias)->count();

        $totalConexoes = Conexao::count();
        $conexoesAceites = Conexao::where('status', 'aceito')->count();
        $conexoesRecentes = Conexao::where('created_at', '>=', $ha30Dias)->count();

        $totalMensagens = Mensagem::count();
        $mensagensLidas = Mensagem::where('lida', true)->count();
        $mensagensRecentes = Mensagem::where('created_at', '>=', $ha30Dias)->count();

        $totalChamadas = Notificacao::whereIn('tipo', ['chamada_voz', 'chamada_video'])->count();
        $chamadasVideo = Notificacao::where('tipo', 'chamada_video')->count();
        $chamadasRecentes = Notificacao::whereIn('tipo', ['chamada_voz', 'chamada_video'])
            ->where('created_at', '>=', $ha30Dias)->count();

        $totalAudios = Mensagem::where('tipo', 'audio')->count();
        $audiosLidos = Mensagem::where('tipo', 'audio')->where('lida', true)->count();
        $audiosRecentes = Mensagem::where('tipo', 'audio')->where('created_at', '>=', $ha30Dias)->count();

        $totalNotificacoes = Notificacao::count();
        $notificacoesLidas = Notificacao::where('lida', true)->count();
        $notificacoesRecentes = Notificacao::where('created_at', '>=', $ha30Dias)->count();

        $totalFeedbacks = Feedback::count();
        $feedbacksAprovados = Feedback::where('status', 'aprovado')->count();
        $feedbacksRecentes = Feedback::where('created_at', '>=', $ha30Dias)->count();

        $totalServicos = Servico::count();
        $servicosAtendidos = Servico::where('status', 'atendido')->count();
        $servicosRecentes = Servico::where('created_at', '>=', $ha30Dias)->count();

        $totalProfissionais = Profissional::whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->where('is_current', true)
            ->count();

        $egressosRecentes = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))
            ->where('created_at', '>=', $ha30Dias)
            ->count();

        $egressos = Egresso::with(['curso.unidade', 'localizacaoAtual', 'profissionalAtual'])
            ->whereHas('user', fn($q) => $q->where('is_admin', false))
            ->orderBy('nome_completo')
            ->take(50)
            ->get();

        $mapaBase64 = $this->gerarMapaBase64();

        return compact(
            'totalEgressos', 'empregados', 'taxaEmpregabilidade', 'totalPaises',
            'porUnidade', 'porStatus', 'porTipoEmprego', 'porPais', 'porCurso',
            'totalOportunidades', 'oportunidadesAtivas', 'oportunidadesExpiradas',
            'totalCandidaturas', 'candidaturasPendentes', 'candidaturasAprovadas', 'candidaturasRejeitadas',
            'candidaturasRecentes', 'topOportunidades',
            'totalEventos', 'eventosAtivos', 'eventosRecentes',
            'totalInscricoes', 'inscricoesPresentes', 'inscricoesRecentes',
            'totalConexoes', 'conexoesAceites', 'conexoesRecentes',
            'totalMensagens', 'mensagensLidas', 'mensagensRecentes',
            'totalChamadas', 'chamadasVideo', 'chamadasRecentes',
            'totalAudios', 'audiosLidos', 'audiosRecentes',
            'totalNotificacoes', 'notificacoesLidas', 'notificacoesRecentes',
            'totalFeedbacks', 'feedbacksAprovados', 'feedbacksRecentes',
            'totalServicos', 'servicosAtendidos', 'servicosRecentes',
            'totalProfissionais', 'egressosRecentes', 'egressos', 'mapaBase64'
        );
    }

    private function gerarMapaBase64(): ?string
    {
        $localizacoes = Localizacao::whereHas('egresso.user', fn($q) => $q->where('is_admin', false))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($localizacoes->count() === 0) return null;

        $pontos = []; $lats = []; $lons = [];
        foreach ($localizacoes as $loc) {
            $lat = (float) trim($loc->latitude);
            $lon = (float) trim($loc->longitude);
            $lats[] = $lat; $lons[] = $lon;
            $pontos[] = "{$lon},{$lat},pm2rdm";
        }
        $stringPontos = implode('~', $pontos);

        if ($localizacoes->count() === 1) {
            $mapUrl = "https://static-maps.yandex.ru/1.x/?l=map&lang=pt_RU&z=10&size=650,250&pt={$stringPontos}";
        } else {
            $minLat = min($lats) - 1.5; $maxLat = max($lats) + 1.5;
            $minLon = min($lons) - 2.0; $maxLon = max($lons) + 2.0;
            $bbox = "{$minLon},{$minLat}~{$maxLon},{$maxLat}";
            $mapUrl = "https://static-maps.yandex.ru/1.x/?l=map&lang=pt_RU&size=650,250&bbox={$bbox}&pt={$stringPontos}";
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $mapUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
            CURLOPT_TIMEOUT => 15,
        ]);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $imageData && strlen($imageData) > 500) {
            return 'data:image/png;base64,' . base64_encode($imageData);
        }

        return null;
    }

    // ================================================================
    // 9. CONEXÕES
    // ================================================================
    public function pdfConexoes()
    {
        $conexoes = Conexao::with(['solicitante', 'destinatario'])->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.conexoes', compact('conexoes'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_conexoes_' . date('Y-m-d') . '.pdf');
    }

    public function excelConexoes()
    {
        $conexoes = Conexao::with(['solicitante', 'destinatario'])->orderBy('created_at', 'desc')->get();

        $headers = ['Solicitante', 'Destinatário', 'Status', 'Data', 'Observação'];

        $rows = $conexoes->map(fn($c) => [
            $c->solicitante->nome_completo ?? '',
            $c->destinatario->nome_completo ?? '',
            ucfirst($c->status ?? ''),
            $c->created_at->format('d/m/Y H:i'),
            $c->observacao ?? '',
        ])->toArray();

        return $this->excelDownload('relatorio_conexoes', $headers, $rows, 'Conexões');
    }

    // ================================================================
    // 10. MENSAGENS
    // ================================================================
    public function pdfMensagens()
    {
        $mensagens = Mensagem::with(['remetente', 'destinatario'])->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.mensagens', compact('mensagens'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_mensagens_' . date('Y-m-d') . '.pdf');
    }

    public function excelMensagens()
    {
        $mensagens = Mensagem::with(['remetente', 'destinatario'])->orderBy('created_at', 'desc')->get();

        $headers = ['Remetente', 'Destinatário', 'Mensagem', 'Data', 'Lida', 'Anexo'];

        $rows = $mensagens->map(fn($m) => [
            $m->remetente->nome_completo ?? '',
            $m->destinatario->nome_completo ?? '',
            Str::limit($m->mensagem ?? '', 100),
            $m->created_at->format('d/m/Y H:i'),
            $m->lida ? 'Sim' : 'Não',
            $m->ficheiro ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_mensagens', $headers, $rows, 'Mensagens');
    }

    // ================================================================
    // 11. CHAMADAS
    // ================================================================
    public function pdfChamadas()
    {
        $chamadas = collect();
        try {
            if (class_exists('\App\Models\Chamada')) {
                $model = new \App\Models\Chamada();
                $query = \App\Models\Chamada::query();
                if (method_exists($model, 'chamador'))    $query->with('chamador');
                if (method_exists($model, 'destinatario')) $query->with('destinatario');
                $chamadas = $query->orderBy('created_at', 'desc')->get();
            } else {
                $chamadas = Notificacao::whereIn('tipo', ['chamada_voz', 'chamada_video'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(fn($n) => (object) [
                        'id' => $n->id,
                        'chamador' => (object) ['nome_completo' => 'Egresso'],
                        'destinatario' => (object) ['nome_completo' => 'Egresso'],
                        'tipo' => str_contains($n->tipo, 'video') ? 'video' : 'voz',
                        'duracao_segundos' => 0,
                        'estado' => 'terminada',
                        'created_at' => $n->created_at,
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Erro ao carregar chamadas: ' . $e->getMessage());
        }

        return Pdf::loadView('admin.relatorios.pdf.chamadas', compact('chamadas'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_chamadas_' . date('Y-m-d') . '.pdf');
    }

    public function excelChamadas()
    {
        $chamadas = collect();
        try {
            if (class_exists('\App\Models\Chamada')) {
                $model = new \App\Models\Chamada();
                $query = \App\Models\Chamada::query();
                if (method_exists($model, 'chamador'))    $query->with('chamador');
                if (method_exists($model, 'destinatario')) $query->with('destinatario');
                $chamadas = $query->orderBy('created_at', 'desc')->get();
            }
        } catch (\Throwable $e) {
            Log::warning('Erro ao carregar chamadas para Excel: ' . $e->getMessage());
        }

        $headers = ['Chamador', 'Destinatário', 'Tipo', 'Duração (s)', 'Estado', 'Data'];

        $rows = $chamadas->map(fn($c) => [
            $c->chamador->nome_completo    ?? '',
            $c->destinatario->nome_completo ?? '',
            $c->tipo ?? 'voz',
            $c->duracao_segundos ?? 0,
            $c->estado ?? 'terminada',
            $c->created_at ? $c->created_at->format('d/m/Y H:i') : '',
        ])->toArray();

        return $this->excelDownload('relatorio_chamadas', $headers, $rows, 'Chamadas');
    }

    // ================================================================
    // 12. ÁUDIOS
    // ================================================================
    public function pdfAudios()
    {
        $audios = collect();
        try {
            $query = Mensagem::query();
            if (Schema::hasColumn('mensagens', 'audio_path')) $query->whereNotNull('audio_path');
            if (Schema::hasColumn('mensagens', 'tipo')) $query->orWhere('tipo', 'audio');
            $audios = $query->with(['remetente', 'destinatario'])->orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {
            Log::warning('Erro ao carregar áudios: ' . $e->getMessage());
        }

        return Pdf::loadView('admin.relatorios.pdf.audios', compact('audios'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_audios_' . date('Y-m-d') . '.pdf');
    }

    public function excelAudios()
    {
        $audios = collect();
        try {
            $query = Mensagem::query();
            if (Schema::hasColumn('mensagens', 'audio_path')) $query->whereNotNull('audio_path');
            if (Schema::hasColumn('mensagens', 'tipo')) $query->orWhere('tipo', 'audio');
            $audios = $query->with(['remetente', 'destinatario'])->orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {
            Log::warning('Erro ao carregar áudios para Excel: ' . $e->getMessage());
        }

        $headers = ['Remetente', 'Destinatário', 'Duração (s)', 'Data', 'Ouvido'];

        $rows = $audios->map(fn($a) => [
            $a->remetente->nome_completo ?? '',
            $a->destinatario->nome_completo ?? '',
            $a->duracao_segundos ?? 0,
            $a->created_at ? $a->created_at->format('d/m/Y H:i') : '',
            $a->lida ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_audios', $headers, $rows, 'Áudios');
    }

    // ================================================================
    // 13. NOTIFICAÇÕES
    // ================================================================
    public function pdfNotificacoes()
    {
        $notificacoes = Notificacao::with('egresso')->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.notificacoes', compact('notificacoes'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_notificacoes_' . date('Y-m-d') . '.pdf');
    }

    public function excelNotificacoes()
    {
        $notificacoes = Notificacao::with('egresso')->orderBy('created_at', 'desc')->get();

        $headers = ['Egresso', 'Tipo', 'Título', 'Mensagem', 'Data', 'Lida'];

        $rows = $notificacoes->map(fn($n) => [
            $n->egresso->nome_completo ?? 'Admin',
            $n->tipo ?? '',
            $n->titulo ?? '',
            Str::limit($n->mensagem ?? '', 100),
            $n->created_at->format('d/m/Y H:i'),
            $n->lida ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_notificacoes', $headers, $rows, 'Notificações');
    }

    // ================================================================
    // 14. CANDIDATURAS
    // ================================================================
    public function pdfCandidaturas()
    {
        $candidaturas = Candidatura::with(['egresso', 'oportunidade'])->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.candidaturas', compact('candidaturas'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_candidaturas_' . date('Y-m-d') . '.pdf');
    }

    public function excelCandidaturas()
    {
        $candidaturas = Candidatura::with(['egresso', 'oportunidade'])->orderBy('created_at', 'desc')->get();

        $headers = ['Candidato', 'Oportunidade', 'Empresa', 'Status', 'Data', 'Avaliado em'];

        $rows = $candidaturas->map(fn($c) => [
            $c->egresso->nome_completo ?? '',
            $c->oportunidade->titulo ?? '',
            $c->oportunidade->empresa ?? '',
            $c->status ?? '',
            $c->created_at ? $c->created_at->format('d/m/Y') : '',
            ($c->avaliado_em ?? null) ? $c->avaliado_em->format('d/m/Y') : '',
        ])->toArray();

        return $this->excelDownload('relatorio_candidaturas', $headers, $rows, 'Candidaturas');
    }

    // ================================================================
    // 15. EVENTOS
    // ================================================================
    public function pdfEventos()
    {
        $eventos = Evento::withCount('inscricoes')->orderBy('data_inicio', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.eventos', compact('eventos'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_eventos_' . date('Y-m-d') . '.pdf');
    }

    public function excelEventos()
    {
        $eventos = Evento::withCount('inscricoes')->orderBy('data_inicio', 'desc')->get();

        $headers = ['Título', 'Tipo', 'Categoria', 'Data Início', 'Local', 'Vagas', 'Inscritos', 'Ativo'];

        $rows = $eventos->map(fn($e) => [
            $e->titulo ?? '',
            $e->tipo ?? '',
            $e->categoria ?? '',
            $e->data_inicio ? $e->data_inicio->format('d/m/Y H:i') : '',
            $e->local ?? '',
            $e->max_participantes ?? '∞',
            $e->inscricoes_count ?? 0,
            $e->is_active ? 'Sim' : 'Não',
        ])->toArray();

        return $this->excelDownload('relatorio_eventos', $headers, $rows, 'Eventos');
    }

    // ================================================================
    // 16. INSCRIÇÕES
    // ================================================================
    public function pdfInscricoes()
    {
        $inscricoes = InscricaoEvento::with(['egresso', 'evento'])->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.inscricoes', compact('inscricoes'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_inscricoes_' . date('Y-m-d') . '.pdf');
    }

    public function excelInscricoes()
    {
        $inscricoes = InscricaoEvento::with(['egresso', 'evento'])->orderBy('created_at', 'desc')->get();

        $headers = ['Egresso', 'Evento', 'Comprovativo', 'Inscrição', 'Presença'];

        $rows = $inscricoes->map(fn($i) => [
            $i->egresso->nome_completo ?? '',
            $i->evento->titulo ?? '',
            $i->codigo_comprovativo ?? '',
            $i->created_at->format('d/m/Y'),
            $i->presente ? 'Presente' : 'Não Registado',
        ])->toArray();

        return $this->excelDownload('relatorio_inscricoes', $headers, $rows, 'Inscrições');
    }

    // ================================================================
    // 17. FEEDBACKS
    // ================================================================
    public function pdfFeedbacks()
    {
        $feedbacks = Feedback::with(['egresso', 'curso'])->orderBy('created_at', 'desc')->get();

        return Pdf::loadView('admin.relatorios.pdf.feedbacks', compact('feedbacks'))
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_feedbacks_' . date('Y-m-d') . '.pdf');
    }

    public function excelFeedbacks()
    {
        $feedbacks = Feedback::with(['egresso', 'curso'])->orderBy('created_at', 'desc')->get();

        $headers = ['Egresso', 'Título', 'Nota', 'Status', 'Curso', 'Data'];

        $rows = $feedbacks->map(fn($f) => [
            $f->egresso->nome_completo ?? '',
            $f->titulo ?? '',
            $f->nota ?? '',
            ucfirst($f->status ?? ''),
            $f->curso->nome ?? '',
            $f->created_at->format('d/m/Y'),
        ])->toArray();

        return $this->excelDownload('relatorio_feedbacks', $headers, $rows, 'Feedbacks');
    }

    // ================================================================
    // 18. SERVIÇOS
    // ================================================================
    public function pdfServicos()
    {
        $dados = $this->dadosServicos();

        return Pdf::loadView('admin.relatorios.pdf.servicos_solicitados', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_servicos_' . date('Y-m-d') . '.pdf');
    }

    public function excelServicos()
    {
        $servicos = Servico::with('egresso')->orderByDesc('created_at')->get();

        $headers = ['Egresso', 'Nº Processo', 'Serviço', 'Descrição', 'Status', 'Data Pedido', 'Atendido em', 'Resposta Admin'];

        $rows = $servicos->map(fn($s) => [
            $s->egresso->nome_completo ?? '',
            $s->egresso->numero_processo ?? '',
            $s->servico ?? '',
            Str::limit($s->descricao ?? '', 120),
            ucfirst($s->status ?? ''),
            $s->created_at  ? $s->created_at->format('d/m/Y H:i')  : '',
            $s->atendido_em ? $s->atendido_em->format('d/m/Y H:i') : '',
            Str::limit($s->resposta_admin ?? '', 120),
        ])->toArray();

        return $this->excelDownload('relatorio_servicos', $headers, $rows, 'Serviços');
    }

    private function dadosServicos(): array
    {
        $servicos = Servico::with('egresso:id,nome_completo,numero_processo,foto_url')
            ->orderByDesc('created_at')
            ->get();

        $totalServicos = $servicos->count();
        $pendentes     = $servicos->where('status', 'pendente')->count();
        $andamento     = $servicos->where('status', 'andamento')->count();
        $atendidos     = $servicos->where('status', 'atendido')->count();
        $cancelados    = $servicos->where('status', 'cancelado')->count();

        $taxaAtendimento = $totalServicos > 0 ? round(($atendidos / $totalServicos) * 100, 1) : 0;

        $tempos = $servicos->whereNotNull('atendido_em')->map(fn($s) =>
            $s->created_at->diffInHours($s->atendido_em)
        );
        $tempoMedio = $tempos->count() > 0 ? round($tempos->avg(), 1) : 0;

        $topServicos = $servicos->groupBy('servico')
            ->map(fn($g) => $g->count())
            ->sortDesc()
            ->take(5);

        return compact(
            'servicos', 'totalServicos', 'pendentes', 'andamento',
            'atendidos', 'cancelados', 'taxaAtendimento', 'tempoMedio', 'topServicos'
        );
    }

    // ================================================================
    // 19. ATIVIDADE GERAL
    // ================================================================
    public function pdfAtividade()
    {
        $dados = $this->dadosAtividade();

        return Pdf::loadView('admin.relatorios.pdf.atividade', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_atividade_' . date('Y-m-d') . '.pdf');
    }

    public function excelAtividade()
    {
        $d = $this->dadosAtividade();

        $headers = ['Módulo', 'Total', 'Últimos 30 dias'];

        $rows = [
            ['Egressos',     $d['totalEgressos'],     $d['egressosRecentes']],
            ['Conexões',     $d['totalConexoes'],     $d['conexoesRecentes']],
            ['Mensagens',    $d['totalMensagens'],    $d['mensagensRecentes']],
            ['Chamadas',     $d['totalChamadas'],     $d['chamadasRecentes']],
            ['Áudios',       $d['totalAudios'],       $d['audiosRecentes']],
            ['Notificações', $d['totalNotificacoes'], $d['notificacoesRecentes']],
            ['Candidaturas', $d['totalCandidaturas'], $d['candidaturasRecentes']],
            ['Eventos',      $d['totalEventos'],      $d['eventosRecentes']],
            ['Inscrições',   $d['totalInscricoes'],   $d['inscricoesRecentes']],
            ['Feedbacks',    $d['totalFeedbacks'],    $d['feedbacksRecentes']],
            ['Serviços',     $d['totalServicos'],     $d['servicosRecentes']],
        ];

        return $this->excelDownload('relatorio_atividade', $headers, $rows, 'Atividade');
    }

    private function dadosAtividade(): array
    {
        $ha30Dias = now()->subDays(30);

        return [
            'totalEgressos'        => Egresso::count(),
            'egressosRecentes'     => Egresso::where('created_at', '>=', $ha30Dias)->count(),
            'totalConexoes'        => Conexao::where('status', 'aceito')->count(),
            'conexoesRecentes'     => Conexao::where('status', 'aceito')->where('created_at', '>=', $ha30Dias)->count(),
            'totalMensagens'       => Mensagem::count(),
            'mensagensRecentes'    => Mensagem::where('created_at', '>=', $ha30Dias)->count(),
            'totalChamadas'        => Notificacao::whereIn('tipo', ['chamada_voz', 'chamada_video'])->count(),
            'chamadasRecentes'     => Notificacao::whereIn('tipo', ['chamada_voz', 'chamada_video'])->where('created_at', '>=', $ha30Dias)->count(),
            'totalAudios'          => Mensagem::where('tipo', 'audio')->count(),
            'audiosRecentes'       => Mensagem::where('tipo', 'audio')->where('created_at', '>=', $ha30Dias)->count(),
            'totalNotificacoes'    => Notificacao::count(),
            'notificacoesRecentes' => Notificacao::where('created_at', '>=', $ha30Dias)->count(),
            'totalCandidaturas'    => Candidatura::count(),
            'candidaturasRecentes' => Candidatura::where('created_at', '>=', $ha30Dias)->count(),
            'totalEventos'         => Evento::count(),
            'eventosRecentes'      => Evento::where('created_at', '>=', $ha30Dias)->count(),
            'totalInscricoes'      => InscricaoEvento::count(),
            'inscricoesRecentes'   => InscricaoEvento::where('created_at', '>=', $ha30Dias)->count(),
            'totalFeedbacks'       => Feedback::count(),
            'feedbacksRecentes'    => Feedback::where('created_at', '>=', $ha30Dias)->count(),
            'totalServicos'        => Servico::count(),
            'servicosRecentes'     => Servico::where('created_at', '>=', $ha30Dias)->count(),
        ];
    }

    // ================================================================
    // 20. ENGAJAMENTO
    // ================================================================
    public function pdfEngajamento()
    {
        $dados = $this->dadosEngajamento();

        return Pdf::loadView('admin.relatorios.pdf.engajamento', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_engajamento_' . date('Y-m-d') . '.pdf');
    }

    public function excelEngajamento()
    {
        $d = $this->dadosEngajamento();

        $headers = ['Unidade', 'Egressos', 'Ativos', 'Conexões', 'Mensagens', 'Taxa (%)'];

        $rows = collect($d['porUnidade'])->map(fn($u) => [
            $u->nome,
            $u->total_egressos ?? 0,
            $u->total_ativos ?? 0,
            $u->total_conexoes ?? 0,
            $u->total_mensagens ?? 0,
            $u->taxa_engajamento ?? 0,
        ])->toArray();

        return $this->excelDownload('relatorio_engajamento', $headers, $rows, 'Engajamento');
    }

    private function dadosEngajamento(): array
    {
        $totalEgressos = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))->count();
        $ativos = Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))->where('status', 'active')->count();
        $inativos = $totalEgressos - $ativos;
        $taxa = $totalEgressos > 0 ? round(($ativos / $totalEgressos) * 100) : 0;

        $porUnidade = UnidadeOrganica::with(['cursos.egressos'])->get()->map(function($unidade) {
            $total = $unidade->cursos->sum(fn($c) => $c->egressos->count());
            $unidade->total_egressos   = $total;
            $unidade->total_ativos     = $unidade->cursos->sum(fn($c) => $c->egressos->where('status', 'active')->count());
            $unidade->total_conexoes   = 0;
            $unidade->total_mensagens  = 0;
            $unidade->taxa_engajamento = $total > 0 ? round(($unidade->total_ativos / $total) * 100) : 0;
            return $unidade;
        });

        return [
            'totalEgressos'    => $totalEgressos,
            'ativos'           => $ativos,
            'inativos'         => $inativos,
            'taxaEngajamento'  => $taxa,
            'porUnidade'       => $porUnidade,
            'topEgressos'      => Egresso::whereHas('user', fn($q) => $q->where('is_admin', false))
                ->with('curso')
                ->take(20)
                ->get()
                ->map(function($e) {
                    $e->total_conexoes  = 0;
                    $e->total_mensagens = 0;
                    $e->total_chamadas  = 0;
                    $e->total_eventos   = 0;
                    $e->score           = 0;
                    return $e;
                }),
        ];
    }

    // ================================================================
    // 21. PESQUISAS
    // ================================================================
    public function pdfPesquisas()
    {
        $dados = $this->dadosPesquisas();

        return Pdf::loadView('admin.relatorios.pdf.pesquisas', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_pesquisas_' . date('Y-m-d') . '.pdf');
    }

    public function excelPesquisas()
    {
        $pesquisas = Pesquisa::withCount(['perguntas', 'respostas'])
            ->orderByDesc('created_at')
            ->get();

        $headers = ['Título', 'Descrição', 'Nº Perguntas', 'Nº Respostas', 'Participantes', 'Início', 'Fim', 'Ativa'];

        $rows = $pesquisas->map(function ($p) {
            $participantes = PesquisaResposta::where('pesquisa_id', $p->id)
                ->distinct('egresso_id')->count('egresso_id');

            return [
                $p->titulo ?? '',
                Str::limit($p->descricao ?? '', 100),
                $p->perguntas_count ?? 0,
                $p->respostas_count ?? 0,
                $participantes,
                $p->data_inicio ? $p->data_inicio->format('d/m/Y') : '',
                $p->data_fim    ? $p->data_fim->format('d/m/Y')    : '',
                $p->ativa ? 'Sim' : 'Não',
            ];
        })->toArray();

        return $this->excelDownload('relatorio_pesquisas', $headers, $rows, 'Pesquisas');
    }

    private function dadosPesquisas(): array
    {
        $pesquisas = Pesquisa::withCount(['perguntas', 'respostas'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                $p->total_participantes = PesquisaResposta::where('pesquisa_id', $p->id)
                    ->distinct('egresso_id')->count('egresso_id');
                return $p;
            });

        $totalPesquisas     = $pesquisas->count();
        $pesquisasAtivas    = $pesquisas->where('ativa', true)->count();
        $totalRespostas     = $pesquisas->sum('respostas_count');
        $totalParticipantes = PesquisaResposta::distinct('egresso_id')->count('egresso_id');

        $perguntaTop = PesquisaPergunta::withCount('respostas')
            ->orderByDesc('respostas_count')
            ->first();

        return compact(
            'pesquisas', 'totalPesquisas', 'pesquisasAtivas',
            'totalRespostas', 'totalParticipantes', 'perguntaTop'
        );
    }

    // ================================================================
    // 22. MURAL DE NOTÍCIAS
    // ================================================================
    public function pdfMural()
    {
        $dados = $this->dadosMural();

        return Pdf::loadView('admin.relatorios.pdf.mural', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_mural_' . date('Y-m-d') . '.pdf');
    }

    public function excelMural()
    {
        $publicacoes = MuralNoticia::with(['egresso:id,nome_completo', 'admin:id,name'])
            ->withCount(['curtidas', 'comentarios'])
            ->orderByDesc('created_at')
            ->get();

        $headers = ['Título', 'Tipo', 'Autor', 'Publicado', 'Destaque', 'Curtidas', 'Comentários', 'Data'];

        $rows = $publicacoes->map(fn($p) => [
            $p->titulo ?? '',
            ucfirst($p->tipo ?? ''),
            $p->egresso->nome_completo ?? ($p->admin->name ?? 'Admin'),
            $p->publicado ? 'Sim' : 'Não',
            $p->destaque ? 'Sim' : 'Não',
            $p->curtidas_count ?? 0,
            $p->comentarios_count ?? 0,
            $p->created_at ? $p->created_at->format('d/m/Y H:i') : '',
        ])->toArray();

        return $this->excelDownload('relatorio_mural', $headers, $rows, 'Mural');
    }

    private function dadosMural(): array
    {
        $publicacoes = MuralNoticia::with(['egresso:id,nome_completo,foto_url', 'admin:id,name'])
            ->withCount(['curtidas', 'comentarios'])
            ->orderByDesc('created_at')
            ->get();

        $totalPublicacoes = $publicacoes->count();
        $publicadas       = $publicacoes->where('publicado', true)->count();
        $emDestaque       = $publicacoes->where('destaque', true)->count();
        $totalCurtidas    = $publicacoes->sum('curtidas_count');
        $totalComentarios = $publicacoes->sum('comentarios_count');

        $porTipo = $publicacoes->groupBy('tipo')->map(fn($g) => $g->count());
        $porEgressos = $publicacoes->whereNotNull('egresso_id')->count();
        $porAdmin    = $publicacoes->whereNull('egresso_id')->count();

        $topPublicacoes = $publicacoes
            ->sortByDesc(fn($p) => $p->curtidas_count + $p->comentarios_count)
            ->take(5);

        return compact(
            'publicacoes', 'totalPublicacoes', 'publicadas', 'emDestaque',
            'totalCurtidas', 'totalComentarios', 'porTipo',
            'porEgressos', 'porAdmin', 'topPublicacoes'
        );
    }

    // ================================================================
    // 23. MAPA DE LOCALIZAÇÕES
    // ================================================================
    public function pdfMapa()
    {
        $dados = $this->dadosMapa();

        return Pdf::loadView('admin.relatorios.pdf.mapa', $dados)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('relatorio_mapa_' . date('Y-m-d') . '.pdf');
    }

    public function excelMapa()
    {
        $localizacoes = Localizacao::with('egresso')
            ->where('is_current', true)
            ->orderBy('pais')
            ->orderBy('cidade')
            ->get();

        $headers = ['Egresso', 'Nº Processo', 'País', 'Província', 'Cidade', 'Endereço', 'Latitude', 'Longitude', 'Desde'];

        $rows = $localizacoes->map(fn($l) => [
            $l->egresso->nome_completo ?? '',
            $l->egresso->numero_processo ?? '',
            $l->pais ?? '',
            $l->provincia ?? '',
            $l->cidade ?? '',
            $l->endereco ?? '',
            $l->latitude ?? '',
            $l->longitude ?? '',
            $l->data_desde ? date('d/m/Y', strtotime($l->data_desde)) : '',
        ])->toArray();

        return $this->excelDownload('relatorio_mapa', $headers, $rows, 'Mapa');
    }

    private function dadosMapa(): array
    {
        $localizacoes = Localizacao::with('egresso:id,nome_completo,numero_processo,foto_url')
            ->where('is_current', true)
            ->get();

        $comCoordenadas = $localizacoes->filter(fn($l) => $l->latitude && $l->longitude);
        $semCoordenadas = $localizacoes->filter(fn($l) => !$l->latitude || !$l->longitude);

        $totalLocalizados    = $localizacoes->count();
        $totalComCoordenadas = $comCoordenadas->count();

        $porPais = $localizacoes->groupBy('pais')->map(fn($g) => $g->count())->sortDesc();
        $porProvincia = $localizacoes->whereNotNull('provincia')->groupBy('provincia')->map(fn($g) => $g->count())->sortDesc();

        $totalPaises     = $porPais->count();
        $totalProvincias = $porProvincia->count();

        $topCidades = $localizacoes->whereNotNull('cidade')->groupBy('cidade')->map(fn($g) => $g->count())->sortDesc()->take(10);

        $mapaBase64 = $this->gerarMapaBase64();

        return compact(
            'localizacoes', 'comCoordenadas', 'semCoordenadas',
            'totalLocalizados', 'totalComCoordenadas',
            'porPais', 'porProvincia', 'totalPaises', 'totalProvincias',
            'topCidades', 'mapaBase64'
        );
    }

    // ================================================================
// 24. EMPRESAS
// ================================================================
public function pdfEmpresas()
{
    $dados = $this->dadosEmpresas();

    return Pdf::loadView('admin.relatorios.pdf.empresas', $dados)
        ->setOption('isPhpEnabled', true)
        ->setOption('isRemoteEnabled', true)
        ->setPaper('a4', 'landscape')
        ->download('relatorio_empresas_' . date('Y-m-d') . '.pdf');
}

public function excelEmpresas()
{
    $empresas = \App\Models\Empresa::with('user')
        ->orderByDesc('created_at')
        ->get();

    $headers = [
        'Nome', 'NIF', 'Email', 'Telefone', 'Sector', 'Localização',
        'Província', 'Tipo', 'Status Validação', 'Data Validação', 'Registada em',
    ];

    $rows = $empresas->map(fn($e) => [
        $e->nome ?? '',
        $e->nif ?? '',
        $e->email ?? '',
        $e->telefone ?? '',
        $e->sector ?? '',
        $e->localizacao ?? '',
        $e->provincia ?? '',
        $e->tipo ?? '',
        $e->status_validacao ?? '',
        $e->data_validacao ? $e->data_validacao->format('d/m/Y H:i') : '',
        $e->created_at ? $e->created_at->format('d/m/Y H:i') : '',
    ])->toArray();

    return $this->excelDownload('relatorio_empresas', $headers, $rows, 'Empresas');
}

private function dadosEmpresas(): array
{
    $empresas = \App\Models\Empresa::with('user')
        ->withCount(['oportunidades'])
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($empresa) {
            // Total de candidaturas desta empresa
            $empresa->total_candidaturas = \App\Models\Candidatura::whereIn(
                'oportunidade_id',
                \App\Models\Oportunidade::where('empresa_id', $empresa->id)->pluck('id')
            )->count();

            return $empresa;
        });

    $totalEmpresas    = $empresas->count();
    $aprovadas        = $empresas->where('status_validacao', 'aprovado')->count();
    $pendentes        = $empresas->where('status_validacao', 'pendente')->count();
    $reprovadas       = $empresas->where('status_validacao', 'reprovado')->count();

    $taxaAprovacao = $totalEmpresas > 0
        ? round(($aprovadas / $totalEmpresas) * 100, 1)
        : 0;

    // Distribuição por sector
    $porSector = $empresas->groupBy('sector')
        ->map(fn($g) => $g->count())
        ->sortDesc();

    // Distribuição por província
    $porProvincia = $empresas->whereNotNull('provincia')
        ->groupBy('provincia')
        ->map(fn($g) => $g->count())
        ->sortDesc();

    // Top 10 empresas com mais oportunidades
    $topEmpresas = $empresas->sortByDesc('oportunidades_count')->take(10);

    // Empresas recentes (últimos 30 dias)
    $empresasRecentes = \App\Models\Empresa::where('created_at', '>=', now()->subDays(30))->count();

    return compact(
        'empresas', 'totalEmpresas', 'aprovadas', 'pendentes', 'reprovadas',
        'taxaAprovacao', 'porSector', 'porProvincia',
        'topEmpresas', 'empresasRecentes'
    );
}
}