<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function index()
    {
        $egresso = Auth::user()->egresso;
        $notificacoes = $egresso->notificacoes()->orderBy('created_at', 'desc')->paginate(20);
        
        return view('egresso.notificacoes.index', compact('notificacoes'));
    }

    public function marcarComoLida($id)
    {
        $egresso = Auth::user()->egresso;
        $notificacao = $egresso->notificacoes()->findOrFail($id);
        $notificacao->update(['lida' => true]);
        
        return response()->json(['success' => true]);
    }

    public function marcarTodasComoLidas()
    {
        $egresso = Auth::user()->egresso;
        $egresso->notificacoes()->where('lida', false)->update(['lida' => true]);
        
        return redirect()->back()->with('success', 'Todas as notificações marcadas como lidas.');
    }

    public function contarNaoLidas()
    {
        $egresso = Auth::user()->egresso;
        $total = $egresso->notificacoes()->where('lida', false)->count();
        
        return response()->json(['total' => $total]);
    }
}