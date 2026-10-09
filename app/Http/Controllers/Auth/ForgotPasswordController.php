<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    /**
     * Mostrar o formulário de solicitação de redefinição de senha
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Enviar o link de redefinição de senha
     */
    public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    try {
        Log::info('🔐 Tentando enviar recuperação para: ' . $request->email);
        
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('✅ Email enviado com sucesso para: ' . $request->email);
            
            // ✅ Retorno JSON para o modal
            return response()->json([
                'success' => true,
                'message' => 'Link de recuperação enviado para ' . $request->email
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao enviar link de recuperação. Tente novamente.'
        ], 400);

    } catch (\Exception $e) {
        Log::error('❌ Erro: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erro interno: ' . $e->getMessage()
        ], 500);
    }
}
}