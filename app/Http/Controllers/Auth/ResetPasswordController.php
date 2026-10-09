<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            Log::info('🔄 Tentando redefinir senha para: ' . $request->email);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                    
                    Log::info('✅ Senha redefinida com sucesso para: ' . $user->email);
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                Log::info('✅ Redefinição concluída para: ' . $request->email);
                return redirect()->route('login')->with('status', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
            }

            Log::warning('⚠️ Falha ao redefinir senha: ' . $status);
            return back()->withErrors(['email' => [__($status)]]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao redefinir senha: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erro ao redefinir senha: ' . $e->getMessage()]);
        }
    }
}