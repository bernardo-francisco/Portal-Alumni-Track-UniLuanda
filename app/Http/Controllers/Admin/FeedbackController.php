<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Egresso;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pendente');

        $query = Feedback::with(['egresso.user', 'curso']);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(20);

        $pendentes = Feedback::where('status', 'pendente')->count();
        $aprovados = Feedback::where('status', 'aprovado')->count();
        $rejeitados = Feedback::where('status', 'rejeitado')->count();

        return view('admin.feedbacks.index', compact('feedbacks', 'pendentes', 'aprovados', 'rejeitados', 'status'));
    }

    public function show($id)
    {
        $feedback = Feedback::with(['egresso.user', 'curso'])->findOrFail($id);
        return view('admin.feedbacks.show', compact('feedback'));
    }

    public function aprovar($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => 'aprovado']);

        // 🔔 NOTIFICAÇÃO: Feedback aprovado
        $egresso = Egresso::find($feedback->egresso_id);
        if ($egresso) {
            $egresso->notificar(
                '✅ Feedback aprovado',
                'Seu feedback "' . $feedback->titulo . '" foi aprovado e publicado.',
                'sistema',
                route('egresso.feedbacks.show', $feedback->id)
            );
        }

        return back()->with('success', 'Feedback aprovado e egresso notificado!');
    }

    public function rejeitar($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => 'rejeitado']);

        // 🔔 NOTIFICAÇÃO: Feedback rejeitado
        $egresso = Egresso::find($feedback->egresso_id);
        if ($egresso) {
            $egresso->notificar(
                '❌ Feedback rejeitado',
                'Seu feedback "' . $feedback->titulo . '" foi rejeitado. Entre em contato para mais informações.',
                'sistema',
                null
            );
        }

        return back()->with('success', 'Feedback rejeitado e egresso notificado.');
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        // 🔔 NOTIFICAÇÃO: Feedback removido
        $egresso = Egresso::find($feedback->egresso_id);
        if ($egresso) {
            $egresso->notificar(
                '🗑️ Feedback removido',
                'Seu feedback "' . $feedback->titulo . '" foi removido do sistema.',
                'sistema',
                null
            );
        }

        return back()->with('success', 'Feedback eliminado!');
    }
}