<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamada;
use App\Models\Egresso;
use App\Models\Mensagem;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoCallController extends Controller
{
    /**
     * ============================================================
     * INICIAR CHAMADA (Admin → Egresso)
     * ============================================================
     */
    public function iniciar(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        // O admin pode ter um registo de egresso associado
        $adminEgresso = $user->egresso;

        if (!$adminEgresso) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de admin não configurado.',
            ], 403);
        }

        $request->validate([
            'target_user_id' => 'required|exists:egressos,id',
            'type'           => 'required|in:video,audio',
        ]);

        // Limpar chamadas antigas (> 2 minutos)
        Chamada::whereIn('status', ['pendente', 'aceite'])
            ->where('created_at', '<', now()->subMinutes(2))
            ->update([
                'status'       => 'perdida',
                'terminada_em' => now(),
            ]);

        if ((int) $request->target_user_id === (int) $adminEgresso->id) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível iniciar uma chamada para si próprio.',
            ], 422);
        }

        // Verificar chamada ativa
        $chamadaExistente = Chamada::where(function ($q) use ($adminEgresso, $request) {
            $q->where(function ($q2) use ($adminEgresso, $request) {
                $q2->where('chamador_id', $adminEgresso->id)
                   ->where('recetor_id', $request->target_user_id);
            })
            ->orWhere(function ($q2) use ($adminEgresso, $request) {
                $q2->where('chamador_id', $request->target_user_id)
                   ->where('recetor_id', $adminEgresso->id);
            });
        })
        ->whereIn('status', ['pendente', 'aceite'])
        ->latest()
        ->first();

        if ($chamadaExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Já existe uma chamada ativa com este utilizador.',
            ], 409);
        }

        $roomId = 'room-' . Str::random(16) . '-' . time();

        try {
            Mensagem::create([
                'remetente_id'    => $adminEgresso->id,
                'destinatario_id' => $request->target_user_id,
                'mensagem'        => $request->type === 'video'
                    ? 'Chamada de vídeo'
                    : 'Chamada de voz',
                'tipo'            => 'chamada',
                'lida'            => true,
            ]);

            $chamada = Chamada::create([
                'room_id'     => $roomId,
                'chamador_id' => $adminEgresso->id,
                'recetor_id'  => $request->target_user_id,
                'tipo'        => $request->type,
                'status'      => 'pendente',
            ]);

            try {
                NotificacaoService::chamadaRecebida(
                    $adminEgresso,
                    $request->target_user_id,
                    $request->type,
                    $roomId
                );
            } catch (\Exception $e) {
                Log::warning('Erro ao criar notificação: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'room_id' => $roomId,
                'chamada' => $chamada,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao iniciar chamada (admin): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar chamada.',
            ], 500);
        }
    }

    /**
     * ============================================================
     * VERIFICAR CHAMADAS PENDENTES (polling)
     * ============================================================
     */
    public function verificar(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['chamada' => null]);
        }

        $adminEgresso = $user->egresso;

        if (!$adminEgresso) {
            return response()->json(['chamada' => null]);
        }

        $chamada = Chamada::where('recetor_id', $adminEgresso->id)
            ->where('status', 'pendente')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->latest()
            ->first();

        if (!$chamada) {
            return response()->json(['chamada' => null]);
        }

        if ($chamada->created_at->diffInSeconds(now()) > 60) {
            $chamada->update([
                'status'       => 'perdida',
                'terminada_em' => now(),
            ]);
            return response()->json(['chamada' => null]);
        }

        $chamador = Egresso::select('id', 'nome_completo', 'foto_url')
            ->find($chamada->chamador_id);

        return response()->json([
            'chamada' => [
                'id'          => $chamada->id,
                'room_id'     => $chamada->room_id,
                'chamador_id' => $chamada->chamador_id,
                'recetor_id'  => $chamada->recetor_id,
                'tipo'        => $chamada->tipo,
                'status'      => $chamada->status,
                'chamador'    => $chamador,
            ],
        ]);
    }

    /**
     * ============================================================
     * ACEITAR CHAMADA
     * ============================================================
     */
    public function aceitar(Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)
            ->where('recetor_id', $adminEgresso->id)
            ->where('status', 'pendente')
            ->first();

        if (!$chamada) {
            return response()->json([
                'success' => false,
                'message' => 'Chamada não encontrada.',
            ], 404);
        }

        $chamada->update([
            'status'    => 'aceite',
            'aceite_em' => now(),
        ]);

        return response()->json([
            'success' => true,
            'chamada' => $chamada,
        ]);
    }

    /**
     * ============================================================
     * RECUSAR CHAMADA
     * ============================================================
     */
    public function recusar(Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return response()->json(['success' => false], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)
            ->where('recetor_id', $adminEgresso->id)
            ->where('status', 'pendente')
            ->first();

        if (!$chamada) {
            return response()->json(['success' => false], 404);
        }

        $chamada->update([
            'status'       => 'recusada',
            'terminada_em' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * ============================================================
     * SINAL (PeerJS não usa — mantido para compatibilidade)
     * ============================================================
     */
    public function sinal(Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return response()->json(['success' => false], 403);
        }

        $request->validate([
            'room_id' => 'required|string',
            'tipo'    => 'required|in:oferta,resposta,ice_chamador,ice_recetor',
            'dados'   => 'required|array',
        ]);

        $chamada = Chamada::where('room_id', $request->room_id)->first();

        if (!$chamada) {
            return response()->json(['success' => false], 404);
        }

        $campo = match ($request->tipo) {
            'oferta'       => 'sinal_oferta',
            'resposta'     => 'sinal_resposta',
            'ice_chamador' => 'ice_candidates_chamador',
            'ice_recetor'  => 'ice_candidates_recetor',
        };

        $chamada->update([$campo => $request->dados]);

        return response()->json(['success' => true]);
    }

    /**
     * ============================================================
     * ESTADO DA CHAMADA (polling)
     * ============================================================
     */
    public function estado(Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)->first();

        if (!$chamada) {
            return response()->json([
                'success' => false,
                'message' => 'Chamada não encontrada.',
            ], 404);
        }

        $isChamador = (int) $chamada->chamador_id === (int) $adminEgresso->id;

     return response()->json([
    'success'          => true,
    'status'           => $chamada->status,
    'is_chamador'      => $isChamador,
    'chamador_id'      => (int) $chamada->chamador_id,
    'recetor_id'       => (int) $chamada->recetor_id,
    'tipo'             => $chamada->tipo,

    // ✅ ADICIONAR ESTAS 2 LINHAS
    'peer_id_chamador' => $chamada->peer_id_chamador,
    'peer_id_recetor'  => $chamada->peer_id_recetor,

    'sinal_oferta'     => $chamada->sinal_oferta,
    'sinal_resposta'   => $chamada->sinal_resposta,
    'ice_chamador'     => $chamada->ice_candidates_chamador ?? [],
    'ice_recetor'      => $chamada->ice_candidates_recetor  ?? [],
]);
    }

    /**
     * ============================================================
     * ENCERRAR CHAMADA
     * ============================================================
     */
    public function encerrar(Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return response()->json(['success' => false], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)->first();

        if ($chamada) {
            $chamada->update([
                'status'       => 'terminada',
                'terminada_em' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * ============================================================
     * SALA DE CHAMADA
     * ============================================================
     */
    public function sala($roomId, Request $request)
    {
        $user = Auth::user();
        $adminEgresso = $user?->egresso;

        if (!$adminEgresso) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Perfil de admin não configurado.');
        }

        $chamada = Chamada::where('room_id', $roomId)->first();

        if (!$chamada) {
            return redirect()->route('admin.mensagens.index')
                ->with('error', 'Chamada não encontrada.');
        }

        $isChamador = (int) $chamada->chamador_id === (int) $adminEgresso->id;

        if (!$isChamador && (int) $chamada->recetor_id !== (int) $adminEgresso->id) {
            abort(403, 'Não autorizado.');
        }

        $type = $chamada->tipo;
        $targetUserId = $isChamador
            ? $chamada->recetor_id
            : $chamada->chamador_id;

        $role = $isChamador ? 'chamador' : 'recetor';

        return view('admin.video-call.sala', compact(
            'roomId',
            'targetUserId',
            'adminEgresso',
            'type',
            'chamada',
            'isChamador',
            'role'
        ));
    }

   
 /**
 * ============================================================
 * REGISTAR PEER ID
 * ============================================================
 */
public function registarPeer(Request $request)
{
    $adminEgresso = Auth::user()->egresso;

    if (!$adminEgresso) {
        return response()->json(['success' => false], 403);
    }

    $request->validate([
        'room_id' => 'required|string',
        'peer_id' => 'required|string|max:100',
    ]);

    $chamada = Chamada::where('room_id', $request->room_id)->first();

    if (!$chamada) {
        return response()->json(['success' => false], 404);
    }

    $campo = (int) $chamada->chamador_id === (int) $adminEgresso->id
        ? 'peer_id_chamador'
        : 'peer_id_recetor';

    $chamada->update([$campo => $request->peer_id]);

    return response()->json(['success' => true, 'campo' => $campo]);
}

}