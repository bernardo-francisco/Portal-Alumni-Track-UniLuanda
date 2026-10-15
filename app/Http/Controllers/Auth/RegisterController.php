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
        // ============================================================
        if ($egresso->claimed_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.'
            ]);
        }

        // ============================================================
        // CASO 3 — PROCESSO EXISTE MAS AINDA NÃO FOI REIVINDICADO
        // ============================================================

        $nomeMascarado = $this->mascararNome($egresso->nome_completo);

        return response()->json([
            'success'        => true,
            'reivindicavel'  => true,
            'message'        => '✅ Encontrámos o seu registo! Confirme os dados para continuar.',

            'pre_preenchido' => [
                'egresso_id'      => $egresso->id,
                'nome_mascarado'  => $nomeMascarado,
                'email_mascarado' => $this->mascararEmail($egresso->email),
                'curso'           => $egresso->curso->nome ?? null,
                'unidade'         => $egresso->curso->unidade->sigla ?? null,
                'ano_formatura'   => $egresso->ano_formatura,
                'genero'          => $egresso->genero,
            ],

            'auto_preencher' => [
                'nome_completo'    => $egresso->nome_completo,
                'email'            => $egresso->email,
                'telefone'         => $egresso->telefone,
                'foto_url'         => $egresso->foto_url,
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
                $resultado[] = $parte;
            } else {
                $letra = mb_substr($parte, 0, 1);
                $resto = str_repeat('*', max(1, mb_strlen($parte) - 1));
                $resultado[] = $letra . $resto;
            }
        }

        return implode(' ', $resultado);
    }

    /**
     * ============================================================
     * 🛡️ Mascarar email para confirmação visual
     * ============================================================
     */
    private function mascararEmail(?string $email): string
    {
        if (empty($email)) {
            return '—';
        }

        if (!str_contains($email, '@')) {
            return str_repeat('*', mb_strlen($email));
        }

        [$local, $dominio] = explode('@', $email, 2);
        $tamanhoLocal = mb_strlen($local);

        if ($tamanhoLocal <= 2) {
            $localMascarado = str_repeat('*', $tamanhoLocal);
        } else {
            $localMascarado = mb_substr($local, 0, 2) . str_repeat('*', max(1, $tamanhoLocal - 2));
        }

        return $localMascarado . '@' . $dominio;
    }

    /**
     * ============================================================
     * ✅ FASE 1 — VALIDAÇÕES ANTI-BOT
     * ============================================================
     */
    private function validarAntiBot(Request $request): array
    {
        $erros = [];

        // 1. HONEYPOT
        if (!empty($request->input('website_url'))) {
            Log::warning('🚨 Honeypot detectado (registo)', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'email'      => $request->input('email'),
            ]);
            $erros[] = 'honeypot';
        }

        // 2. TIME-CHECK
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

        if ($loadedAt > $agora + 60) {
            Log::warning('🚨 Timestamp manipulado (registo)', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            $erros[] = 'invalid_timestamp';
        }

        // 3. PADRÕES SUSPEITOS
        $nome = (string) $request->input('nome_completo', '');
        if ($nome !== '' && preg_match('/\d{5,}/', $nome)) {
            $erros[] = 'suspicious_name';
        }

        // 3.3 — EMAIL COM PARTE LOCAL MUITO LONGA (típico de bots)
        $email = (string) $request->input('email', '');
        if (preg_match('/[a-z0-9]{40,}@/i', $email)) {
            Log::warning('🚨 Email suspeito detectado (registo)', [
                'ip'    => $request->ip(),
                'email' => $email,
            ]);
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
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Não foi possível concluir o registo. Verifique os dados e tente novamente.'
                );
        }

        // ============================================================
        // ✅ FASE 3 — DETETAR REIVINDICAÇÃO ANTES DA VALIDAÇÃO DE EMAIL
        // ============================================================

        $egressoParaReivindicar = null;

        if ($request->tipo === 'egresso' && !empty($request->numero_processo)) {
            $egressoParaReivindicar = Egresso::where('numero_processo', $request->numero_processo)->first();
        }

        $emailRule = 'unique:users,email';

        if ($egressoParaReivindicar && $egressoParaReivindicar->user_id) {
            $emailRule = 'unique:users,email,' . $egressoParaReivindicar->user_id;
        }

        // ============================================================
        // VALIDAÇÕES GERAIS
        // ============================================================

        $validated = $request->validate([
            'tipo' => 'required|in:admin,egresso,empresa',
            'nome_completo' => 'nullable|string|max:200',
            'email' => 'required|email|' . $emailRule . '|indisposable',
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

            if (empty($request->numero_processo)) {
                $request->validate([
                    'curso_id' => 'required|exists:cursos,id',
                    'unidade_id' => 'required|exists:unidades_organicas,id',
                ]);
            }

            if (!empty($request->numero_processo)) {

                $egressoExistente = Egresso::where(
                    'numero_processo',
                    $request->numero_processo
                )->first();

                if (!$egressoExistente) {
                    $statusValidacao = 'pendente';
                    $isNivelOuro = false;
                } else {

                    // ========================================================
                    // 🛡️ BLOQUEIO 1 — Já reivindicado
                    // ========================================================
                    if ($egressoExistente->claimed_at !== null) {
                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.'
                            );
                    }

                    // ========================================================
                    // 🛡️ BLOQUEIO 2 — Egresso sem email original
                    // ========================================================
                    $emailOriginal = strtolower(trim((string) $egressoExistente->email));

                    if ($emailOriginal === '') {
                        Log::warning('🚨 Reivindicação de egresso SEM email original', [
                            'ip'              => $request->ip(),
                            'numero_processo' => $request->numero_processo,
                            'email_novo'      => $request->email,
                            'user_agent'      => $request->userAgent(),
                        ]);

                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                '⚠️ Este número de processo não tem email registado. Contacte a administração para reivindicar a conta.'
                            );
                    }

                    // ========================================================
                    // 🛡️ BLOQUEIO 3 — Email introduzido ≠ Email original
                    // ========================================================
                    $emailNovo = strtolower(trim((string) $request->email));

                    if ($emailOriginal !== $emailNovo) {
                        Log::warning('🚨 Tentativa de reivindicação com email DIFERENTE', [
                            'ip'                => $request->ip(),
                            'numero_processo'   => $request->numero_processo,
                            'email_introduzido' => $request->email,
                            'email_original'    => $egressoExistente->email,
                            'user_agent'        => $request->userAgent(),
                        ]);

                        return back()
                            ->withInput()
                            ->with(
                                'error',
                                '❌ O email introduzido não corresponde ao email registado para este número de processo. ' .
                                'Se é o titular, use o email original ou contacte a administração.'
                            );
                    }

                    $statusValidacao = 'aprovado';
                    $isNivelOuro = true;
                }

            } else {
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
            // PROCESSAR FOTO
            // ========================================================

            $fotoUrl = null;

            if (
                $request->hasFile('foto') &&
                $request->file('foto')->isValid() &&
                in_array($request->tipo, ['admin', 'egresso'])
            ) {

                $foto = $request->file('foto');

                $tipoPasta = $request->tipo === 'admin' ? 'admins' : 'egressos';

                $nomeFoto =
                    $tipoPasta . '_' .
                    uniqid() . '_' .
                    time() . '.' .
                    $foto->getClientOriginalExtension();

                $foto->move(public_path('uploads/' . $tipoPasta), $nomeFoto);

                $fotoUrl = 'uploads/' . $tipoPasta . '/' . $nomeFoto;
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

                $logo->move(public_path('uploads/empresas'), $nomeLogo);

                $logoUrl = 'uploads/empresas/' . $nomeLogo;
            }

            // ========================================================
            // CRIAR USUÁRIO
            // ========================================================

            $isActive = $request->tipo === 'admin';

            $userName = $request->tipo === 'empresa'
                ? $request->empresa_nome
                : $request->nome_completo;

            $isReivindicacao = (
                $request->tipo === 'egresso' &&
                $isNivelOuro &&
                $egressoExistente &&
                $egressoExistente->user
            );

            if ($isReivindicacao) {
                $user = $egressoExistente->user;

                $dadosAtualizacao = [
                    'name'                => $userName,
                    'password'            => Hash::make($request->password),
                    'phone'               => $request->telefone,
                    'photo_url'           => $fotoUrl ?: $egressoExistente->foto_url,
                    'is_active'           => false,
                    'email_verified_at'   => null,
                    'email_verificado_at' => null,
                ];

                if ($user->email !== $request->email) {
                    $dadosAtualizacao['email'] = $request->email;
                }

                $user->update($dadosAtualizacao);

            } else {
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

                $token = \App\Models\EmailVerificationToken::gerarToken();

                \App\Models\EmailVerificationToken::where('email', $request->email)
                    ->where('purpose', 'verificar_registo')
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);

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

                DB::commit();

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
                // CASO 1: NÍVEL OURO — REIVINDICAÇÃO
                // ========================================================

                if ($isNivelOuro && $egressoExistente) {

                    $userIdFinal = $user->id;

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

                    // Gerar OTP
                    $codigo = OtpCode::gerarCodigo();

                    OtpCode::where('email', $request->email)
                        ->where('purpose', 'reclamar_conta')
                        ->whereNull('used_at')
                        ->update(['used_at' => now()]);

                    // ✅ DEBUG — ANTES do create
                    Log::info('🔍 [OTP] ANTES do create', [
                        'expires_at_calculado' => now()->addMinutes(30)->format('Y-m-d H:i:s'),
                        'now'                  => now()->format('Y-m-d H:i:s'),
                        'timezone'             => config('app.timezone'),
                    ]);

                    $otpCriado = OtpCode::create([
                        'email'      => $request->email,
                        'code'       => $codigo,
                        'purpose'    => 'reclamar_conta',
                        'payload'    => [
                            'egresso_id' => $egressoExistente->id,
                            'user_id'    => $userIdFinal,
                        ],
                        'expires_at' => now()->addMinutes(30),
                        'ip'         => $request->ip(),
                        'user_agent' => Str::limit((string) $request->userAgent(), 500),
                    ]);

                    // ✅ DEBUG — DEPOIS do create (memória)
                    Log::info('🔍 [OTP] DEPOIS do create (memória)', [
                        'id'         => $otpCriado->id,
                        'code'       => $otpCriado->code,
                        'created_at' => $otpCriado->created_at->format('Y-m-d H:i:s'),
                        'expires_at' => $otpCriado->expires_at->format('Y-m-d H:i:s'),
                        'diff_min'   => $otpCriado->created_at->diffInMinutes($otpCriado->expires_at),
                    ]);

                    DB::commit();

                    // ✅ DEBUG — DEPOIS do commit (BD)
                    $otpDaBD = OtpCode::find($otpCriado->id);

                    Log::info('🔍 [OTP] DEPOIS do commit (BD)', [
                        'id'         => $otpDaBD->id,
                        'created_at' => $otpDaBD->created_at->format('Y-m-d H:i:s'),
                        'expires_at' => $otpDaBD->expires_at->format('Y-m-d H:i:s'),
                        'diff_min'   => $otpDaBD->created_at->diffInMinutes($otpDaBD->expires_at),
                        'used_at'    => $otpDaBD->used_at,
                    ]);

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

                    return redirect()
                        ->route('register.verificar-otp', ['email' => $request->email])
                        ->with(
                            'success',
                            '📧 Enviámos um código de verificação para o seu email. Introduza-o para ativar a conta.'
                        );
                }

                // ========================================================
                // CASO 2: NÍVEL PRATA
                // ========================================================

                $numeroProcessoFinal =
                    'PENDENTE_' .
                    Str::random(8) .
                    '_' .
                    time();

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

                $token = \App\Models\EmailVerificationToken::gerarToken();

                \App\Models\EmailVerificationToken::where('email', $request->email)
                    ->where('purpose', 'verificar_registo')
                    ->whereNull('used_at')
                    ->update(['used_at' => now()]);

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

                DB::commit();

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
     * Notificar administradores sobre novo cadastro pendente (Egresso)
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