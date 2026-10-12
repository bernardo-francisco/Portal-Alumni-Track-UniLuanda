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
use App\Models\OtpCode;
use App\Mail\OtpMail;
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

    $egresso = Egresso::where('numero_processo', $numeroProcesso)->first();

    // ============================================================
    // CASO 1 — PROCESSO NÃO EXISTE NA BD
    // Nível Prata — pendente para validação manual
    // ============================================================
    if (!$egresso) {
        return response()->json([
            'success'          => false,
            'message'          => 'Número de processo não encontrado. Seu cadastro ficará pendente para validação manual.',
            'sugerir_pendente' => true
        ]);
    }

    // ============================================================
    // CASO 2 — PROCESSO EXISTE E JÁ FOI REIVINDICADO
    // O egresso já tem conta ativa
    // ============================================================
    if ($egresso->claimed_at !== null) {
        return response()->json([
            'success' => false,
            'message' => 'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.'
        ]);
    }

    // ============================================================
    // CASO 3 — PROCESSO EXISTE MAS AINDA NÃO FOI REIVINDICADO
    // O egresso pode reivindicar a conta
    // ============================================================

    // Mascarar o nome para segurança (ex: "José M*** S****")
    $nomeMascarado = $this->mascararNome($egresso->nome_completo);

    return response()->json([
        'success'        => true,
        'reivindicavel'  => true,
        'message'        => '✅ Encontrámos o seu registo! Confirme os dados para continuar.',

        // Dados parciais — o suficiente para o egresso se reconhecer
        'pre_preenchido' => [
            'egresso_id'     => $egresso->id,
            'nome_mascarado' => $nomeMascarado,
            'curso'          => $egresso->curso->nome ?? null,
            'unidade'        => $egresso->curso->unidade->sigla ?? null,
            'ano_formatura'  => $egresso->ano_formatura,
            'genero'         => $egresso->genero,
        ],

        // Dados completos para auto-preencher o form
        // (só os que já existem na BD — os outros o egresso preenche)
        'auto_preencher' => [
            'nome_completo'    => $egresso->nome_completo,
            'email'            => $egresso->email,           // pode ser null
            'telefone'         => $egresso->telefone,        // pode ser null
            'foto_url'         => $egresso->foto_url,        // pode ser null
            'genero'           => $egresso->genero,
            'data_nascimento'  => $egresso->data_nascimento?->format('Y-m-d'),
            'curso_id'         => $egresso->curso_id,
            'ano_formatura'    => $egresso->ano_formatura,
        ],
    ]);
}

/**
 * ============================================================
 * Mascarar nome para confirmação visual
 * ============================================================
 * "José Manuel Fernandes" → "José M*** F*******"
 */
