<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Chamada;
use App\Models\Egresso;
use App\Models\Mensagem;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoCallController extends Controller
{
    /**
     * ============================================================
     * INICIAR CHAMADA
     * ============================================================
     */
    public function iniciar(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate([
            'target_user_id' => 'required|exists:egressos,id',
            'type'           => 'required|in:video,audio',
        ]);

        // Limpar chamadas antigas (> 2 min)
        Chamada::whereIn('status', ['pendente', 'aceite'])
            ->where('created_at', '<', now()->subMinutes(2))
            ->update([
                'status'       => 'perdida',
                'terminada_em' => now(),
            ]);

        if ((int) $request->target_user_id === (int) $egresso->id) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível iniciar uma chamada para si próprio.',
            ], 422);
        }

        $chamadaExistente = Chamada::where(function ($q) use ($egresso, $request) {
            $q->where(function ($q2) use ($egresso, $request) {
                $q2->where('chamador_id', $egresso->id)
                   ->where('recetor_id', $request->target_user_id);
            })
            ->orWhere(function ($q2) use ($egresso, $request) {
                $q2->where('chamador_id', $request->target_user_id)
                   ->where('recetor_id', $egresso->id);
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
            DB::beginTransaction();

            Mensagem::create([
                'remetente_id'    => $egresso->id,
                'destinatario_id' => $request->target_user_id,
                'mensagem'        => $request->type === 'video' ? 'Chamada de vídeo' : 'Chamada de voz',
                'tipo'            => 'chamada',
                'lida'            => true,
            ]);

            $chamada = Chamada::create([
                'room_id'                 => $roomId,
                'chamador_id'             => $egresso->id,
                'recetor_id'              => $request->target_user_id,
                'tipo'                    => $request->type,
                'status'                  => 'pendente',
                'sinal_oferta'            => null,
                'sinal_resposta'          => null,
                'ice_candidates_chamador' => [],
                'ice_candidates_recetor'  => [],
            ]);

            DB::commit();

            try {
                NotificacaoService::chamadaRecebida(
                    $egresso,
                    $request->target_user_id,
                    $request->type,
                    $roomId
                );
            } catch (\Throwable $e) {
                Log::warning('Erro ao criar notificação: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'room_id' => $roomId,
                'chamada' => $chamada,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao iniciar chamada: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar chamada.',
            ], 500);
        }
    }

    /**
     * ============================================================
     * VERIFICAR CHAMADAS PENDENTES
     * ============================================================
     */
    public function verificar(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json(['chamada' => null]);
        }

        $chamada = Chamada::where('recetor_id', $egresso->id)
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
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)
            ->where('recetor_id', $egresso->id)
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
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate(['room_id' => 'required|string']);

        $chamada = Chamada::where('room_id', $request->room_id)
            ->where('recetor_id', $egresso->id)
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
     * SINAL (oferta / resposta / ICE)
     * ============================================================
     */
    public function sinal(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.',
            ], 403);
        }

        $request->validate([
            'room_id' => 'required|string',
            'tipo'    => 'required|in:oferta,resposta,ice_chamador,ice_recetor',
            'dados'   => 'required|array',
        ]);

        $chamada = Chamada::where('room_id', $request->room_id)->first();

        if (!$chamada) {
            return response()->json([
                'success' => false,
                'message' => 'Chamada não encontrada.',
            ], 404);
        }

        $isChamador = (int) $chamada->chamador_id === (int) $egresso->id;
        $isRecetor  = (int) $chamada->recetor_id  === (int) $egresso->id;

        if (!$isChamador && !$isRecetor) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado.',
            ], 403);
        }

        if ($chamada->status !== 'aceite') {
            return response()->json([
                'success' => false,
                'message' => 'Chamada ainda não foi aceite.',
            ], 409);
        }

        if ($request->tipo === 'oferta') {
            if (!$isChamador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Só o chamador pode enviar a oferta.',
                ], 403);
            }

            $chamada->sinal_oferta = $request->dados;
            $chamada->save();

            return response()->json(['success' => true]);
        }

        if ($request->tipo === 'resposta') {
            if (!$isRecetor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Só o recetor pode enviar a resposta.',
                ], 403);
            }

            $chamada->sinal_resposta = $request->dados;
            $chamada->save();

            return response()->json(['success' => true]);
        }

        if ($request->tipo === 'ice_chamador' || $request->tipo === 'ice_recetor') {
            if ($request->tipo === 'ice_chamador' && !$isChamador) {
                return response()->json(['success' => false], 403);
            }
            if ($request->tipo === 'ice_recetor' && !$isRecetor) {
                return response()->json(['success' => false], 403);
            }

            $campo = $request->tipo === 'ice_chamador'
                ? 'ice_candidates_chamador'
                : 'ice_candidates_recetor';

            $existentes = $chamada->{$campo};

            if (!is_array($existentes)) {
                $existentes = [];
            }

            $novo = $request->dados;
            $duplicado = false;

            foreach ($existentes as $c) {
                if (
                    ($c['candidate']     ?? null) === ($novo['candidate']     ?? null) &&
                    ($c['sdpMid']        ?? null) === ($novo['sdpMid']        ?? null) &&
                    ($c['sdpMLineIndex'] ?? null) === ($novo['sdpMLineIndex'] ?? null)
                ) {
                    $duplicado = true;
                    break;
                }
            }

            if (!$duplicado) {
                $existentes[] = $novo;
            }

            $chamada->{$campo} = $existentes;
            $chamada->save();

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipo de sinal inválido.',
        ], 422);
    }

    /**
     * ============================================================
     * ESTADO + SIGNALING
     * ============================================================
     */
    public function estado(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
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

        $isChamador = (int) $chamada->chamador_id === (int) $egresso->id;
        $isRecetor  = (int) $chamada->recetor_id  === (int) $egresso->id;

        if (!$isChamador && !$isRecetor) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado.',
            ], 403);
        }

        return response()->json([
            'success'          => true,
            'status'           => $chamada->status,
            'is_chamador'      => $isChamador,
            'chamador_id'      => (int) $chamada->chamador_id,
            'recetor_id'       => (int) $chamada->recetor_id,
            'tipo'             => $chamada->tipo,

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
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
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

        $isParticipante =
            (int) $chamada->chamador_id === (int) $egresso->id ||
            (int) $chamada->recetor_id  === (int) $egresso->id;

        if (!$isParticipante) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado.',
            ], 403);
        }

        if (!in_array($chamada->status, ['terminada', 'recusada', 'perdida'])) {
            $chamada->update([
                'status'       => 'terminada',
                'terminada_em' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * ============================================================
     * REGISTAR PEER ID (ÚNICO)
     * ============================================================
     */
    public function registarPeer(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
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

        $campo = (int) $chamada->chamador_id === (int) $egresso->id
            ? 'peer_id_chamador'
            : 'peer_id_recetor';

        $chamada->update([$campo => $request->peer_id]);

        return response()->json(['success' => true, 'campo' => $campo]);
    }

    /**
     * ============================================================
     * SALA DA CHAMADA
     * ============================================================
     */
    public function sala($roomId, Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $chamada = Chamada::where('room_id', $roomId)->first();

        if (!$chamada) {
            return redirect()->route('egresso.mensagens')
                ->with('error', 'Chamada não encontrada ou já terminada.');
        }

        $isChamador = (int) $chamada->chamador_id === (int) $egresso->id;
        $isRecetor  = (int) $chamada->recetor_id  === (int) $egresso->id;

        if (!$isChamador && !$isRecetor) {
            abort(403, 'Não autorizado para esta chamada.');
        }

        $type = $chamada->tipo;

        $targetUserId = $isChamador
            ? $chamada->recetor_id
            : $chamada->chamador_id;

        $role = $isChamador ? 'chamador' : 'recetor';

        return view('egresso.video-call.sala', compact(
            'roomId',
            'targetUserId',
            'egresso',
            'type',
            'chamada',
            'isChamador',
            'isRecetor',
            'role'
        ));
    }
}