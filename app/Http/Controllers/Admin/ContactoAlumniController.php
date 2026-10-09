<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactoAlumni;
use App\Mail\RespostaContactoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactoAlumniController extends Controller
{
    /**
     * Listar mensagens
     */
    public function index(Request $request)
    {
        $query = ContactoAlumni::query();

        if ($request->filled('status') && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('assunto', 'LIKE', "%{$search}%");
            });
        }

        $contactos = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => ContactoAlumni::count(),
            'novos' => ContactoAlumni::where('status', 'novo')->count(),
            'respondidos' => ContactoAlumni::where('status', 'respondido')->count(),
            'arquivados' => ContactoAlumni::where('status', 'arquivado')->count(),
        ];

        return view('admin.contactos.index', compact('contactos', 'stats'));
    }

    /**
     * Mostrar mensagem
     */
    public function show(ContactoAlumni $contacto)
    {
        if ($contacto->status === 'novo') {
            $contacto->update(['status' => 'lido']);
        }

        return view('admin.contactos.show', compact('contacto'));
    }

    /**
     * ✅ RESPONDER (COM ENVIO DE EMAIL REAL)
     */
    public function responder(Request $request, ContactoAlumni $contacto)
    {
        $validated = $request->validate([
            'resposta' => 'required|string|max:2000',
        ], [
            'resposta.required' => 'Por favor, escreva uma resposta.',
            'resposta.max' => 'A resposta não pode ultrapassar 2000 caracteres.',
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. ENVIAR EMAIL DE RESPOSTA
            |--------------------------------------------------------------------------
            */
            Mail::to($contacto->email)
                ->send(new RespostaContactoMail($contacto, $validated['resposta']));


            /*
            |--------------------------------------------------------------------------
            | 2. GUARDAR A RESPOSTA NA BASE DE DADOS
            |--------------------------------------------------------------------------
            */
            $contacto->update([
                'status' => 'respondido',
                // Se tiveres a coluna 'resposta' na tabela:
                // 'resposta' => $validated['resposta'],
                // 'respondido_em' => now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 3. REGISTAR NO LOG
            |--------------------------------------------------------------------------
            */
            Log::info('Resposta enviada por email', [
                'contacto_id' => $contacto->id,
                'email' => $contacto->email,
                'assunto' => $contacto->assunto,
            ]);


            return back()->with('success', '✅ Resposta enviada por email com sucesso!');

        } catch (\Throwable $e) {

            Log::error('Erro ao enviar resposta por email', [
                'contacto_id' => $contacto->id,
                'email' => $contacto->email,
                'erro' => $e->getMessage(),
            ]);

            return back()->with('error', '❌ Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar mensagem
     */
    public function destroy(ContactoAlumni $contacto)
    {
        try {
            $contacto->delete();
            return redirect()->route('admin.contactos.index')
                ->with('success', 'Mensagem eliminada com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Erro ao eliminar contacto: ' . $e->getMessage());
            return back()->with('error', 'Erro ao eliminar mensagem.');
        }
    }
}