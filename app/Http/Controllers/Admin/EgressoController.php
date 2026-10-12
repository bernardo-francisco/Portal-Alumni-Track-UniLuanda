<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use App\Models\Localizacao;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class EgressoController extends Controller
{
    // ================================================================
    // 📋 LISTA DE EGRESSOS
    // ================================================================
    /**
     * Lista APENAS:
     *   1. Egressos APROVADOS pelo admin
     *   2. Egressos CRIADOS pelo admin (nunca verificaram email nem reivindicaram)
     *
     * NÃO mostra:
     *   - Egressos que se registaram sozinhos (Nível Prata) e estão pendentes
     *   - Egressos que reivindicaram conta (Nível Ouro) e estão pendentes
     *   - Egressos reprovados
     *
     * Esses aparecem apenas em /admin/validacao
     */
        public function index(Request $request)
        {
            $query = Egresso::with(['curso.unidade', 'user'])
                ->whereHas('user', function ($q) {
                    $q->where('is_admin', false)
                    ->where('tipo', '!=', 'admin')
                    ->where('role', '!=', 'admin');
                })
                ->where(function ($q) {
                    // 1. Egressos APROVADOS pelo admin
                    $q->where('status_validacao', 'aprovado')
                    // 2. OU egressos CRIADOS pelo admin
                    //    (nº processo normal, sem prefixos PENDENTE_ / ADMIN_)
                    ->orWhere(function ($sub) {
                        $sub->where('status_validacao', 'pendente')
                            ->whereNotNull('numero_processo')
                            ->where('numero_processo', 'NOT LIKE', 'PENDENTE_%')
                            ->where('numero_processo', 'NOT LIKE', 'ADMIN_%');
                    });
                });

            // Filtro: Pesquisa
            if ($request->filled('q')) {
                $termo = $request->q;
                $query->where(function ($q) use ($termo) {
                    $q->where('nome_completo', 'like', "%{$termo}%")
                    ->orWhere('numero_processo', 'like', "%{$termo}%")
                    ->orWhere('email', 'like', "%{$termo}%");
                });
            }

            // Filtro: Status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtro: Curso
            if ($request->filled('curso_id')) {
                $query->where('curso_id', $request->curso_id);
            }

            // Filtro: Unidade
            if ($request->filled('unidade_id')) {
                $query->whereHas('curso', fn($q) => $q->where('unidade_id', $request->unidade_id));
            }

            $egressos  = $query->orderBy('nome_completo')->paginate(20)->appends($request->query());
            $cursos    = Curso::orderBy('nome')->get();
            $unidades  = UnidadeOrganica::orderBy('nome')->get();

            // ============================================================
            // ✅ STATS — apenas egressos REAIS
            // (aprovados + criados pelo admin, excluindo admins e registos PENDENTE_)
            // ============================================================
            $baseQuery = fn() => Egresso::whereHas('user', function ($q) {
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

            $stats = [
                'total'       => $baseQuery()->count(),
                'ativos'      => $baseQuery()->where('status', 'active')->count(),
                'inativos'    => $baseQuery()->where('status', 'inactive')->count(),
                'verificados' => $baseQuery()->where('verificado', true)->count(),
            ];

            return view('admin.egressos.index', compact('egressos', 'cursos', 'unidades', 'stats'));
        }

    // ================================================================
    // 🆕 FORMULÁRIO DE CRIAÇÃO
    // ================================================================
    public function create()
    {
        $cursos   = Curso::with('unidade')->orderBy('nome')->get();
        $unidades = UnidadeOrganica::orderBy('nome')->get();

        return view('admin.egressos.create', compact('cursos', 'unidades'));
    }

    // ================================================================
    // 💾 GUARDAR NOVO EGRESSO
    // ================================================================
    public function store(Request $request)
    {
        $request->validate([
            'numero_processo' => 'required|string|max:50|unique:egressos,numero_processo',
            'nome_completo'   => 'required|string|max:255',
            'genero'          => 'required|in:M,F,O',
            'email'           => 'nullable|email|max:255',
            'foto'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'unidade_id'      => 'required|exists:unidades_organicas,id',
            'curso_id'        => 'required|exists:cursos,id',
            'status'          => 'required|in:active,inactive,lost_contact',
        ]);

        // Validar unidade ↔ curso
        $curso = Curso::find($request->curso_id);
        if ($curso->unidade_id != $request->unidade_id) {
            return back()->withErrors([
                'curso_id' => 'O curso não pertence à unidade selecionada.'
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // ============================================================
            // ✅ 1. GUARDAR A FOTO (antes de criar o egresso)
            // ============================================================
            $fotoPath = null;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');

                if ($file->isValid()) {
                    $pasta = public_path('uploads/egressos');
                    if (!file_exists($pasta)) {
                        mkdir($pasta, 0755, true);
                    }

                    $filename = 'egresso_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($pasta, $filename);
                    $fotoPath = 'uploads/egressos/' . $filename;
                }
            }

            // ============================================================
            // ✅ 2. CRIAR USER
            // ============================================================
            // Nota: password aleatória de 32 chars + is_active = false
            // O egresso real vai "reivindicar" esta conta mais tarde.
            // ============================================================
            $user = User::create([
                'name'              => $request->nome_completo,
                'email'             => $request->email ?? $request->numero_processo . '@uniluanda.ao',
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'egresso',
                'is_active'         => false,
                'email_verified_at' => null,
            ]);

            // ============================================================
            // ✅ 3. CRIAR EGRESSO
            // ============================================================
            $egresso = Egresso::create([
            'user_id'            => $user->id,
            'numero_processo'    => $request->numero_processo,
            'nome_completo'      => $request->nome_completo,
            'genero'             => $request->genero,
            'data_nascimento'    => $request->data_nascimento,
            'email'              => $request->email,
            'telefone'           => $request->telefone,
            'foto_url'           => $fotoPath,
            'curso_id'           => $request->curso_id,
            'ano_formatura'      => $request->ano_formatura,
            'nota_final'         => $request->nota_final,

            // ✅ CRIADO PELO ADMIN = já aprovado (não precisa de validação)
            'status_validacao'   => 'aprovado',
            'verificado'         => true,
            'data_validacao'     => now(),
            'validado_por'       => Auth::id(),

            'status'             => $request->status,
            'observacoes'        => $request->observacoes,

            // FASE 3 — Marcar como NÃO reivindicado
            'claimed_at'         => null,
            'claimed_ip'         => null,
            'claimed_user_agent' => null,
        ]);

            // ============================================================
            // ✅ 4. GUARDAR LOCALIZAÇÃO (se houver dados)
            // ============================================================
            $temDadosLocalizacao =
                $request->filled('pais') ||
                $request->filled('provincia') ||
                $request->filled('cidade') ||
                $request->filled('endereco') ||
                $request->filled('latitude') ||
                $request->filled('longitude');

            if ($temDadosLocalizacao) {
                Localizacao::create([
                    'egresso_id' => $egresso->id,
                    'pais'       => $request->pais,
                    'provincia'  => $request->provincia,
                    'cidade'     => $request->cidade,
                    'endereco'   => $request->endereco,
                    'latitude'   => $request->latitude,
                    'longitude'  => $request->longitude,
                    'data_desde' => $request->data_desde,
                    'is_current' => 1,
                ]);
            }

            // ============================================================
            // ✅ 5. GUARDAR PROFISSIONAL (se houver dados)
            // ============================================================
            $temDadosProfissional =
                $request->filled('cargo') ||
                $request->filled('empregador') ||
                $request->filled('sector') ||
                $request->filled('emprego_tipo');

            if ($temDadosProfissional) {
                Profissional::create([
                    'egresso_id'   => $egresso->id,
                    'tipo_emprego' => $request->emprego_tipo ?? 'unknown',
                    'cargo'        => $request->cargo,
                    'empregador'   => $request->empregador,
                    'sector'       => $request->sector,
                    'data_inicio'  => $request->data_inicio,
                    'linkedin_url' => $request->linkedin_url,
                    'is_current'   => 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.egressos.show', $egresso->id)
                ->with('success', 'Egresso criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar egresso: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar egresso: ' . $e->getMessage())->withInput();
        }
    }

    // ================================================================
    // 👁️ MOSTRAR EGRESSO
    // ================================================================
    public function show($id)
    {
        $egresso = Egresso::with([
            'user',
            'curso.unidade',
            'localizacoes',
            'profissionais',
            'localizacaoAtual',
            'profissionalAtual',
        ])->findOrFail($id);

        // ✅ Proteção: se é um egresso PENDENTE (registou-se sozinho),
        //    redirecionar para a página de validação.
        $isPendenteSozinho = (
            $egresso->status_validacao === 'pendente' &&
            (
                $egresso->claimed_at !== null ||
                ($egresso->user && $egresso->user->email_verificado_at !== null)
            )
        );

        if ($isPendenteSozinho) {
            return redirect()
                ->route('admin.validacao.show', $egresso->id)
                ->with('info', 'Este egresso está pendente de validação.');
        }

        return view('admin.egressos.show', compact('egresso'));
    }

    // ================================================================
    // ✏️ FORMULÁRIO DE EDIÇÃO
    // ================================================================
    public function edit($id)
    {
        $egresso = Egresso::with([
            'curso.unidade',
            'localizacaoAtual',
            'profissionalAtual',
        ])->findOrFail($id);

        $cursos   = Curso::with('unidade')->orderBy('nome')->get();
        $unidades = UnidadeOrganica::orderBy('nome')->get();

        return view('admin.egressos.edit', compact('egresso', 'cursos', 'unidades'));
    }

    // ================================================================
    // 🔄 ATUALIZAR EGRESSO
    // ================================================================
    public function update(Request $request, $id)
    {
        $egresso = Egresso::findOrFail($id);

        // ============================================================
        // VALIDAÇÃO
        // ============================================================
        $request->validate([
            'numero_processo'   => 'required|string|max:50|unique:egressos,numero_processo,' . $id,
            'nome_completo'     => 'required|string|max:255',
            'genero'            => 'required|in:M,F,O',
            'data_nascimento'   => 'nullable|date',
            'email'             => 'nullable|email|max:255',
            'telefone'          => 'nullable|string|max:20',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Dados académicos
            'unidade_id'        => 'required|exists:unidades_organicas,id',
            'curso_id'          => 'required|exists:cursos,id',
            'ano_formatura'     => 'nullable|integer|min:1990|max:' . (date('Y') + 5),
            'nota_final'        => 'nullable|numeric|min:0|max:20',

            // Localização
            'pais'              => 'nullable|string|max:100',
            'provincia'         => 'nullable|string|max:100',
            'cidade'            => 'nullable|string|max:100',
            'data_desde'        => 'nullable|date',
            'latitude'          => 'nullable|string|max:50',
            'longitude'         => 'nullable|string|max:50',
            'endereco'          => 'nullable|string|max:255',
            'is_current'        => 'nullable|boolean',

            // Profissional
            'emprego_tipo'      => 'nullable|in:full_time,part_time,freelance,self_employed,unemployed,student,unknown',
            'cargo'             => 'nullable|string|max:255',
            'empregador'        => 'nullable|string|max:255',
            'sector'            => 'nullable|string|max:255',
            'data_inicio'       => 'nullable|date',
            'linkedin_url'      => 'nullable|url|max:255',
            'is_current_emprego'=> 'nullable|boolean',

            // Status
            'status'            => 'required|in:active,inactive,lost_contact',
            'observacoes'       => 'nullable|string|max:1000',
        ]);

        // ============================================================
        // ✅ VALIDAÇÃO CRUZADA: curso pertence à unidade?
        // ============================================================
        $curso = Curso::find($request->curso_id);

        if (!$curso) {
            return back()->withErrors(['curso_id' => 'O curso selecionado não existe.'])->withInput();
        }

        if ($curso->unidade_id != $request->unidade_id) {
            return back()
                ->withErrors([
                    'curso_id' => '❌ O curso "' . $curso->nome . '" não pertence à unidade orgânica selecionada.'
                ])
                ->withInput();
        }

        // ============================================================
        // FOTO (opcional)
        // ============================================================
        $fotoPath = $egresso->foto_url;
        if ($request->hasFile('foto')) {
            if ($fotoPath && file_exists(public_path($fotoPath))) {
                @unlink(public_path($fotoPath));
            }

            $file     = $request->file('foto');
            $filename = 'egresso_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/egressos'), $filename);
            $fotoPath = 'uploads/egressos/' . $filename;
        }

        // ============================================================
        // ✅ ATUALIZAR EGRESSO — preservar valores antigos
        // ============================================================
        $egresso->update([
            'numero_processo'  => $request->numero_processo,
            'nome_completo'    => $request->nome_completo,
            'genero'           => $request->genero,
            'data_nascimento'  => $request->filled('data_nascimento') ? $request->data_nascimento : $egresso->data_nascimento,
            'email'            => $request->filled('email') ? $request->email : $egresso->email,
            'telefone'         => $request->filled('telefone') ? $request->telefone : $egresso->telefone,
            'foto_url'         => $fotoPath,

            'curso_id'         => $request->curso_id,
            'ano_formatura'    => $request->filled('ano_formatura') ? $request->ano_formatura : $egresso->ano_formatura,
            'nota_final'       => $request->filled('nota_final') ? $request->nota_final : $egresso->nota_final,

            'status'           => $request->status,
            'observacoes'      => $request->filled('observacoes') ? $request->observacoes : $egresso->observacoes,
        ]);

        // ============================================================
        // ✅ LOCALIZAÇÃO
        // ============================================================
        $temDadosLocalizacao =
            $request->filled('pais') ||
            $request->filled('provincia') ||
            $request->filled('cidade') ||
            $request->filled('endereco') ||
            $request->filled('latitude') ||
            $request->filled('longitude') ||
            $request->filled('data_desde');

        if ($temDadosLocalizacao) {

            $totalLocalizacoes = Localizacao::where('egresso_id', $egresso->id)->count();
            $marcarComoAtual = $request->has('is_current') || $totalLocalizacoes === 0;

            if ($marcarComoAtual) {
                Localizacao::where('egresso_id', $egresso->id)
                    ->update(['is_current' => false]);
            }

            $localizacao = Localizacao::where('egresso_id', $egresso->id)
                ->where('is_current', true)
                ->first();

            if (!$localizacao) {
                $localizacao = new Localizacao();
                $localizacao->egresso_id = $egresso->id;
            }

            $localizacao->fill([
                'pais'       => $request->filled('pais') ? $request->pais : ($localizacao->pais ?? null),
                'provincia'  => $request->filled('provincia') ? $request->provincia : ($localizacao->provincia ?? null),
                'cidade'     => $request->filled('cidade') ? $request->cidade : ($localizacao->cidade ?? null),
                'endereco'   => $request->filled('endereco') ? $request->endereco : ($localizacao->endereco ?? null),
                'latitude'   => $request->filled('latitude') ? $request->latitude : ($localizacao->latitude ?? null),
                'longitude'  => $request->filled('longitude') ? $request->longitude : ($localizacao->longitude ?? null),
                'data_desde' => $request->filled('data_desde') ? $request->data_desde : ($localizacao->data_desde ?? null),
                'is_current' => 1,
            ]);

            $localizacao->save();
        }

        // ============================================================
        // ✅ PROFISSIONAL
        // ============================================================
        $temDadosProfissional =
            $request->filled('emprego_tipo') ||
            $request->filled('cargo') ||
            $request->filled('empregador') ||
            $request->filled('sector') ||
            $request->filled('data_inicio') ||
            $request->filled('linkedin_url');

        if ($temDadosProfissional) {

            $totalProfissionais = Profissional::where('egresso_id', $egresso->id)->count();
            $marcarComoAtual = $request->has('is_current_emprego') || $totalProfissionais === 0;

            if ($marcarComoAtual) {
                Profissional::where('egresso_id', $egresso->id)
                    ->update(['is_current' => false]);
            }

            $profissional = Profissional::where('egresso_id', $egresso->id)
                ->where('is_current', true)
                ->first();

            if (!$profissional) {
                $profissional = new Profissional();
                $profissional->egresso_id = $egresso->id;
            }

            $tipoEmprego = $request->filled('emprego_tipo')
                ? $request->emprego_tipo
                : ($profissional->tipo_emprego ?? 'unknown');

            $profissional->fill([
                'tipo_emprego' => $tipoEmprego,
                'cargo'        => $request->filled('cargo') ? $request->cargo : ($profissional->cargo ?? null),
                'empregador'   => $request->filled('empregador') ? $request->empregador : ($profissional->empregador ?? null),
                'sector'       => $request->filled('sector') ? $request->sector : ($profissional->sector ?? null),
                'data_inicio'  => $request->filled('data_inicio') ? $request->data_inicio : ($profissional->data_inicio ?? null),
                'linkedin_url' => $request->filled('linkedin_url') ? $request->linkedin_url : ($profissional->linkedin_url ?? null),
                'is_current'   => 1,
            ]);

            $profissional->save();
        }

        return redirect()
            ->route('admin.egressos.show', $egresso->id)
            ->with('success', 'Egresso atualizado com sucesso!');
    }

    // ================================================================
    // 🗑️ ELIMINAR EGRESSO
    // ================================================================
    public function destroy($id)
    {
        $egresso = Egresso::findOrFail($id);

        DB::beginTransaction();
        try {
            $user = $egresso->user;
            $egresso->delete();
            if ($user) $user->delete();

            DB::commit();
            return redirect()
                ->route('admin.egressos.index')
                ->with('success', 'Egresso eliminado com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao eliminar egresso: ' . $e->getMessage());
            return back()->with('error', 'Erro ao eliminar egresso.');
        }
    }

    // ================================================================
    // ✅ VERIFICAR EGRESSO
    // ================================================================
    public function verificar($id)
    {
        $egresso = Egresso::findOrFail($id);
        $egresso->update([
            'verificado'      => true,
            'data_verificacao'=> now(),
        ]);

        return back()->with('success', 'Egresso verificado!');
    }

    // ================================================================
    // ❌ DESVERIFICAR EGRESSO
    // ================================================================
    public function desverificar($id)
    {
        $egresso = Egresso::findOrFail($id);
        $egresso->update([
            'verificado'      => false,
            'data_verificacao'=> null,
        ]);

        return back()->with('success', 'Verificação removida.');
    }

    // ================================================================
    // ♻️ REATIVAR EGRESSO
    // ================================================================
    public function reativar($id)
    {
        $egresso = Egresso::findOrFail($id);
        $egresso->update(['status' => 'active']);

        return back()->with('success', 'Egresso reativado!');
    }

    // ================================================================
    // 📥 IMPORTAR EGRESSOS
    // ================================================================
    public function importar(Request $request)
    {
        return back()->with('info', 'Funcionalidade de importação em desenvolvimento.');
    }

    // ================================================================
    // 📤 EXPORTAR EGRESSOS
    // ================================================================
    public function exportar()
    {
        return back()->with('info', 'Funcionalidade de exportação em desenvolvimento.');
    }
}