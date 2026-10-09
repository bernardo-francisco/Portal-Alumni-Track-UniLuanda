<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    private function empresaId(): int
    {
        return Auth::user()->empresa->id;
    }

    public function marcarLida($id)
    {
        $notif = Notificacao::paraEmpresa($this->empresaId())->findOrFail($id);
        $notif->marcarComoLida();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function marcarTodas()
    {
        Notificacao::paraEmpresa($this->empresaId())
            ->where('lida', false)
            ->update(['lida' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}