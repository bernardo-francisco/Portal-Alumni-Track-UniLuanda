<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Egresso;
use App\Models\Admin;
use App\Models\Empresa;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\NovoPendenteMail;

class RegisterController extends Controller
{
    /**
     * Mostra o formulário de registro
     */
    public function showRegisterForm()
    {
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        $cursos = Curso::with('unidade')
            ->orderBy('nome')
            ->get();

        return view(
            'auth.register',
            compact('unidades', 'cursos')
        );
    }

    /**
     * Verificar se o número de processo existe na base de dados
     */
    public function verificarNumeroProcesso(Request $request)
    {
        $numeroProcesso = $request->input('numero_processo');

        if (empty($numeroProcesso)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, digite um número de processo.'
            ]);
        }

        $egresso = Egresso::where(
            'numero_processo',
            $numeroProcesso
        )->first();

        // Processo não encontrado
        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Número de processo não encontrado. Seu cadastro ficará pendente para validação manual.',
                'sugerir_pendente' => true
            ]);
        }

        // Processo já associado a uma conta
        if ($egresso->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este número de processo já está associado a uma conta.'
            ]);
        }

        // Processo válido
        return response()->json([
            'success' => true,
            'message' => '✅ Número de processo verificado! Cadastro automático liberado.',
            'egresso' => [
                'id' => $egresso->id,
                'nome' => $egresso->nome_completo,
                'email' => $egresso->email,
                'curso' => $egresso->curso->nome ?? null,
                'unidade' => $egresso->curso->unidade->sigla ?? null,
            ]
        ]);
    }

    /**
     * Processa o registro do usuário
     */
    public function register(Request $request)
    {
        // ============================================================
        // VALIDAÇÕES GERAIS
        // ============================================================

        $validated = $request->validate([
            'tipo' => 'required|in:admin,egresso,empresa',
            'nome_completo' => 'nullable|string|max:200',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'nullable|string|max:25',
            'password' => 'required|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // ============================================================
        // VALIDAÇÕES ESPECÍFICAS PARA ADMIN
        // ============================================================

        if ($request->tipo === 'admin') {
            $request->validate([
                'nome_completo' => 'required|string|max:200',
                'admin_code' => 'required|string|in:UNILUNDA2024',
                'unidade_id' => 'nullable|exists:unidades_organicas,id',
            ]);
        }

        // ============================================================
        // VALIDAÇÕES ESPECÍFICAS PARA EMPRESA
        // ============================================================

        if ($request->tipo === 'empresa') {
            $request->validate([
                'empresa_nome' => 'required|string|max:200',
                'empresa_nif' => 'nullable|string|max:20|unique:empresas,nif',
                'empresa_sector' => 'required|string|max:100',
                'empresa_website' => 'nullable|url|max:255',
                'empresa_localizacao' => 'nullable|string|max:200',
                'empresa_provincia' => 'nullable|string|max:100',
                'empresa_descricao' => 'nullable|string',
                'empresa_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }

        // ============================================================
        // CONTROLE DO FLUXO DO EGRESSO
        // ============================================================

        $egressoExistente = null;
        $statusValidacao = 'pendente';
        $isNivelOuro = false;

        // ============================================================
        // VALIDAÇÕES ESPECÍFICAS PARA EGRESSO
        // ============================================================

        if ($request->tipo === 'egresso') {

            $request->validate([
                'nome_completo' => 'required|string|max:200',
                'numero_processo' => 'nullable|string',
                'genero' => 'nullable|in:M,F,O',
                'data_nascimento' => 'nullable|date|before:today',
                'curso_id' => 'nullable|exists:cursos,id',
                'unidade_id' => 'nullable|exists:unidades_organicas,id',
                'ano_formatura' => 'nullable|integer|min:1990|max:' . date('Y'),
            ]);

            // ========================================================
            // SE NÃO FORNECEU PROCESSO, CURSO E UNIDADE SÃO OBRIGATÓRIOS
            // ========================================================

            if (empty($request->numero_processo)) {
                $request->validate([
                    'curso_id' => 'required|exists:cursos,id',
                    'unidade_id' => 'required|exists:unidades_organicas,id',
                ]);
            }

            // ========================================================
            // VERIFICAR NÚMERO DE PROCESSO
            // ========================================================

            if (!empty($request->numero_processo)) {

                $egressoExistente = Egresso::where(
                    'numero_processo',
                    $request->numero_processo
                )->first();

                // ====================================================
                // PROCESSO NÃO EXISTE
                // NÍVEL PRATA
                // ====================================================

                if (!$egressoExistente) {

                    $statusValidacao = 'pendente';
                    $isNivelOuro = false;

                } else {

                    // =================================================
                    // PROCESSO JÁ ESTÁ ASSOCIADO
                    // =================================================

                    if ($egressoExistente->user_id) {
                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                'Este número de processo já está associado a uma conta.'
                            );
                    }

                    // =================================================
                    // PROCESSO EXISTE
                    // NÍVEL OURO
                    // =================================================

                    $statusValidacao = 'aprovado';
                    $isNivelOuro = true;
                }

            } else {

                // ====================================================
                // SEM PROCESSO
                // NÍVEL PRATA
                // ====================================================

                $statusValidacao = 'pendente';
                $isNivelOuro = false;
            }
        }

        // ============================================================
        // INICIAR TRANSAÇÃO
        // ============================================================

        try {

            DB::beginTransaction();

            // ========================================================
            // PROCESSAR FOTO (APENAS ADMIN E EGRESSO)
            // ========================================================

            $fotoUrl = null;

            if (
                $request->hasFile('foto') &&
                $request->file('foto')->isValid() &&
                in_array($request->tipo, ['admin', 'egresso'])
            ) {

                $foto = $request->file('foto');

                $tipoPasta = $request->tipo === 'admin'
                    ? 'admins'
                    : 'egressos';

                $nomeFoto =
                    $tipoPasta . '_' .
                    uniqid() . '_' .
                    time() . '.' .
                    $foto->getClientOriginalExtension();

                $foto->move(
                    public_path('uploads/' . $tipoPasta),
                    $nomeFoto
                );

                $fotoUrl =
                    'uploads/' .
                    $tipoPasta .
                    '/' .
                    $nomeFoto;
            }

            // ========================================================
            // PROCESSAR LOGO DA EMPRESA
            // ========================================================

            $logoUrl = null;

            if (
                $request->tipo === 'empresa' &&
                $request->hasFile('empresa_logo') &&
                $request->file('empresa_logo')->isValid()
            ) {

                $logo = $request->file('empresa_logo');

                $nomeLogo =
                    'empresa_' .
                    uniqid() . '_' .
                    time() . '.' .
                    $logo->getClientOriginalExtension();

                $logo->move(
                    public_path('uploads/empresas'),
                    $nomeLogo
                );

                $logoUrl =
                    'uploads/empresas/' .
                    $nomeLogo;
            }

            // ========================================================
            // CRIAR USUÁRIO
            // ========================================================

            // Admin começa ativo
            // Egresso e Empresa começam inativos (aguardam validação)
            $isActive = $request->tipo === 'admin';

            // Determinar o nome a guardar no User
            $userName = $request->tipo === 'empresa'
                ? $request->empresa_nome
                : $request->nome_completo;

            $user = User::create([
                'name' => $userName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->telefone,
                'photo_url' => $request->tipo === 'empresa' ? $logoUrl : $fotoUrl,
                'tipo' => $request->tipo,
                'role' => $request->tipo,
                'is_active' => $isActive,
            ]);

            // ============================================================
            // CRIAR ADMIN
            // ============================================================

            if ($request->tipo === 'admin') {

                Admin::create([
                    'user_id' => $user->id,
                    'nome_completo' => $request->nome_completo,
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'foto_url' => $fotoUrl,
                    'unidade_id' => $request->unidade_id,
                    'cargo' => 'Administrador',
                    'nivel' => 'master',
                    'pode_gerenciar_admins' => true,
                    'pode_gerenciar_egressos' => true,
                    'pode_gerenciar_oportunidades' => true,
                    'pode_gerenciar_eventos' => true,
                    'pode_gerenciar_configuracoes' => true,
                    'pode_visualizar_relatorios' => true,
                    'pode_validar_egressos' => true,
                    'primeiro_acesso' => false,
                    'data_expiracao_senha' => now()->addDays(90),
                    'ativo' => true,
                ]);

                DB::commit();

                return redirect()
                    ->route('login')
                    ->with(
                        'success',
                        '✅ Administrador registado com sucesso! Faça login para aceder ao sistema.'
                    );
            }

            // ============================================================
            // CRIAR EMPRESA
            // ============================================================

            if ($request->tipo === 'empresa') {

                $empresa = Empresa::create([
                    'user_id' => $user->id,
                    'nome' => $request->empresa_nome,
                    'nif' => $request->empresa_nif,
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'website' => $request->empresa_website,
                    'sector' => $request->empresa_sector,
                    'descricao' => $request->empresa_descricao,
                    'localizacao' => $request->empresa_localizacao,
                    'provincia' => $request->empresa_provincia,
                    'logo_url' => $logoUrl,
                    'tipo' => 'potencial',
                    'status_validacao' => 'pendente',
                    'ativo' => true,
                ]);

                // ========================================================
                // NOTIFICAR ADMINS
                // ========================================================

                $this->notificarAdminSobreEmpresaPendente($empresa);

                DB::commit();

                return redirect()
                    ->route('login')
                    ->with([
                        'status' => 'pending_validation',
                        'success' => '⏳ Registo da empresa recebido! A equipa UniLuanda irá validar a sua conta. Receberá um email quando for aprovada.',
                        'prazo' => 'Até 5 dias úteis'
                    ]);
            }

            // ============================================================
            // CRIAR EGRESSO
            // ============================================================

            if ($request->tipo === 'egresso') {

                // ========================================================
                // CASO 1: NÍVEL OURO
                // PROCESSO EXISTE NA BASE
                // ========================================================

                if ($isNivelOuro && $egressoExistente) {

                    $egressoExistente->update([
                        'user_id' => $user->id,
                        'email' => $request->email,
                        'telefone' => $request->telefone,
                        'foto_url' => $fotoUrl,
                        'genero' => $request->genero ?? 'M',
                        'data_nascimento' => $request->data_nascimento,
                        'ano_formatura' => $request->ano_formatura,
                        'status_validacao' => 'aprovado',
                        'verificado' => true,
                        'data_verificacao' => now(),
                        'status' => 'active',
                    ]);

                    // Ativar usuário
                    $user->update([
                        'is_active' => true
                    ]);

                    DB::commit();

                    return redirect()
                        ->route('login')
                        ->with(
                            'success',
                            '✅ Cadastro aprovado! Faça login para aceder ao sistema.'
                        );
                }

                // ========================================================
                // CASO 2: NÍVEL PRATA
                // PROCESSO NÃO EXISTE OU NÃO FOI INFORMADO
                // ========================================================

                $numeroProcessoFinal =
                    'PENDENTE_' .
                    Str::random(8) .
                    '_' .
                    time();

                // ========================================================
                // GARANTIR QUE O IDENTIFICADOR NÃO EXISTE
                // ========================================================

                $count = 1;
                $numeroOriginal = $numeroProcessoFinal;

                while (
                    Egresso::where(
                        'numero_processo',
                        $numeroProcessoFinal
                    )->exists()
                ) {

                    $numeroProcessoFinal =
                        $numeroOriginal . '_' . $count;

                    $count++;
                }

                // ========================================================
                // CRIAR EGRESSO PENDENTE
                // ========================================================

                $novoEgresso = Egresso::create([
                    'user_id' => $user->id,
                    'curso_id' => $request->curso_id,
                    'numero_processo' => $numeroProcessoFinal,
                    'nome_completo' => $request->nome_completo,
                    'genero' => $request->genero ?? 'M',
                    'data_nascimento' => $request->data_nascimento,
                    'email' => $request->email,
                    'telefone' => $request->telefone,
                    'foto_url' => $fotoUrl,
                    'ano_formatura' => $request->ano_formatura,
                    'status_validacao' => 'pendente',
                    'status' => 'inactive',
                    'verificado' => false,
                    'observacoes' =>
                        'Cadastro pendente de validação manual. Nº processo informado: ' .
                        ($request->numero_processo ?? 'Não informado'),
                ]);

                // ========================================================
                // NOTIFICAR ADMIN
                // ========================================================

                $this->notificarAdminSobrePendente($novoEgresso);

                DB::commit();

                return redirect()
                    ->route('login')
                    ->with([
                        'status' => 'pending_validation',
                        'success' => '⏳ Cadastro recebido! Aguarde a validação manual da equipe. Você receberá um email quando sua conta for aprovada.',
                        'prazo' => 'Até 5 dias úteis'
                    ]);
            }

            // ============================================================
            // FINALIZAÇÃO PADRÃO
            // ============================================================

            DB::commit();

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Conta criada com sucesso! Faça login para aceder ao sistema.'
                );

        } catch (\Exception $e) {

            // ========================================================
            // DESFAZER TRANSAÇÃO
            // ========================================================

            DB::rollBack();

            Log::error(
                'Erro no registo:',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'request' => $request->all()
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Erro ao criar conta: ' . $e->getMessage()
                );
        }
    }

    /**
     * Notificar administradores sobre novo cadastro pendente
     * (Egresso)
     */
    private function notificarAdminSobrePendente(Egresso $egresso)
    {
        $admins = User::where('tipo', 'admin')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {

            Log::info(
                '📧 Enviando email para admin sobre novo egresso pendente',
                [
                    'admin' => $admin->email,
                    'egresso' => $egresso->nome_completo,
                    'id' => $egresso->id
                ]
            );

            try {
                Mail::to($admin->email)
                    ->send(new NovoPendenteMail($egresso));

                Log::info('✅ Email enviado para: ' . $admin->email);

            } catch (\Exception $e) {
                Log::error('❌ Erro ao enviar email para admin: ' . $e->getMessage());
            }

            // Notificação no sino
            try {
                $adminPerfil = $admin->admin;

                if ($adminPerfil) {
                    Notificacao::create([
                        'admin_id' => $adminPerfil->id,
                        'tipo' => 'sistema',
                        'titulo' => '🆕 Novo egresso pendente de validação',
                        'mensagem' => $egresso->nome_completo . ' registou-se e aguarda validação manual.',
                        'link' => '/admin/validacao/' . $egresso->id,
                        'lida' => false,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('❌ Erro ao criar notificação para admin: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notificar administradores sobre nova empresa pendente
     */
    private function notificarAdminSobreEmpresaPendente(Empresa $empresa)
    {
        $admins = User::where('tipo', 'admin')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {

            Log::info(
                '📧 Enviando email para admin sobre nova empresa pendente',
                [
                    'admin' => $admin->email,
                    'empresa' => $empresa->nome,
                    'id' => $empresa->id
                ]
            );

            // Notificação no sino
            try {
                $adminPerfil = $admin->admin;

                if ($adminPerfil) {
                    Notificacao::create([
                        'admin_id' => $adminPerfil->id,
                        'tipo' => 'sistema',
                        'titulo' => '🏢 Nova empresa pendente de validação',
                        'mensagem' => $empresa->nome . ' registou-se e aguarda validação.',
                        'link' => '/admin/empresas/' . $empresa->id,
                        'lida' => false,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('❌ Erro ao criar notificação para admin: ' . $e->getMessage());
            }
        }
    }
}