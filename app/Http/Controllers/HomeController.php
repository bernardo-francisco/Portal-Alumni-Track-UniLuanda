<?php

namespace App\Http\Controllers;

use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use App\Models\Oportunidade;
use App\Models\Evento;
use App\Models\Feedback;
use App\Models\ContactoAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    /**
     * ============================================================
     * PÁGINA INICIAL
     * ============================================================
     */
    public function index()
    {
        try {
            return view('home', $this->getDadosHome());

        } catch (\Throwable $e) {

            Log::warning(
                'Erro ao carregar dados da Home: ' . $e->getMessage()
            );

            return view('home', $this->getDadosVazios());
        }
    }


    /**
     * ============================================================
     * CONTACTO / APOIO AO ALUMNI
     * ============================================================
     *
     * Processa o formulário existente no modal "Apoio ao Alumni".
     */
    public function contacto(Request $request)
    {
        $validated = $request->validate(
            [
                'nome' => ['required', 'string', 'max:200'],
                'email' => ['required', 'email', 'max:200'],
                'assunto' => ['required', 'string', 'max:100'],
                'mensagem' => ['required', 'string', 'max:2000'],
            ],
            [
                'nome.required' => 'Por favor, informe o seu nome.',
                'email.required' => 'Por favor, informe o seu email.',
                'email.email' => 'Por favor, informe um email válido.',
                'assunto.required' => 'Por favor, selecione um assunto.',
                'mensagem.required' => 'Por favor, escreva a sua mensagem.',
            ]
        );

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. GUARDAR NA BASE DE DADOS
            |--------------------------------------------------------------------------
            */
            $contacto = ContactoAlumni::create([
                'nome'     => $validated['nome'],
                'email'    => $validated['email'],
                'assunto'  => $validated['assunto'],
                'mensagem' => $validated['mensagem'],
                'ip'       => $request->ip(),
                'status'   => 'novo',
            ]);


            /*
            |--------------------------------------------------------------------------
            | 2. CRIAR NOTIFICAÇÃO PARA TODOS OS ADMINS
            |--------------------------------------------------------------------------
            */
            try {

                $admins = \App\Models\Admin::all();

                foreach ($admins as $admin) {

                    \App\Models\Notificacao::create([
                        'admin_id' => $admin->id,
                        'tipo'     => 'contacto',
                        'titulo'   => '📧 Nova mensagem de Apoio ao Alumni',
                        'mensagem' => $validated['nome'] . ' — ' . $validated['assunto'],
                        'link'     => route('admin.contactos.show', $contacto->id),
                        'lida'     => false,
                    ]);
                }

                Log::info('Notificações criadas para admins', [
                    'contacto_id' => $contacto->id,
                    'total_admins' => $admins->count(),
                ]);

            } catch (\Throwable $e) {

                Log::warning(
                    'Erro ao criar notificações de contacto: ' .
                    $e->getMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 3. REGISTAR NO LOG
            |--------------------------------------------------------------------------
            */
            Log::info('Nova mensagem de Apoio ao Alumni', [
                'id'       => $contacto->id,
                'nome'     => $validated['nome'],
                'email'    => $validated['email'],
                'assunto'  => $validated['assunto'],
                'ip'       => $request->ip(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 4. REDIRECIONAR
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route('home')
                ->with('success', '✅ Mensagem enviada com sucesso! Entraremos em contacto brevemente.')
                ->withFragment('modalApoio');

        } catch (\Throwable $e) {

            Log::error('Erro ao processar contacto: ' . $e->getMessage());

            return redirect()
                ->route('home')
                ->with('error', '❌ Erro ao enviar a mensagem. Tente novamente.')
                ->withInput()
                ->withFragment('modalApoio');
        }
    }


    /**
     * ============================================================
     * DADOS DA HOME
     * ============================================================
     */
    private function getDadosHome(): array
    {
        /*
        |--------------------------------------------------------------------------
        | 1. ESTATÍSTICAS PRINCIPAIS
        |--------------------------------------------------------------------------
        */

        $totalEgressos = 0;

        try {

            if (Schema::hasTable('egressos')) {

                $totalEgressos = Egresso::query()
                    ->whereDoesntHave('user', function ($q) {

                        $q->where('role', 'admin')
                          ->orWhere('tipo', 'admin');

                    })
                    ->where(function ($q) {

                        $q->whereNull('numero_processo')
                          ->orWhere(
                              'numero_processo',
                              'NOT LIKE',
                              'ADMIN_%'
                          );

                    })
                    ->count();
            }

        } catch (\Throwable $e) {

            Log::warning(
                'Erro ao calcular total de egressos: ' .
                $e->getMessage()
            );

            $totalEgressos = 0;
        }


        $totalCursos =
            $this->safeCount('cursos');


        $totalUnidades =
            $this->safeCount('unidades_organicas');


        /*
        |--------------------------------------------------------------------------
        | EMPRESAS PARCEIRAS
        |--------------------------------------------------------------------------
        */

        $totalEmpresas = 0;

        if (Schema::hasTable('oportunidades')) {

            try {

                $totalEmpresas = Oportunidade::query()
                    ->where('is_active', true)
                    ->whereNotNull('empresa')
                    ->where('empresa', '!=', '')
                    ->distinct()
                    ->count('empresa');

            } catch (\Throwable $e) {

                $totalEmpresas = 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | OPORTUNIDADES ATIVAS
        |--------------------------------------------------------------------------
        */

        $totalOportunidades = 0;

        if (Schema::hasTable('oportunidades')) {

            try {

                $totalOportunidades =
                    Oportunidade::query()
                        ->where('is_active', true)
                        ->count();

            } catch (\Throwable $e) {

                $totalOportunidades = 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EVENTOS ATIVOS
        |--------------------------------------------------------------------------
        */

        $totalEventos = 0;

        if (Schema::hasTable('eventos')) {

            try {

                $totalEventos =
                    Evento::query()
                        ->where('is_active', true)
                        ->count();

            } catch (\Throwable $e) {

                $totalEventos = 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL DE PAÍSES
        |--------------------------------------------------------------------------
        */

        $totalPaises = 0;

        if (Schema::hasTable('localizacoes')) {

            try {

                $totalPaises = DB::table('localizacoes as l')
                    ->join(
                        'egressos as e',
                        'e.id',
                        '=',
                        'l.egresso_id'
                    )
                    ->leftJoin(
                        'users as u',
                        'u.id',
                        '=',
                        'e.user_id'
                    )
                    ->where('l.is_current', true)
                    ->whereNotNull('l.pais')
                    ->where('l.pais', '!=', '')
                    ->where(function ($q) {

                        $q->whereNull('u.role')
                          ->orWhere(function ($q2) {

                              $q2->where(
                                  'u.role',
                                  '!=',
                                  'admin'
                              )
                              ->where(
                                  'u.tipo',
                                  '!=',
                                  'admin'
                              );

                          });

                    })
                    ->where(function ($q) {

                        $q->whereNull('e.numero_processo')
                          ->orWhere(
                              'e.numero_processo',
                              'NOT LIKE',
                              'ADMIN_%'
                          );

                    })
                    ->distinct()
                    ->count('l.pais');

            } catch (\Throwable $e) {

                $totalPaises = 0;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. TAXA DE EMPREGABILIDADE
        |--------------------------------------------------------------------------
        */

        $taxaEmpregabilidade = 0;

        try {

            if (
                Schema::hasTable('profissionais') &&
                $totalEgressos > 0
            ) {

                $totalComProfissao = DB::table(
                    'egressos as e'
                )
                    ->join(
                        'profissionais as p',
                        'p.egresso_id',
                        '=',
                        'e.id'
                    )
                    ->leftJoin(
                        'users as u',
                        'u.id',
                        '=',
                        'e.user_id'
                    )
                    ->where('p.is_current', true)
                    ->whereNotNull('p.tipo_emprego')
                    ->whereNotIn(
                        'p.tipo_emprego',
                        [
                            'unemployed',
                            'student',
                            'unknown',
                        ]
                    )
                    ->where(function ($q) {

                        $q->whereNull('u.role')
                          ->orWhere(function ($q2) {

                              $q2->where(
                                  'u.role',
                                  '!=',
                                  'admin'
                              )
                              ->where(
                                  'u.tipo',
                                  '!=',
                                  'admin'
                              );

                          });

                    })
                    ->where(function ($q) {

                        $q->whereNull('e.numero_processo')
                          ->orWhere(
                              'e.numero_processo',
                              'NOT LIKE',
                              'ADMIN_%'
                          );

                    })
                    ->distinct('e.id')
                    ->count('e.id');


                $taxaEmpregabilidade = min(
                    round(
                        (
                            $totalComProfissao /
                            $totalEgressos
                        ) * 100
                    ),
                    100
                );
            }

        } catch (\Throwable $e) {

            $taxaEmpregabilidade = 0;
        }


        /*
        |--------------------------------------------------------------------------
        | 3. DISTRIBUIÇÃO POR UNIDADE
        |--------------------------------------------------------------------------
        */

        $distribuicaoUnidades = [];

        try {

            if (
                Schema::hasTable('unidades_organicas') &&
                Schema::hasTable('cursos') &&
                Schema::hasTable('egressos')
            ) {

                $distribuicaoUnidades =
                    DB::table(
                        'unidades_organicas as u'
                    )
                    ->select(
                        'u.sigla as nome',
                        DB::raw(
                            'count(DISTINCT e.id) as total'
                        )
                    )
                    ->join(
                        'cursos as c',
                        'c.unidade_id',
                        '=',
                        'u.id'
                    )
                    ->join(
                        'egressos as e',
                        'e.curso_id',
                        '=',
                        'c.id'
                    )
                    ->leftJoin(
                        'users as usr',
                        'usr.id',
                        '=',
                        'e.user_id'
                    )
                    ->where(function ($q) {

                        $q->whereNull('usr.role')
                          ->orWhere(function ($q2) {

                              $q2->where(
                                  'usr.role',
                                  '!=',
                                  'admin'
                              )
                              ->where(
                                  'usr.tipo',
                                  '!=',
                                  'admin'
                              );

                          });

                    })
                    ->where(function ($q) {

                        $q->whereNull('e.numero_processo')
                          ->orWhere(
                              'e.numero_processo',
                              'NOT LIKE',
                              'ADMIN_%'
                          );

                    })
                    ->groupBy(
                        'u.id',
                        'u.sigla'
                    )
                    ->orderBy(
                        'total',
                        'desc'
                    )
                    ->limit(5)
                    ->get()
                    ->map(
                        fn ($item) => [
                            'nome' =>
                                $item->nome ?? 'N/A',

                            'total' =>
                                (int) $item->total,
                        ]
                    )
                    ->toArray();
            }

        } catch (\Throwable $e) {

            $distribuicaoUnidades = [];
        }


        /*
        |--------------------------------------------------------------------------
        | 4. PAÍSES REPRESENTADOS
        |--------------------------------------------------------------------------
        */

        $paises = [];

        try {

            if (Schema::hasTable('localizacoes')) {

                $paises = DB::table(
                    'localizacoes as l'
                )
                    ->select(
                        'l.pais',
                        DB::raw(
                            'count(DISTINCT l.egresso_id) as total'
                        )
                    )
                    ->join(
                        'egressos as e',
                        'e.id',
                        '=',
                        'l.egresso_id'
                    )
                    ->leftJoin(
                        'users as u',
                        'u.id',
                        '=',
                        'e.user_id'
                    )
                    ->where(
                        'l.is_current',
                        true
                    )
                    ->whereNotNull('l.pais')
                    ->where('l.pais', '!=', '')
                    ->where(function ($q) {

                        $q->whereNull('u.role')
                          ->orWhere(function ($q2) {

                              $q2->where(
                                  'u.role',
                                  '!=',
                                  'admin'
                              )
                              ->where(
                                  'u.tipo',
                                  '!=',
                                  'admin'
                              );

                          });

                    })
                    ->where(function ($q) {

                        $q->whereNull('e.numero_processo')
                          ->orWhere(
                              'e.numero_processo',
                              'NOT LIKE',
                              'ADMIN_%'
                          );

                    })
                    ->groupBy('l.pais')
                    ->orderBy(
                        'total',
                        'desc'
                    )
                    ->limit(8)
                    ->pluck(
                        'total',
                        'l.pais'
                    )
                    ->toArray();
            }

        } catch (\Throwable $e) {

            $paises = [];
        }


        /*
        |--------------------------------------------------------------------------
        | 5. EGRESSOS EM DESTAQUE
        |--------------------------------------------------------------------------
        */

        $ultimosEgressos =
            $this->getEgressosDestaque();


        /*
        |--------------------------------------------------------------------------
        | 6. OPORTUNIDADES RECENTES
        |--------------------------------------------------------------------------
        */

        $oportunidadesRecentes =
            collect();

        if (
            Schema::hasTable(
                'oportunidades'
            )
        ) {

            try {

                $oportunidadesRecentes =
                    Oportunidade::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->withCount(
                            'candidaturas'
                        )
                        ->orderBy(
                            'created_at',
                            'desc'
                        )
                        ->take(4)
                        ->get();

            } catch (\Throwable $e) {

                $oportunidadesRecentes =
                    collect();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 7. PRÓXIMOS EVENTOS
        |--------------------------------------------------------------------------
        */

        $proximosEventos =
            collect();

        if (
            Schema::hasTable(
                'eventos'
            )
        ) {

            try {

                $proximosEventos =
                    Evento::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->where(
                            'data_inicio',
                            '>=',
                            now()
                        )
                        ->withCount(
                            'inscricoes'
                        )
                        ->orderBy(
                            'data_inicio',
                            'asc'
                        )
                        ->take(4)
                        ->get();


                if (
                    $proximosEventos->isEmpty()
                ) {

                    $proximosEventos =
                        Evento::query()
                            ->where(
                                'is_active',
                                true
                            )
                            ->withCount(
                                'inscricoes'
                            )
                            ->orderBy(
                                'data_inicio',
                                'desc'
                            )
                            ->take(4)
                            ->get();
                }

            } catch (\Throwable $e) {

                $proximosEventos =
                    collect();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 8. TESTEMUNHOS
        |--------------------------------------------------------------------------
        */

        $testemunhos =
            collect();

        if (
            Schema::hasTable(
                'feedbacks'
            )
        ) {

            try {

                $testemunhos =
                    Feedback::with([
                        'egresso.curso',
                        'curso',
                    ])
                    ->where(
                        'status',
                        'aprovado'
                    )
                    ->whereNotNull(
                        'mensagem'
                    )
                    ->where(
                        'mensagem',
                        '!=',
                        ''
                    )
                    ->whereHas(
                        'egresso',
                        function ($q) {

                            $q->whereDoesntHave(
                                'user',
                                function ($q2) {

                                    $q2->where(
                                        'role',
                                        'admin'
                                    )
                                    ->orWhere(
                                        'tipo',
                                        'admin'
                                    );
                                }
                            );

                            $q->where(
                                function ($q2) {

                                    $q2->whereNull(
                                        'numero_processo'
                                    )
                                    ->orWhere(
                                        'numero_processo',
                                        'NOT LIKE',
                                        'ADMIN_%'
                                    );
                                }
                            );
                        }
                    )
                    ->orderByRaw(
                        'CASE WHEN nota >= 4 THEN 0 ELSE 1 END'
                    )
                    ->orderBy(
                        'created_at',
                        'desc'
                    )
                    ->take(3)
                    ->get();

            } catch (\Throwable $e) {

                $testemunhos =
                    collect();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETORNO
        |--------------------------------------------------------------------------
        */

        return compact(
            'totalEgressos',
            'totalCursos',
            'totalUnidades',
            'totalEmpresas',
            'totalOportunidades',
            'totalEventos',
            'totalPaises',
            'taxaEmpregabilidade',
            'distribuicaoUnidades',
            'paises',
            'ultimosEgressos',
            'oportunidadesRecentes',
            'proximosEventos',
            'testemunhos'
        );
    }


    /**
     * ============================================================
     * EGRESSOS EM DESTAQUE
     * ============================================================
     */
    private function getEgressosDestaque(): \Illuminate\Support\Collection
    {
        try {

            if (
                !Schema::hasTable(
                    'egressos'
                )
            ) {
                return collect();
            }


            $base = Egresso::with([
                'curso.unidade'
            ])
                ->whereNotNull(
                    'nome_completo'
                )
                ->where(
                    'nome_completo',
                    '!=',
                    ''
                )
                ->where(
                    'status',
                    'active'
                )
                ->whereDoesntHave(
                    'user',
                    function ($q) {

                        $q->where(
                            'role',
                            'admin'
                        )
                        ->orWhere(
                            'tipo',
                            'admin'
                        );
                    }
                )
                ->where(
                    function ($q) {

                        $q->whereNull(
                            'numero_processo'
                        )
                        ->orWhere(
                            'numero_processo',
                            'NOT LIKE',
                            'ADMIN_%'
                        );
                    }
                );


            $total =
                (clone $base)->count();


            if (
                $total === 0
            ) {
                return collect();
            }


            $verificados =
                (clone $base)
                    ->where(
                        'verificado',
                        true
                    )
                    ->count();


            if (
                $verificados > 0
            ) {

                $base =
                    (clone $base)
                        ->where(
                            'verificado',
                            true
                        );

                $total =
                    $verificados;
            }


            $diaDoAno =
                now()->dayOfYear;


            $bloco =
                intval(
                    $diaDoAno / 3
                );


            $offset =
                ($bloco * 3) % $total;


            $egressos =
                (clone $base)
                    ->orderBy(
                        'nome_completo'
                    )
                    ->skip(
                        $offset
                    )
                    ->take(3)
                    ->get();


            if (
                $egressos->count() < 3
            ) {

                $faltam =
                    3 -
                    $egressos->count();


                $extras =
                    (clone $base)
                        ->orderBy(
                            'nome_completo'
                        )
                        ->take(
                            $faltam
                        )
                        ->get();


                $egressos =
                    $egressos
                        ->merge($extras)
                        ->unique('id');
            }


            return $egressos
                ->take(3)
                ->values();

        } catch (\Throwable $e) {

            Log::warning(
                'Erro ao carregar egressos em destaque: ' .
                $e->getMessage()
            );

            return collect();
        }
    }


    /**
     * ============================================================
     * CONTAGEM SEGURA
     * ============================================================
     */
    private function safeCount(
        string $tabela
    ): int {

        try {

            if (
                !Schema::hasTable(
                    $tabela
                )
            ) {
                return 0;
            }


            return DB::table(
                $tabela
            )->count();

        } catch (\Throwable $e) {

            return 0;
        }
    }



        /**
         * ============================================================
         * LISTA PÚBLICA DE EGRESSOS (AJAX) — com filtros
         * ============================================================
         */
        public function listarEgressosPublicos(Request $request)
        {
            try {
                if (!Schema::hasTable('egressos')) {
                    return response()->json(['total' => 0, 'items' => []]);
                }

                $termo      = trim($request->get('q', ''));
                $cursoId    = $request->get('curso_id');
                $unidadeId  = $request->get('unidade_id');
                $ordenacao  = $request->get('ordenacao', 'az'); // az, za, recentes

                $query = Egresso::with(['curso.unidade'])
                    ->where('status', 'active')
                    ->whereNotNull('nome_completo')
                    ->where('nome_completo', '!=', '')
                    ->whereDoesntHave('user', function ($q) {
                        $q->where('role', 'admin')
                        ->orWhere('tipo', 'admin');
                    })
                    ->where(function ($q) {
                        $q->whereNull('numero_processo')
                        ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
                    });

                // Filtro por termo (nome, curso, unidade)
                if (mb_strlen($termo) >= 2) {
                    $query->where(function ($q) use ($termo) {
                        $q->where('nome_completo', 'LIKE', "%{$termo}%")
                        ->orWhereHas('curso', function ($q2) use ($termo) {
                            $q2->where('nome', 'LIKE', "%{$termo}%");
                        })
                        ->orWhereHas('curso.unidade', function ($q2) use ($termo) {
                            $q2->where('nome', 'LIKE', "%{$termo}%")
                                ->orWhere('sigla', 'LIKE', "%{$termo}%");
                        });
                    });
                }

                // Filtro por curso
                if (!empty($cursoId)) {
                    $query->where('curso_id', $cursoId);
                }

                // Filtro por unidade orgânica
                if (!empty($unidadeId)) {
                    $query->whereHas('curso', function ($q) use ($unidadeId) {
                        $q->where('unidade_id', $unidadeId);
                    });
                }

                // Ordenação
                switch ($ordenacao) {
                    case 'za':
                        $query->orderBy('nome_completo', 'desc');
                        break;
                    case 'recentes':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'az':
                    default:
                        $query->orderBy('nome_completo', 'asc');
                        break;
                }

                $egressos = $query->limit(100)->get();

                $items = $egressos->map(function ($egresso) {

                    $iniciais = collect(explode(' ', trim($egresso->nome_completo ?? 'E')))
                        ->filter()
                        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                        ->take(2)
                        ->implode('');

                    return [
                        'id'         => $egresso->id,
                        'nome'       => $egresso->nome_completo ?? 'Egresso',
                        'iniciais'   => $iniciais,
                        'foto_url'   => $egresso->foto_url ? asset($egresso->foto_url) : null,
                        'curso'      => $egresso->curso->nome ?? 'Curso não especificado',
                        'unidade'    => $egresso->curso->unidade->sigla
                                            ?? $egresso->curso->unidade->nome
                                            ?? 'Unidade não especificada',
                    ];
                });

                return response()->json([
                    'termo' => $termo,
                    'total' => $items->count(),
                    'items' => $items,
                ]);

            } catch (\Throwable $e) {
                Log::error('Erro ao listar egressos públicos: ' . $e->getMessage());
                return response()->json(['total' => 0, 'items' => []], 500);
            }
        }


        /**
         * ============================================================
         * FILTROS DISPONÍVEIS (cursos + unidades)
         * ============================================================
         */
        public function filtrosEgressos()
        {
            try {
                $cursos = [];
                $unidades = [];

                if (Schema::hasTable('cursos')) {
                    $cursos = DB::table('cursos')
                        ->select('id', 'nome')
                        ->orderBy('nome')
                        ->get()
                        ->map(fn ($c) => ['id' => $c->id, 'nome' => $c->nome]);
                }

                if (Schema::hasTable('unidades_organicas')) {
                    $unidades = DB::table('unidades_organicas')
                        ->select('id', 'nome', 'sigla')
                        ->orderBy('nome')
                        ->get()
                        ->map(fn ($u) => [
                            'id'    => $u->id,
                            'nome'  => $u->sigla ?? $u->nome,
                        ]);
                }

                return response()->json([
                    'cursos'   => $cursos,
                    'unidades' => $unidades,
                ]);

            } catch (\Throwable $e) {
                Log::error('Erro ao carregar filtros: ' . $e->getMessage());
                return response()->json(['cursos' => [], 'unidades' => []]);
            }
        }
    /**
     * ============================================================
     * DADOS VAZIOS
     * ============================================================
     */
    private function getDadosVazios(): array
    {
        return [

            'totalEgressos' =>
                0,

            'totalCursos' =>
                0,

            'totalUnidades' =>
                0,

            'totalEmpresas' =>
                0,

            'totalOportunidades' =>
                0,

            'totalEventos' =>
                0,

            'totalPaises' =>
                0,

            'taxaEmpregabilidade' =>
                0,

            'distribuicaoUnidades' =>
                [],

            'paises' =>
                [],

            'ultimosEgressos' =>
                collect(),

            'oportunidadesRecentes' =>
                collect(),

            'proximosEventos' =>
                collect(),

            'testemunhos' =>
                collect(),
        ];
    }
}