private function mascararNome(?string $nome): string
{
    if (empty($nome)) {
        return '—';
    }

    $partes = preg_split('/\s+/', trim($nome));
    $resultado = [];

    foreach ($partes as $i => $parte) {
        if ($i === 0) {
            // Primeiro nome: manter completo
            $resultado[] = $parte;
        } else {
            // Restantes: primeira letra + asteriscos
            $letra = mb_substr($parte, 0, 1);
            $resto = str_repeat('*', max(1, mb_strlen($parte) - 1));
            $resultado[] = $letra . $resto;
        }
    }

    return implode(' ', $resultado);
}

    /**
     * ============================================================
     * ✅ FASE 1 — VALIDAÇÕES ANTI-BOT
     * ============================================================
     * Verifica honeypot, time-check e padrões suspeitos.
     * Retorna um array de erros (vazio = passou).
     */
    private function validarAntiBot(Request $request): array
    {
        $erros = [];

        // -----------------------------------------------------------
        // 1. HONEYPOT — campo invisível preenchido = bot
        // -----------------------------------------------------------
        if (!empty($request->input('website_url'))) {
            Log::warning('🚨 Honeypot detectado (registo)', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email'      => $request->input('email'),
            ]);
            $erros[] = 'honeypot';
        }

        // -----------------------------------------------------------
        // 2. TIME-CHECK — submissão demasiado rápida = bot
        // -----------------------------------------------------------
        $loadedAt = (int) $request->input('form_loaded_at', 0);
        $agora    = time();

        if ($loadedAt > 0 && ($agora - $loadedAt) < 3) {
            Log::warning('🚨 Submissão rápida detectada (registo)', [
                'ip'    => $request->ip(),
                'tempo' => $agora - $loadedAt,
                'email' => $request->input('email'),
            ]);
            $erros[] = 'too_fast';
        }

        // Timestamp no futuro (manipulação) → suspeito
        if ($loadedAt > $agora + 60) {
            Log::warning('🚨 Timestamp manipulado (registo)', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            $erros[] = 'invalid_timestamp';
        }

        // -----------------------------------------------------------
        // 3. PADRÕES SUSPEITOS no nome / email
        // -----------------------------------------------------------

        // Nome com 5+ dígitos seguidos
        $nome = (string) $request->input('nome_completo', '');
        if ($nome !== '' && preg_match('/\d{5,}/', $nome)) {
            $erros[] = 'suspicious_name';
        }

        // Email com 25+ caracteres seguidos (típico de bots)
        $email = (string) $request->input('email', '');
        if (preg_match('/[a-z0-9]{25,}@/i', $email)) {
            $erros[] = 'suspicious_email';
        }

        return $erros;
    }

    /**
     * Processa o registro do usuário
     */
    public function register(Request $request)
    {
        // ============================================================
        // ✅ FASE 1 — VERIFICAÇÕES ANTI-BOT
        // ============================================================

        $errosBot = $this->validarAntiBot($request);

        if (!empty($errosBot)) {
            // Resposta genérica — não revelar qual foi o gatilho
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Não foi possível concluir o registo. Verifique os dados e tente novamente.'
                );
        }

        // ============================================================
        // VALIDAÇÕES GERAIS
        // ============================================================

        $validated = $request->validate([
            'tipo' => 'required|in:admin,egresso,empresa',
            'nome_completo' => 'nullable|string|max:200',
            'email' => 'required|email|unique:users,email|indisposable', // ✅ indisposable
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
                    // PROCESSO EXISTE
                    // NÍVEL OURO — o egresso pode reivindicar
                    // =================================================

                    // ✅ FASE 3 — Só bloqueia se JÁ foi reivindicado
                    // (o admin pode ter criado o user, mas ninguém o reivindicou ainda)
                    if ($egressoExistente->claimed_at !== null) {
                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.'
                            );
                    }

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

                // ✅ FASE 3 — Detetar fluxo de reivindicação
                $isReivindicacao = (
                    $request->tipo === 'egresso' &&
                    $isNivelOuro &&
                    $egressoExistente &&
                    $egressoExistente->user
                );

                if ($isReivindicacao) {
                    // Reivindicação: reutilizar o User existente (criado pelo admin)
                    $user = $egressoExistente->user;

                    // Atualizar os dados do User existente
                    $user->update([
                        'name'              => $userName,
                        'email'             => $request->email,
                        'password'          => Hash::make($request->password),
                        'phone'             => $request->telefone,
                        'photo_url'         => $fotoUrl ?: $egressoExistente->foto_url,
                        'is_active'         => false,             // só ativa após OTP
                        'email_verified_at' => null,
                    ]);

                } else {
                    // Fluxo normal: criar User novo
                    $user = User::create([
                        'name'                    => $userName,
                        'email'                   => $request->email,
                        'password'                => Hash::make($request->password),
                        'phone'                   => $request->telefone,
                        'photo_url'               => $request->tipo === 'empresa' ? $logoUrl : $fotoUrl,
                        'tipo'                    => $request->tipo,
                        'role'                    => $request->tipo,
                        'is_active'               => $isActive,
                        'registration_ip'         => $request->ip(),
                        'registration_user_agent' => Str::limit((string) $request->userAgent(), 500),
                    ]);
                }

                    // ============================================================
                    // CRIAR ADMIN
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
                    // ✅ FASE 2 — GERAR TOKEN DE VERIFICAÇÃO DE EMAIL
                    // ========================================================
                    $token = \App\Models\EmailVerificationToken::gerarToken();

                    // Invalidar tokens anteriores deste email
                    \App\Models\EmailVerificationToken::where('email', $request->email)
                        ->where('purpose', 'verificar_registo')
                        ->whereNull('used_at')
                        ->update(['used_at' => now()]);

                    // Criar novo token (válido 24 horas)
                    \App\Models\EmailVerificationToken::create([
                        'email'      => $request->email,
                        'token'      => $token,
                        'purpose'    => 'verificar_registo',
                        'payload'    => [
                            'user_id' => $user->id,
                            'tipo'    => 'empresa',
                            'alvo_id' => $empresa->id,
                        ],
                        'expires_at' => now()->addHours(24),
                        'ip'         => $request->ip(),
                        'user_agent' => Str::limit((string) $request->userAgent(), 500),
                    ]);

                    // ========================================================
                    // COMMIT PRIMEIRO
                    // ========================================================
                    DB::commit();

                    // ========================================================
                    // ✅ FASE 2 — ENVIAR EMAIL DE VERIFICAÇÃO (fora da transação)
                    // ========================================================
                    try {
                        $link = route('verificar.email', ['token' => $token]);

                        Mail::to($request->email)->send(
                            new \App\Mail\VerificarEmailMail(
                                $request->empresa_nome,
                                $link,
                                'empresa'
                            )
                        );

                        Log::info('✅ Email de verificação enviado', [
                            'email' => $request->email,
                            'tipo'  => 'empresa',
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Erro ao enviar email de verificação: ' . $e->getMessage());
                    }

                    // ========================================================
                    // ✅ FASE 2 — NÃO notificar admin ainda
                    // ========================================================

                    return redirect()
                        ->route('login')
                        ->with([
                            'status'  => 'pending_email_verification',
                            'success' => '📧 Enviámos um email de verificação para ' . $request->email . '. Confirme o seu email para concluir o registo.',
                            'prazo'   => 'Verifique também a pasta de spam',
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

    // ========================================================
    // FASE 3 — REIVINDICAÇÃO DE CONTA (fluxo com OTP)
    // ========================================================
    // O User já foi atualizado no bloco "CRIAR USUÁRIO" acima.
    // ========================================================

    $userIdFinal = $user->id;

    // Atualizar o Egresso existente com os dados do form
    $egressoExistente->update([
        'user_id'            => $userIdFinal,
        'email'              => $request->email,
        'telefone'           => $request->telefone,
        'foto_url'           => $fotoUrl ?: $egressoExistente->foto_url,
        'genero'             => $request->genero ?? $egressoExistente->genero ?? 'M',
        'data_nascimento'    => $request->data_nascimento ?? $egressoExistente->data_nascimento,
        'ano_formatura'      => $request->ano_formatura ?? $egressoExistente->ano_formatura,
        'curso_id'           => $request->curso_id ?? $egressoExistente->curso_id,
        'claimed_at'         => now(),
        'claimed_ip'         => $request->ip(),
        'claimed_user_agent' => Str::limit((string) $request->userAgent(), 500),
        'status_validacao'   => 'pendente',
        'status'             => 'inactive',
        'verificado'         => false,
    ]);

    // ========================================================
    // GERAR OTP
    // ========================================================
    $codigo = OtpCode::gerarCodigo();

    OtpCode::where('email', $request->email)
        ->where('purpose', 'reclamar_conta')
        ->whereNull('used_at')
        ->update(['used_at' => now()]);

    OtpCode::create([
        'email'      => $request->email,
        'code'       => $codigo,
        'purpose'    => 'reclamar_conta',
        'payload'    => [
            'egresso_id' => $egressoExistente->id,
            'user_id'    => $userIdFinal,
        ],
        'expires_at' => now()->addMinutes(30),   // ✅ 30 minutos
        'ip'         => $request->ip(),
        'user_agent' => Str::limit((string) $request->userAgent(), 500),
    ]);

    // ========================================================
    // COMMIT PRIMEIRO
    // ========================================================
    DB::commit();

    // ========================================================
    // ENVIAR EMAIL DEPOIS DO COMMIT (fora da transação)
    // ========================================================
    try {
        Mail::to($request->email)->send(
            new OtpMail($codigo, $request->nome_completo)
        );

        Log::info('✅ Email OTP enviado', [
            'email' => $request->email,
            'code'  => $codigo,
        ]);
    } catch (\Exception $e) {
        Log::error('❌ Erro ao enviar OTP por email: ' . $e->getMessage());
    }

    // ========================================================
    // REDIRECIONAR
    // ========================================================
    return redirect()
        ->route('register.verificar-otp', ['email' => $request->email])
        ->with(
            'success',
            '📧 Enviámos um código de verificação para o seu email. Introduza-o para ativar a conta.'
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

            // Loop para garantir ID único
            $count = 1;
            $numeroOriginal = $numeroProcessoFinal;

            while (Egresso::where('numero_processo', $numeroProcessoFinal)->exists()) {
                $numeroProcessoFinal = $numeroOriginal . '_' . $count;
                $count++;
            }

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
            // ✅ FASE 2 — GERAR TOKEN DE VERIFICAÇÃO DE EMAIL
            // ========================================================
            $token = \App\Models\EmailVerificationToken::gerarToken();

            // Invalidar tokens anteriores deste email
            \App\Models\EmailVerificationToken::where('email', $request->email)
                ->where('purpose', 'verificar_registo')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            // Criar novo token (válido 24 horas)
            \App\Models\EmailVerificationToken::create([
                'email'      => $request->email,
                'token'      => $token,
                'purpose'    => 'verificar_registo',
                'payload'    => [
                    'user_id'  => $user->id,
                    'tipo'     => 'egresso',
                    'alvo_id'  => $novoEgresso->id,
                ],
                'expires_at' => now()->addHours(24),
                'ip'         => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500),
            ]);

            // ========================================================
            // COMMIT PRIMEIRO
            // ========================================================
            DB::commit();

            // ========================================================
            // ✅ FASE 2 — ENVIAR EMAIL DE VERIFICAÇÃO (fora da transação)
            // ========================================================
            try {
                $link = route('verificar.email', ['token' => $token]);

                Mail::to($request->email)->send(
                    new \App\Mail\VerificarEmailMail(
                        $request->nome_completo,
                        $link,
                        'egresso'
                    )
                );

                Log::info('✅ Email de verificação enviado', [
                    'email' => $request->email,
                    'tipo'  => 'egresso',
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Erro ao enviar email de verificação: ' . $e->getMessage());
            }

            // ========================================================
            // ✅ FASE 2 — NÃO notificar admin ainda (só após verificação)
            // ========================================================

            return redirect()
                ->route('login')
                ->with([
                    'status'  => 'pending_email_verification',
                    'success' => '📧 Enviámos um email de verificação para ' . $request->email . '. Confirme o seu email para concluir o registo.',
                    'prazo'   => 'Verifique também a pasta de spam',
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