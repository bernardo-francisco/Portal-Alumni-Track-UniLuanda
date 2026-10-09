<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Curso;
use App\Models\Admin;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Lista os feedbacks do egresso autenticado.
     */
    public function index()
    {
        $egresso = Auth::user()->egresso;

        $feedbacks = Feedback::where('egresso_id', $egresso->id)
            ->with(['curso'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('egresso.feedbacks.index', compact('feedbacks'));
    }

    /**
     * Formulário de criação de feedback.
     */
    public function create()
    {
        $cursos = Curso::orderBy('nome')->get();

        return view('egresso.feedbacks.create', compact('cursos'));
    }

    /**
     * Guardar novo feedback e notificar admin.
     */
    public function store(Request $request)
    {
        $egresso = Auth::user()->egresso;

        $validated = $request->validate([
            'curso_id' => 'nullable|exists:cursos,id',
            'titulo'   => 'required|string|max:255',
            'mensagem' => 'required|string|max:2000',
            'nota'     => 'nullable|integer|min:1|max:5',
        ]);

        $feedback = Feedback::create([
            'egresso_id' => $egresso->id,
            'curso_id'   => $validated['curso_id'] ?? null,
            'titulo'     => $validated['titulo'],
            'mensagem'   => $validated['mensagem'],
            'nota'       => $validated['nota'] ?? null,
            'status'     => 'pendente',
        ]);

        // 🔔 Notificar todos os administradores
        try {
            $admins = Admin::all();
            foreach ($admins as $adminAccount) {
                Notificacao::create([
                    'admin_id' => $adminAccount->id,
                    'tipo'     => 'feedback',
                    'titulo'   => '💬 Novo feedback recebido',
                    'mensagem' => $egresso->nome_completo . ' enviou o feedback "' . $feedback->titulo . '".',
                    'link'     => route('admin.feedbacks.show', $feedback->id),
                    'lida'     => false,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Erro ao notificar admins sobre novo feedback: ' . $e->getMessage()
            );
        }

        return redirect()->route('egresso.feedbacks.index')
            ->with('success', 'Feedback enviado com sucesso! Aguarde a aprovação.');
    }

    /**
     * Mostrar um feedback específico.
     */
    public function show($id)
    {
        $egresso = Auth::user()->egresso;

        $feedback = Feedback::where('egresso_id', $egresso->id)
            ->with(['curso'])
            ->findOrFail($id);

        return view('egresso.feedbacks.show', compact('feedback'));
    }
}