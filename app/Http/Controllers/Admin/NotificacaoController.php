<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    /**
     * Lista todas as notificações do admin
     */
    public function index()
    {
        $admin = Auth::user()->admin;
        
        if (!$admin) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Perfil de administrador não encontrado.');
        }

        $notificacoes = Notificacao::where('admin_id', $admin->id)
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(20);

        $totalNaoLidas = Notificacao::where('admin_id', $admin->id)
                                    ->where('lida', false)
                                    ->count();

        return view('admin.notificacoes.index', compact('notificacoes', 'totalNaoLidas'));
    }

    /**
     * Marca uma notificação como lida
     */
    public function marcarComoLida($id)
    {
        $admin = Auth::user()->admin;
        
        $notificacao = Notificacao::where('id', $id)
                                  ->where('admin_id', $admin->id)
                                  ->first();

        if (!$notificacao) {
            return response()->json(['success' => false, 'message' => 'Notificação não encontrada.'], 404);
        }

        $notificacao->update(['lida' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function marcarTodasComoLidas()
    {
        $admin = Auth::user()->admin;
        
        Notificacao::where('admin_id', $admin->id)
                   ->where('lida', false)
                   ->update(['lida' => true]);

        return back()->with('success', 'Todas as notificações marcadas como lidas.');
    }

    /**
     * Conta as notificações não lidas (AJAX)
     */
    public function contarNaoLidas()
    {
        $admin = Auth::user()->admin;
        
        if (!$admin) {
            return response()->json(['total' => 0]);
        }

        $total = Notificacao::where('admin_id', $admin->id)
                            ->where('lida', false)
                            ->count();

        return response()->json(['total' => $total]);
    }
}