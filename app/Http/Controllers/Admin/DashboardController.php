<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Oportunidade;
use App\Models\Evento;
use App\Models\UnidadeOrganica;
use App\Models\Feedback;
use App\Models\Servico;
use App\Models\Candidatura;
use App\Models\InscricaoEvento;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * ============================================================
     * SCOPE: Egressos REAIS
     * ============================================================
     * Aplica o mesmo filtro usado em /admin/egressos:
     *   - Exclui admins (tipo/role/is_admin)
     *   - Exclui número de processo ADMIN_*
     *   - Só conta aprovados OU pendentes criados pelo admin
     *     (não PENDENTE_*)
     */
    private function scopeEgressosReais()
    {
        return Egresso::query()
            ->whereHas('user', function ($q) {
                $q->where('is_admin', false)
                  ->where('tipo', '!=', 'admin')
                  ->where('role', '!=', 'admin');
            })
            ->where(function ($q) {
                $q->where('status_validacao', 'aprovado')
                  ->orWhere(function ($sub) {
                      $sub->where('status_validacao', 'pendente')
                          ->whereNotNull('numero_processo')
                          ->where('numero_processo', 'NOT LIKE', 'PENDENTE_%')
                          ->where('numero_processo', 'NOT LIKE', 'ADMIN_%');
                  });
            });
    }

    /**
     * ============================================================
     * SCOPE: Egressos PENDENTES de validação
     * ============================================================
     * Só os PENDENTE_* (registados pelo próprio)
     */
    private function scopeEgressosPendentesValidacao()
    {
        return Egresso::query()
            ->whereHas('user', function ($q) {
                $q->where('is_admin', false)
                  ->where('tipo', '!=', 'admin')
                  ->where('role', '!=', 'admin');
            })
            ->where('status_validacao', 'pendente')
            ->whereNotNull('numero_processo')
            ->where('numero_processo', 'LIKE', 'PENDENTE_%');
    }

    public function index()
    {
        // ============================================================
        // 1. ESTATÍSTICAS PRINCIPAIS
        // ============================================================

        $totalEgressos = $this->scopeEgressosReais()->count();

        $egressosActivos = $this->scopeEgressosReais()
            ->where('status', 'active')
            ->count();

        $egressosVerificados = $this->scopeEgressosReais()
            ->where('verificado', true)
            ->count();

        $egressosPendentes = $this->scopeEgressosPendentesValidacao()->count();

        // ============================================================
        // 2. OUTRAS ESTATÍSTICAS
        // ============================================================

        $stats = [
            'total_egressos'        => $totalEgressos,
            'egressos_activos'      => $egressosActivos,
            'egressos_verificados'  => $egressosVerificados,
            'egressos_pendentes'    => $egressosPendentes,

            // Gráfico circular — Distribuição
            'egressos_inativos'     => $this->scopeEgressosReais()
                ->where('status', 'inactive')
                ->count(),

            'egressos_lost_contact' => $this->scopeEgressosReais()
                ->where('status', 'lost_contact')
                ->count(),

            // Restantes campos
            'total_oportunidades'       => Oportunidade::where('is_active', true)->count(),
            'total_eventos'             => Evento::where('is_active', true)->count(),
            'total_unidades'            => UnidadeOrganica::count(),

            'total_candidaturas'        => Candidatura::count(),
            'total_inscricoes'          => InscricaoEvento::count(),

            'total_feedbacks_pendentes' => Feedback::where('status', 'pendente')->count(),
            'total_servicos_pendentes'  => Servico::where('status', 'pendente')->count(),
        ];

        // ============================================================
        // 3. EGRESSOS POR UNIDADE (gráfico de barras)
        // ============================================================

        $stats_unidades = DB::table('unidades_organicas as u')
            ->select(
                'u.sigla',
                DB::raw('COUNT(e.id) as total')
            )
            ->leftJoin('cursos as c', 'c.unidade_id', '=', 'u.id')
            ->leftJoin('egressos as e', function ($join) {
                $join->on('e.curso_id', '=', 'c.id')
                     ->where('e.status', 'active')
                     ->where(function ($query) {
                         $query->where('e.status_validacao', 'aprovado')
                               ->orWhere(function ($sub) {
                                   $sub->where('e.status_validacao', 'pendente')
                                       ->whereNotNull('e.numero_processo')
                                       ->where('e.numero_processo', 'NOT LIKE', 'PENDENTE_%')
                                       ->where('e.numero_processo', 'NOT LIKE', 'ADMIN_%');
                               });
                     })
                     ->where(function ($query) {
                         $query->whereNull('e.numero_processo')
                               ->orWhere(function ($sub) {
                                   $sub->where('e.numero_processo', 'NOT LIKE', 'ADMIN_%')
                                       ->where('e.numero_processo', 'NOT LIKE', 'PENDENTE_%');
                               });
                     });
            })
            ->leftJoin('users as usr', 'e.user_id', '=', 'usr.id')
            ->where(function ($query) {
                $query->whereNull('usr.id')
                    ->orWhere(function ($q) {
                        $q->where(function ($role) {
                            $role->whereNull('usr.role')
                                 ->orWhere('usr.role', '!=', 'admin');
                        })
                        ->where(function ($tipo) {
                            $tipo->whereNull('usr.tipo')
                                 ->orWhere('usr.tipo', '!=', 'admin');
                        });
                    });
            })
            ->groupBy('u.id', 'u.sigla')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'sigla' => $item->sigla,
                    'total' => (int) $item->total,
                ];
            })
            ->toArray();

        // ============================================================
        // 4. ÚLTIMOS EGRESSOS (apenas egressos reais)
        // ============================================================

        $ultimos_egressos = $this->scopeEgressosReais()
            ->with(['curso.unidade', 'user'])
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ============================================================
        // 5. OPORTUNIDADES RECENTES
        // ============================================================

        $oportunidades_recentes = Oportunidade::with('unidade')
            ->withCount('candidaturas')
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDays(7))
            ->where(function ($query) {
                $query->whereNull('data_limite')
                      ->orWhere('data_limite', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ============================================================
        // 6. PRÓXIMOS EVENTOS
        // ============================================================

        $eventos_proximos = Evento::with('unidade')
            ->withCount('inscricoes')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('data_fim')
                      ->where('data_fim', '>=', now());
                })
                ->orWhere(function ($q) {
                    $q->whereNull('data_fim')
                      ->where('data_inicio', '>=', now());
                });
            })
            ->orderBy('data_inicio', 'asc')
            ->take(5)
            ->get();

        // ============================================================
        // 7. EGRESSOS POR CURSO
        // ============================================================

        $stats_cursos = DB::table('cursos as c')
            ->select(
                'c.nome',
                DB::raw('COUNT(e.id) as total')
            )
            ->leftJoin('egressos as e', function ($join) {
                $join->on('e.curso_id', '=', 'c.id')
                    ->where('e.status', 'active')
                    ->where(function ($query) {
                        $query->where('e.status_validacao', 'aprovado')
                              ->orWhere(function ($sub) {
                                  $sub->where('e.status_validacao', 'pendente')
                                      ->whereNotNull('e.numero_processo')
                                      ->where('e.numero_processo', 'NOT LIKE', 'PENDENTE_%')
                                      ->where('e.numero_processo', 'NOT LIKE', 'ADMIN_%');
                              });
                    })
                    ->where(function ($query) {
                        $query->whereNull('e.numero_processo')
                              ->orWhere(function ($sub) {
                                  $sub->where('e.numero_processo', 'NOT LIKE', 'ADMIN_%')
                                      ->where('e.numero_processo', 'NOT LIKE', 'PENDENTE_%');
                              });
                    });
            })
            ->leftJoin('users as usr', 'e.user_id', '=', 'usr.id')
            ->where(function ($query) {
                $query->whereNull('usr.id')
                    ->orWhere(function ($q) {
                        $q->where(function ($role) {
                            $role->whereNull('usr.role')
                                 ->orWhere('usr.role', '!=', 'admin');
                        })
                        ->where(function ($tipo) {
                            $tipo->whereNull('usr.tipo')
                                 ->orWhere('usr.tipo', '!=', 'admin');
                        });
                    });
            })
            ->groupBy('c.id', 'c.nome')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nome'  => $item->nome,
                    'total' => (int) $item->total,
                ];
            })
            ->toArray();

        // ============================================================
        // 8. ATIVIDADES RECENTES
        // ============================================================

        $atividades_recentes = collect();

        // 8.1 Novos egressos (apenas egressos reais)
        $this->scopeEgressosReais()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->each(function ($e) use ($atividades_recentes) {
                $atividades_recentes->push([
                    'tipo'      => 'egresso',
                    'icone'     => 'user-plus',
                    'cor'       => 'primary',
                    'titulo'    => 'Novo egresso registado',
                    'descricao' => $e->nome_completo,
                    'data'      => $e->created_at,
                ]);
            });

        // 8.2 Últimas candidaturas
        Candidatura::with(['egresso', 'oportunidade'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->each(function ($c) use ($atividades_recentes) {
                $atividades_recentes->push([
                    'tipo'      => 'candidatura',
                    'icone'     => 'briefcase',
                    'cor'       => 'success',
                    'titulo'    => 'Nova candidatura',
                    'descricao' => ($c->egresso?->nome_completo ?? 'Egresso')
                                 . ' → ' .
                                 ($c->oportunidade?->titulo ?? 'Oportunidade'),
                    'data'      => $c->created_at,
                ]);
            });

        // 8.3 Últimas inscrições em eventos
        InscricaoEvento::with(['egresso', 'evento'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->each(function ($i) use ($atividades_recentes) {
                $atividades_recentes->push([
                    'tipo'      => 'inscricao',
                    'icone'     => 'calendar-check',
                    'cor'       => 'info',
                    'titulo'    => 'Nova inscrição em evento',
                    'descricao' => ($i->egresso?->nome_completo ?? 'Egresso')
                                 . ' → ' .
                                 ($i->evento?->titulo ?? 'Evento'),
                    'data'      => $i->created_at,
                ]);
            });

        // ============================================================
        // 9. ORDENAR ATIVIDADES
        // ============================================================

        $atividades_recentes = $atividades_recentes
            ->sortByDesc('data')
            ->take(8)
            ->values();

        // ============================================================
        // 10. RETORNAR PARA A VIEW
        // ============================================================

        return view(
            'admin.dashboard',
            compact(
                'stats',
                'stats_unidades',
                'stats_cursos',
                'ultimos_egressos',
                'oportunidades_recentes',
                'eventos_proximos',
                'atividades_recentes'
            )
        );
    }
}