<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StatusCandidaturaMail;
use App\Models\Candidatura;
use App\Models\Oportunidade;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CandidaturaController extends Controller
{
    // ============================================================
    // 📋 LISTA DE CANDIDATURAS
    // ============================================================
    public function index(Request $request)
    {
        $status = $request->get('status', 'todos');
        $oportunidadeId = $request->get('oportunidade_id');

        $query = Candidatura::with(['egresso', 'oportunidade'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        if ($oportunidadeId) {
            $query->where('oportunidade_id', $oportunidadeId);
        }

        $candidaturas = $query->paginate(15);

        $stats = [
            'pendente'   => Candidatura::where('status', 'pendente')->count(),
            'em_analise' => Candidatura::where('status', 'em_analise')->count(),
            'entrevista' => Candidatura::where('status', 'entrevista')->count(),
            'aprovado'   => Candidatura::where('status', 'aprovado')->count(),
            'rejeitado'  => Candidatura::where('status', 'rejeitado')->count(),
            'total'      => Candidatura::count(),
        ];

        $oportunidades = Oportunidade::orderBy('titulo')->get();

        return view('admin.candidaturas.index', compact('candidaturas', 'stats', 'status', 'oportunidades'));
    }

    // ============================================================
    // 👁️ VER DETALHES
    // ============================================================
    public function show($id)
    {
        $candidatura = Candidatura::with(['egresso', 'oportunidade'])
            ->findOrFail($id);

        return view('admin.candidaturas.show', compact('candidatura'));
    }

    // ============================================================
    // 🔄 MUDAR STATUS
    // ============================================================
    public function mudarStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pendente,em_analise,entrevista,aprovado,rejeitado',
        ]);

        // Carregar candidatura com relações necessárias
        $candidatura = Candidatura::with(['oportunidade', 'egresso.user'])->findOrFail($id);

        // ✅ Garantir que temos o egresso correto (dono da candidatura)
        $egresso = $candidatura->egresso;
        $destinatario = $egresso->user ?? null;

        if (!$egresso || !$destinatario) {
            Log::warning("Candidatura ID {$id} sem egresso ou user associado.");
            return back()->with('error', 'Egresso não encontrado para esta candidatura.');
        }

        try {
            DB::beginTransaction();

            $candidatura->update([
                'status'      => $request->status,
                'avaliado_em' => now(),
            ]);

            DB::commit();

            // ✅ Notificações no sino — passa o egresso_id EXPLÍCITO para evitar confusão
            switch ($request->status) {
                case 'aprovado':
                    NotificacaoService::candidaturaAprovada($candidatura, $egresso->id);
                    break;
                case 'rejeitado':
                    NotificacaoService::candidaturaRejeitada($candidatura, $egresso->id);
                    break;
                case 'entrevista':
                    NotificacaoService::entrevistaMarcada($candidatura, $egresso->id);
                    break;
            }

            // ✅ Email de mudança de status — APENAS para o dono da candidatura
            try {
                if ($destinatario->email) {
                    Mail::to($destinatario->email)
                        ->send(new StatusCandidaturaMail($candidatura, $request->status));

                    Log::info('✅ Email status candidatura enviado: ' . $destinatario->email . ' — ' . $request->status);
                }
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao enviar email de status: ' . $e->getMessage());
            }

            return back()->with('success', 'Status atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao mudar status: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atualizar status.');
        }
    }

    // ============================================================
    // 📅 MARCAR ENTREVISTA
    // ============================================================
    public function marcarEntrevista(Request $request, $id)
    {
        $request->validate([
            'data_entrevista'  => 'required|date|after:now',
            'local_entrevista' => 'required|string|max:255',
        ]);

        $candidatura = Candidatura::with(['oportunidade', 'egresso.user'])->findOrFail($id);

        // ✅ Garantir que temos o egresso correto
        $egresso = $candidatura->egresso;
        $destinatario = $egresso->user ?? null;

        if (!$egresso || !$destinatario) {
            Log::warning("Candidatura ID {$id} sem egresso ou user associado.");
            return back()->with('error', 'Egresso não encontrado para esta candidatura.');
        }

        try {
            DB::beginTransaction();

            $candidatura->update([
                'status'           => 'entrevista',
                'data_entrevista'  => $request->data_entrevista,
                'local_entrevista' => $request->local_entrevista,
                'avaliado_em'      => now(),
            ]);

            DB::commit();

            // ✅ Notificação no sino — passa o egresso_id EXPLÍCITO
            NotificacaoService::entrevistaMarcada($candidatura, $egresso->id);

            // ✅ Email de entrevista — APENAS para o dono
            try {
                if ($destinatario->email) {
                    Mail::to($destinatario->email)
                        ->send(new StatusCandidaturaMail($candidatura, 'entrevista'));

                    Log::info('✅ Email entrevista enviado: ' . $destinatario->email);
                }
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao enviar email de entrevista: ' . $e->getMessage());
            }

            return back()->with('success', 'Entrevista marcada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao marcar entrevista: ' . $e->getMessage());
            return back()->with('error', 'Erro ao marcar entrevista.');
        }
    }

    // ============================================================
    // ❌ REJEITAR CANDIDATURA
    // ============================================================
    public function rejeitar(Request $request, $id)
    {
        $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
        ]);

        $candidatura = Candidatura::with(['oportunidade', 'egresso.user'])->findOrFail($id);

        // ✅ Garantir que temos o egresso correto
        $egresso = $candidatura->egresso;
        $destinatario = $egresso->user ?? null;

        if (!$egresso || !$destinatario) {
            Log::warning("Candidatura ID {$id} sem egresso ou user associado.");
            return back()->with('error', 'Egresso não encontrado para esta candidatura.');
        }

        try {
            DB::beginTransaction();

            $candidatura->update([
                'status'          => 'rejeitado',
                'motivo_rejeicao' => $request->motivo_rejeicao,
                'avaliado_em'     => now(),
            ]);

            DB::commit();

            // ✅ Notificação no sino — passa o egresso_id EXPLÍCITO
            NotificacaoService::candidaturaRejeitada($candidatura, $egresso->id);

            // ✅ Email de rejeição — APENAS para o dono
            try {
                if ($destinatario->email) {
                    Mail::to($destinatario->email)
                        ->send(new StatusCandidaturaMail($candidatura, 'rejeitado'));

                    Log::info('✅ Email rejeição enviado: ' . $destinatario->email);
                }
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao enviar email de rejeição: ' . $e->getMessage());
            }

            return back()->with('success', 'Candidatura rejeitada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao rejeitar candidatura: ' . $e->getMessage());
            return back()->with('error', 'Erro ao rejeitar candidatura.');
        }
    }
}