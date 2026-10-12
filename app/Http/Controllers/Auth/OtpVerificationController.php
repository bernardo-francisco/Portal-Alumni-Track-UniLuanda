<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpVerificationController extends Controller
{
    /**
     * ============================================================
     * Mostra o ecrã de verificação de OTP
     * ============================================================
     * Rota: GET /registar/verificar-otp?email=xxx
     */
    public function showForm(Request $request)
    {
        $email = $request->query('email');

        // Validação básica
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()
                ->route('register')
                ->with('error', 'Sessão inválida. Por favor, reinicie o registo.');
        }

        // Verificar se há OTP pendente para este email
        $otp = OtpCode::paraEmail($email, 'reclamar_conta')
            ->validos()
            ->latest()
            ->first();

        if (!$otp) {
            return redirect()
                ->route('register')
                ->with('error', 'Não há código de verificação ativo para este email. Por favor, reinicie o registo.');
        }

        return view('auth.verify-otp', [
            'email'      => $email,
            'expira_em'  => $otp->expires_at,
        ]);
    }

    /**
     * ============================================================
     * Valida o OTP e ativa a conta
     * ============================================================
     * Rota: POST /registar/verificar-otp
     */
    public function verify(Request $request)
    {
        // ============================================================
        // VALIDAÇÃO
        // ============================================================
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $email = $request->input('email');
        $code  = trim($request->input('code'));

        // ============================================================
        // BUSCAR OTP VÁLIDO
        // ============================================================
        $otp = OtpCode::paraEmail($email, 'reclamar_conta')
            ->validos()
            ->where('code', $code)
            ->latest()
            ->first();

        if (!$otp) {
            Log::warning('🚨 OTP inválido', [
                'email' => $email,
                'code'  => $code,
                'ip'    => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->with('error', '❌ Código inválido ou expirado. Verifique e tente novamente.');
        }

        // ============================================================
        // ATIVAR A CONTA
        // ============================================================
        try {
            DB::beginTransaction();

            $payload    = $otp->payload ?? [];
            $egressoId  = $payload['egresso_id'] ?? null;
            $userId     = $payload['user_id']    ?? null;

            if (!$egressoId || !$userId) {
                throw new \Exception('Payload do OTP inválido.');
            }

            $egresso = Egresso::find($egressoId);
            $user    = User::find($userId);

            if (!$egresso || !$user) {
                throw new \Exception('Egresso ou User não encontrado.');
            }

            // 1. Ativar o User
            $user->update([
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);

            // 2. Atualizar o Egresso
            $egresso->update([
                'status_validacao' => 'aprovado',
                'status'           => 'active',
                'verificado'       => true,
                'data_verificacao' => now(),

                // Marcar como reivindicado agora
                'claimed_at'         => now(),
                'claimed_ip'         => $request->ip(),
                'claimed_user_agent' => Str::limit((string) $request->userAgent(), 500),
            ]);

            // 3. Marcar o OTP como usado
            $otp->marcarComoUsado();

            // 4. Invalidar todos os OTPs antigos deste email
            OtpCode::where('email', $email)
                ->where('purpose', 'reclamar_conta')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            DB::commit();

            Log::info('✅ Conta ativada via OTP', [
                'user_id'    => $user->id,
                'egresso_id' => $egresso->id,
                'email'      => $email,
                'ip'         => $request->ip(),
            ]);

            // ========================================================
            // REDIRECIONAR PARA LOGIN COM SUCESSO
            // ========================================================
            return redirect()
                ->route('login')
                ->with('success', '✅ Conta ativada com sucesso! Faça login com o seu email e senha.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro ao ativar conta via OTP', [
                'message' => $e->getMessage(),
                'email'   => $email,
                'ip'      => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao ativar a conta. Por favor, tente novamente mais tarde.');
        }
    }
}