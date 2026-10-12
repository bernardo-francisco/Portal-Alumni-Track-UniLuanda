<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\Egresso;
use App\Models\Empresa;
use App\Models\User;
use App\Mail\NovoPendenteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerificarEmailController extends Controller
{
    /**
     * ============================================================
     * Verifica o token e ativa a verificação de email
     * ============================================================
     * Rota: GET /verificar-email/{token}
     */
    public function verificar(Request $request, string $token)
    {
        // ========================================================
        // 1. BUSCAR TOKEN VÁLIDO
        // ========================================================
        $registro = EmailVerificationToken::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$registro) {
            Log::warning('🚨 Token de verificação inválido ou expirado', [
                'token' => substr($token, 0, 10) . '...',
                'ip'    => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', '❌ Link de verificação inválido ou expirado. Por favor, faça o registo novamente.');
        }

        // ========================================================
        // 2. PROCESSAR VERIFICAÇÃO
        // ========================================================
        try {
            DB::beginTransaction();

            $payload = $registro->payload ?? [];
            $userId  = $payload['user_id']  ?? null;
            $tipo    = $payload['tipo']     ?? 'egresso';
            $alvoId  = $payload['alvo_id']  ?? null;

            // 2.1 — Marcar o User como verificado
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update([
                        'email_verificado_at' => now(),
                    ]);
                }
            }

            // 2.2 — Marcar o token como usado
            $registro->update(['used_at' => now()]);

            DB::commit();

            Log::info('✅ Email verificado com sucesso', [
                'user_id'  => $userId,
                'tipo'     => $tipo,
                'alvo_id'  => $alvoId,
                'email'    => $registro->email,
                'ip'       => $request->ip(),
            ]);

            // ========================================================
            // 3. NOTIFICAR ADMIN (só AGORA, após verificação)
            // ========================================================
            $this->notificarAdmin($tipo, $alvoId, $registro->email);

            // ========================================================
            // 4. REDIRECIONAR COM SUCESSO
            // ========================================================
            return redirect()
                ->route('login')
                ->with('success', '✅ Email verificado com sucesso! O seu registo foi enviado para a administração. Receberá um email quando for aprovado.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao verificar email', [
                'message' => $e->getMessage(),
                'token'   => substr($token, 0, 10) . '...',
                'ip'      => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Erro ao processar a verificação. Tente novamente mais tarde.');
        }
    }

    /**
     * ============================================================
     * Notifica o admin APÓS a verificação bem-sucedida
     * ============================================================
     */
    private function notificarAdmin(string $tipo, $alvoId, string $email): void
    {
        if (!$alvoId) {
            return;
        }

        // --------------------------------------------------------
        // EGRESSO
        // --------------------------------------------------------
        if ($tipo === 'egresso') {
            $egresso = Egresso::find($alvoId);
            if (!$egresso) return;

            $admins = User::where('tipo', 'admin')
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                // Email
                try {
                    Mail::to($admin->email)->send(new NovoPendenteMail($egresso));
                } catch (\Exception $e) {
                    Log::error('❌ Erro ao enviar email para admin: ' . $e->getMessage());
                }

                // Notificação no sino
                try {
                    $adminPerfil = $admin->admin;
                    if ($adminPerfil) {
                        \App\Models\Notificacao::create([
                            'admin_id' => $adminPerfil->id,
                            'tipo'     => 'sistema',
                            'titulo'   => '🆕 Novo egresso verificado',
                            'mensagem' => $egresso->nome_completo . ' verificou o email e aguarda validação.',
                            'link'     => '/admin/validacao/' . $egresso->id,
                            'lida'     => false,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Erro ao criar notificação: ' . $e->getMessage());
                }
            }
        }

        // --------------------------------------------------------
        // EMPRESA
        // --------------------------------------------------------
        if ($tipo === 'empresa') {
            $empresa = Empresa::find($alvoId);
            if (!$empresa) return;

            $admins = User::where('tipo', 'admin')
                ->where('is_active', true)
                ->get();

            foreach ($admins as $admin) {
                try {
                    $adminPerfil = $admin->admin;
                    if ($adminPerfil) {
                        \App\Models\Notificacao::create([
                            'admin_id' => $adminPerfil->id,
                            'tipo'     => 'sistema',
                            'titulo'   => '🏢 Nova empresa verificada',
                            'mensagem' => $empresa->nome . ' verificou o email e aguarda validação.',
                            'link'     => '/admin/empresas/' . $empresa->id,
                            'lida'     => false,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Erro ao criar notificação: ' . $e->getMessage());
                }
            }
        }
    }
}