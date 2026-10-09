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
    public function index()
    {
        // ============================================================
        // 1. ESTATÍSTICAS PRINCIPAIS
        // ============================================================

        /*
        |--------------------------------------------------------------------------
        | TOTAL DE EGRESSOS
        |--------------------------------------------------------------------------
        | Conta TODOS os egressos reais, independentemente do estado.
        | O administrador NÃO entra porque:
        | 1. A conta ligada ao egresso tem role/tipo = admin
        | 2. O perfil administrativo possui numero_processo ADMIN_...
        |--------------------------------------------------------------------------
        */

        $totalEgressos = Egresso::query()
            ->where(function ($query) {
                $query->whereDoesntHave('user', function ($q) {
                    $q->where(function ($admin) {
                        $admin->where('role', 'admin')
                              ->orWhere('tipo', 'admin');
                    });
                })
                ->orWhereNull('user_id');
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | EGRESSOS ATIVOS
        |--------------------------------------------------------------------------
        */

        $egressosActivos = Egresso::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereDoesntHave('user', function ($q) {
                    $q->where(function ($admin) {
                        $admin->where('role', 'admin')
                              ->orWhere('tipo', 'admin');
                    });
                })
                ->orWhereNull('user_id');
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | EGRESSOS VERIFICADOS
        |--------------------------------------------------------------------------
        */

        $egressosVerificados = Egresso::query()
            ->where('verificado', true)
            ->where(function ($query) {
                $query->whereDoesntHave('user', function ($q) {
                    $q->where(function ($admin) {
                        $admin->where('role', 'admin')
                              ->orWhere('tipo', 'admin');
                    });
                })
                ->orWhereNull('user_id');
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | EGRESSOS PENDENTES DE VALIDAÇÃO
        |--------------------------------------------------------------------------
        */

        $egressosPendentes = Egresso::query()
            ->where('status_validacao', 'pendente')
            ->where(function ($query) {
                $query->whereDoesntHave('user', function ($q) {
                    $q->where(function ($admin) {
                        $admin->where('role', 'admin')
                              ->orWhere('tipo', 'admin');
                    });
                })
                ->orWhereNull('user_id');
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | OUTRAS ESTATÍSTICAS
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_egressos'        => $totalEgressos,
            'egressos_activos'      => $egressosActivos,
            'egressos_verificados'  => $egressosVerificados,
            'egressos_pendentes'    => $egressosPendentes,

            // Gráfico circular — Distribuição
            'egressos_inativos'     => Egresso::query()
                ->where('status', 'inactive')
                ->where(function ($query) {
                    $query->whereDoesntHave('user', function ($q) {
                        $q->where(function ($admin) {
                            $admin->where('role', 'admin')->orWhere('tipo', 'admin');
                        });
                    })->orWhereNull('user_id');
                })
                ->where(function ($query) {
                    $query->whereNull('numero_processo')
                        ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
                })
                ->count(),

            'egressos_lost_contact' => Egresso::query()
                ->where('status', 'lost_contact')
                ->where(function ($query) {
                    $query->whereDoesntHave('user', function ($q) {
                        $q->where(function ($admin) {
                            $admin->where('role', 'admin')->orWhere('tipo', 'admin');
                        });
                    })->orWhereNull('user_id');
                })
                ->where(function ($query) {
                    $query->whereNull('numero_processo')
                        ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
                })
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
        // 2. EGRESSOS POR UNIDADE (gráfico de barras)
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
                         $query->whereNull('e.numero_processo')
                               ->orWhere('e.numero_processo', 'NOT LIKE', 'ADMIN_%');
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
        // 3. ÚLTIMOS EGRESSOS
        // ============================================================

        $ultimos_egressos = Egresso::with([
                'curso.unidade',
                'user'
            ])
            ->where('status', 'active')
            ->whereDoesntHave('user', function ($q) {
                $q->where(function ($admin) {
                    $admin->where('role', 'admin')
                          ->orWhere('tipo', 'admin');
                });
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();


        // ============================================================
        // 4. OPORTUNIDADES RECENTES
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
        // 5. PRÓXIMOS EVENTOS
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
        // 6. EGRESSOS POR CURSO
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
                        $query->whereNull('e.numero_processo')
                              ->orWhere('e.numero_processo', 'NOT LIKE', 'ADMIN_%');
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
        // 7. ATIVIDADES RECENTES
        // ============================================================

        $atividades_recentes = collect();

        // 7.1 Novos egressos
        Egresso::with('user')
            ->whereDoesntHave('user', function ($q) {
                $q->where(function ($admin) {
                    $admin->where('role', 'admin')
                          ->orWhere('tipo', 'admin');
                });
            })
            ->where(function ($query) {
                $query->whereNull('numero_processo')
                      ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
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

        // 7.2 Últimas candidaturas
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

        // 7.3 Últimas inscrições em eventos
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
        // 8. ORDENAR ATIVIDADES
        // ============================================================

        $atividades_recentes = $atividades_recentes
            ->sortByDesc('data')
            ->take(8)
            ->values();


        // ============================================================
        // 9. RETORNAR PARA A VIEW
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