<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    // ============================================================
    // 📩 ENVIAR LIGAÇÃO DE RECUPERAÇÃO
    // ============================================================
    public function enviarLigacao(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // 1. Verifica se existe utilizador com esse email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Por segurança, não revelamos se o email existe
                return response()->json([
                    'success' => true,
                    'message' => 'Se o email existir, receberá uma ligação de recuperação.',
                ]);
            }

            // 2. Verifica se o egresso está aprovado (opcional)
            if ($user->isEgresso()) {
                $egresso = $user->egresso;
                if ($egresso && $egresso->status_validacao !== 'aprovado') {
                    return response()->json([
                        'success' => false,
                        'message' => 'A tua conta ainda não foi aprovada. Contacta a administração.',
                    ], 403);
                }
            }

            // 3. Gera token
            $token = Str::random(64);

            // 4. Guarda na tabela password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email'      => $user->email,
                    'token'      => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // 5. Envia email
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            Mail::send('emails.auth.recuperar-senha', [
                'user'     => $user,
                'resetUrl' => $resetUrl,
                'nome'     => $user->name,
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('🔑 Recuperação de Palavra-passe — Alumni Track');
            });

            Log::info('✅ Email de recuperação enviado para: ' . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'Email enviado com sucesso! Verifica a tua caixa de entrada.',
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email de recuperação: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar o email. Tenta novamente.',
            ], 500);
        }
    }

    // ============================================================
    // 🔑 MOSTRAR FORMULÁRIO DE NOVA PALAVRA-PASSE
    // ============================================================
    public function mostrarFormulario(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // ============================================================
    // ✅ GUARDAR NOVA PALAVRA-PASSE
    // ============================================================
    public function redefinir(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // 1. Verifica token
        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$registro) {
            throw ValidationException::withMessages([
                'email' => 'Ligação inválida ou expirada.',
            ]);
        }

        // 2. Verifica hash
        if (!Hash::check($request->token, $registro->token)) {
            throw ValidationException::withMessages([
                'email' => 'Ligação inválida ou expirada.',
            ]);
        }

        // 3. Verifica expiração (60 minutos)
        if (now()->diffInMinutes($registro->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'email' => 'A ligação expirou. Solicita uma nova.',
            ]);
        }

        // 4. Atualiza a palavra-passe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Utilizador não encontrado.',
            ]);
        }

        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // 5. Remove o token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // 6. Dispara evento
        event(new PasswordReset($user));

        Log::info('✅ Palavra-passe redefinida para: ' . $user->email);

        return redirect()->route('login')
            ->with('status', '✅ Palavra-passe redefinida com sucesso! Já podes entrar.');
    }
}