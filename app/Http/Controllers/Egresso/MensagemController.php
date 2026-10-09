<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Mensagem;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MensagemController extends Controller
{
    // ============================================================
    // 📋 LISTA DE CONVERSAS
    // ============================================================
    public function index()
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $mensagens = Mensagem::where('remetente_id', $egresso->id)
            ->orWhere('destinatario_id', $egresso->id)
            ->with(['remetente', 'destinatario'])
            ->orderBy('created_at', 'desc')
            ->get();

        $conversas = [];
        foreach ($mensagens as $mensagem) {
            $contatoId = $mensagem->remetente_id == $egresso->id
                ? $mensagem->destinatario_id
                : $mensagem->remetente_id;

            if (!isset($conversas[$contatoId])) {
                $contato = Egresso::find($contatoId);
                if ($contato) {
                    $conversas[$contatoId] = [
                        'contato'         => $contato,
                        'ultima_mensagem' => $this->previewMensagem($mensagem),
                        'data_ultima'     => $mensagem->created_at,
                        'nao_lidas'       => 0,
                    ];
                }
            }

            if ($mensagem->created_at > $conversas[$contatoId]['data_ultima']) {
                $conversas[$contatoId]['ultima_mensagem'] = $this->previewMensagem($mensagem);
                $conversas[$contatoId]['data_ultima']     = $mensagem->created_at;
            }
        }

        foreach ($conversas as $contatoId => &$conversa) {
            $conversa['nao_lidas'] = Mensagem::where('destinatario_id', $egresso->id)
                ->where('remetente_id', $contatoId)
                ->where('lida', false)
                ->count();
        }
        unset($conversa);

        usort($conversas, function ($a, $b) {
            return $b['data_ultima']->timestamp - $a['data_ultima']->timestamp;
        });

        $conversasData = $conversas;

        return view('egresso.mensagens.index', compact('conversasData'));
    }

    private function previewMensagem($mensagem): string
    {
        if ($mensagem->tipo === 'audio') {
            return '🎤 Mensagem de voz';
        }
        if ($mensagem->tipo === 'ficheiro') {
            return '📎 ' . ($mensagem->ficheiro_nome ?? 'Ficheiro');
        }
        return $mensagem->mensagem ?? '';
    }


    // ============================================================
    // 💬 CONVERSA COM UM CONTACTO
    // ============================================================
    public function conversa($contatoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $contato = Egresso::findOrFail($contatoId);

        $mensagens = Mensagem::where(function ($query) use ($egresso, $contato) {
                $query->where('remetente_id', $egresso->id)
                      ->where('destinatario_id', $contato->id);
            })
            ->orWhere(function ($query) use ($egresso, $contato) {
                $query->where('remetente_id', $contato->id)
                      ->where('destinatario_id', $egresso->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        Mensagem::where('destinatario_id', $egresso->id)
            ->where('remetente_id', $contato->id)
            ->where('lida', false)
            ->update(['lida' => true]);

        return view('egresso.mensagens.conversa', compact('contato', 'mensagens'));
    }


    // ============================================================
    // ✉️ ENVIAR MENSAGEM DE TEXTO
    // ============================================================
    public function enviar(Request $request, $contatoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $request->validate([
            'mensagem' => 'required|string|max:1000',
        ]);

        $contato = Egresso::findOrFail($contatoId);

        try {
            DB::beginTransaction();

            Mensagem::create([
                'remetente_id'    => $egresso->id,
                'destinatario_id' => $contato->id,
                'mensagem'        => $request->mensagem,
                'tipo'            => 'texto',
                'lida'            => false,
            ]);

            DB::commit();

            // ✅ Notificar destinatário
            NotificacaoService::novaMensagem(
                $egresso,
                $contato->id,
                $request->mensagem,
                route('egresso.mensagens.conversa', $egresso->id)
            );

            return redirect()->route('egresso.mensagens.conversa', $contatoId)
                ->with('success', 'Mensagem enviada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return back()->with('error', 'Erro ao enviar mensagem.');
        }
    }


    // ============================================================
    // 🎤 ENVIAR MENSAGEM DE ÁUDIO
    // ============================================================
    public function enviarAudio(Request $request, $contatoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Complete o seu perfil primeiro.'
            ], 403);
        }

        $request->validate([
            'audio'   => 'required|file|mimes:webm,ogg,mp3,wav,m4a|max:10240',
            'duracao' => 'nullable|integer|min:1|max:600',
        ]);

        $contato = Egresso::find($contatoId);
        if (!$contato) {
            return response()->json([
                'success' => false,
                'message' => 'Contato não encontrado.'
            ], 404);
        }

        $path = null;

        try {
            DB::beginTransaction();

            $file     = $request->file('audio');
            $ext      = strtolower($file->getClientOriginalExtension() ?: 'webm');
            $filename = 'audio_' . $egresso->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;

            $path = $file->storeAs('mensagens/audio', $filename, 'public');

            if (!$path) {
                throw new \Exception('Falha ao guardar o ficheiro.');
            }

            $mensagem = Mensagem::create([
                'remetente_id'    => $egresso->id,
                'destinatario_id' => $contato->id,
                'mensagem'        => null,
                'tipo'            => 'audio',
                'audio_url'       => $path,
                'audio_duracao'   => $request->input('duracao', 0),
                'lida'            => false,
            ]);

            DB::commit();

            // ✅ Notificar destinatário
            NotificacaoService::novaMensagemAudio(
                $egresso,
                $contato->id,
                route('egresso.mensagens.conversa', $egresso->id)
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'mensagem' => [
                        'id'            => $mensagem->id,
                        'tipo'          => 'audio',
                        'audio_url'     => asset('storage/' . $path),
                        'audio_duracao' => $mensagem->audio_duracao,
                        'created_at'    => $mensagem->created_at->format('H:i'),
                        'is_mine'       => true,
                    ],
                ]);
            }

            return back()->with('success', 'Áudio enviado!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Erro ao enviar áudio: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar áudio.'
                ], 500);
            }

            return back()->with('error', 'Erro ao enviar áudio.');
        }
    }


    // ============================================================
    // 📎 ENVIAR FICHEIRO
    // ============================================================
    public function enviarFicheiro(Request $request, $contatoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.'
            ], 403);
        }

        $request->validate([
            'ficheiro' => 'required|file|max:51200',
        ]);

        $contato = Egresso::find($contatoId);
        if (!$contato) {
            return response()->json([
                'success' => false,
                'message' => 'Contato não encontrado.'
            ], 404);
        }

        $path = null;

        try {
            DB::beginTransaction();

            $file     = $request->file('ficheiro');
            $ext      = strtolower($file->getClientOriginalExtension());
            $nomeOrig = $file->getClientOriginalName();
            $tamanho  = $file->getSize();
            $mime     = $file->getMimeType();

            $filename = 'ficheiro_' . $egresso->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;
            $path     = $file->storeAs('mensagens/ficheiros', $filename, 'public');

            if (!$path) {
                throw new \Exception('Falha ao guardar o ficheiro.');
            }

            $mensagem = Mensagem::create([
                'remetente_id'     => $egresso->id,
                'destinatario_id'  => $contato->id,
                'mensagem'         => null,
                'tipo'             => 'ficheiro',
                'ficheiro_url'     => $path,
                'ficheiro_nome'    => $nomeOrig,
                'ficheiro_tamanho' => $tamanho,
                'ficheiro_tipo'    => $mime,
                'lida'             => false,
            ]);

            DB::commit();

            // ✅ Notificar destinatário
            NotificacaoService::novoFicheiro(
                $egresso,
                $contato->id,
                $nomeOrig,
                route('egresso.mensagens.conversa', $egresso->id)
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'mensagem' => [
                        'id'               => $mensagem->id,
                        'tipo'             => 'ficheiro',
                        'ficheiro_url'     => asset('storage/' . $path),
                        'ficheiro_nome'    => $nomeOrig,
                        'ficheiro_tamanho' => $mensagem->tamanhoFormatado(),
                        'ficheiro_icone'   => $mensagem->iconeFicheiro(),
                        'created_at'       => $mensagem->created_at->format('H:i'),
                        'is_mine'          => true,
                    ],
                ]);
            }

            return back()->with('success', 'Ficheiro enviado!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Erro ao enviar ficheiro: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar ficheiro.'
                ], 500);
            }

            return back()->with('error', 'Erro ao enviar ficheiro.');
        }
    }


    // ============================================================
    // ✏️ EDITAR MENSAGEM
    // ============================================================
    public function editar(Request $request, $mensagemId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.'
            ], 403);
        }

        $mensagem = Mensagem::where('id', $mensagemId)
            ->where('remetente_id', $egresso->id)
            ->first();

        if (!$mensagem) {
            return response()->json([
                'success' => false,
                'message' => 'Mensagem não encontrada.'
            ], 404);
        }

        if ($mensagem->tipo === 'audio' || $mensagem->tipo === 'ficheiro') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível editar este tipo de mensagem.'
            ], 403);
        }

        if ($mensagem->created_at->diffInMinutes(now()) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível editar mensagens com mais de 5 minutos.'
            ], 403);
        }

        $request->validate([
            'mensagem' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $mensagem->update([
                'mensagem'   => $request->mensagem,
                'editado'    => true,
                'editado_em' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Mensagem editada com sucesso!',
                'mensagem'   => $mensagem->mensagem,
                'editado_em' => $mensagem->editado_em->format('d/m/Y H:i'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao editar mensagem: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao editar mensagem.'
            ], 500);
        }
    }


    // ============================================================
    // 🗑️ ELIMINAR MENSAGEM
    // ============================================================
    public function eliminar($mensagemId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida.'
            ], 403);
        }

        $mensagem = Mensagem::where('id', $mensagemId)
            ->where('remetente_id', $egresso->id)
            ->first();

        if (!$mensagem) {
            return response()->json([
                'success' => false,
                'message' => 'Mensagem não encontrada.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            if ($mensagem->tipo === 'audio' && $mensagem->audio_url) {
                if (Storage::disk('public')->exists($mensagem->audio_url)) {
                    Storage::disk('public')->delete($mensagem->audio_url);
                }
            }

            if ($mensagem->tipo === 'ficheiro' && $mensagem->ficheiro_url) {
                if (Storage::disk('public')->exists($mensagem->ficheiro_url)) {
                    Storage::disk('public')->delete($mensagem->ficheiro_url);
                }
            }

            $mensagem->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mensagem eliminada com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao eliminar mensagem: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar mensagem.'
            ], 500);
        }
    }
}