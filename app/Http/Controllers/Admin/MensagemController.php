<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Mensagem;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MensagemController extends Controller
{
    public function index()
    {
        $admin = Auth::user()->egresso;

        if (!$admin) {
            return view('admin.mensagens.index', ['conversasData' => []])
                ->with('error', 'Perfil de egresso não encontrado.');
        }

        $conversas = Mensagem::where('remetente_id', $admin->id)
            ->orWhere('destinatario_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($msg) use ($admin) {
                return $msg->remetente_id == $admin->id
                    ? $msg->destinatario_id
                    : $msg->remetente_id;
            })
            ->map(function ($msgs) {
                return $msgs->first();
            })
            ->values();

        $conversasData = [];
        foreach ($conversas as $msg) {
            $idContato = $msg->remetente_id == $admin->id
                ? $msg->destinatario_id
                : $msg->remetente_id;

            $contato = Egresso::find($idContato);

            if ($contato) {
                $naoLidas = Mensagem::where('remetente_id', $idContato)
                    ->where('destinatario_id', $admin->id)
                    ->where('lida', false)
                    ->count();

                $conversasData[] = [
                    'contato'         => $contato,
                    'ultima_mensagem' => $this->previewMensagem($msg),
                    'data_ultima'     => $msg->created_at,
                    'nao_lidas'       => $naoLidas,
                ];
            }
        }

        usort($conversasData, function ($a, $b) {
            return $b['data_ultima']->timestamp - $a['data_ultima']->timestamp;
        });

        return view('admin.mensagens.index', compact('conversasData'));
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

    public function conversa(Egresso $egresso)
    {
        $admin = Auth::user()->egresso;

        if (!$admin) {
            return redirect()->route('admin.mensagens.index')
                ->with('error', 'Perfil não encontrado.');
        }

        Mensagem::where('remetente_id', $egresso->id)
            ->where('destinatario_id', $admin->id)
            ->where('lida', false)
            ->update(['lida' => true]);

        $mensagens = Mensagem::where(function ($q) use ($admin, $egresso) {
                $q->where('remetente_id', $admin->id)
                  ->where('destinatario_id', $egresso->id);
            })
            ->orWhere(function ($q) use ($admin, $egresso) {
                $q->where('remetente_id', $egresso->id)
                  ->where('destinatario_id', $admin->id);
            })
            ->orderBy('created_at')
            ->get();

        return view('admin.mensagens.conversa', [
        'egresso'   => $egresso,
        'mensagens' => $mensagens,
        'contato'   => $egresso,   
]);
    }

    public function enviar(Request $request, Egresso $egresso)
    {
        $admin = Auth::user()->egresso;

        if (!$admin) {
            return back()->with('error', 'Perfil não encontrado.');
        }

        $request->validate([
            'mensagem' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            Mensagem::create([
                'remetente_id'    => $admin->id,
                'destinatario_id' => $egresso->id,
                'mensagem'        => $request->mensagem,
                'tipo'            => 'texto',
                'lida'            => false,
            ]);

            DB::commit();

            // ✅ Notificar egresso
            NotificacaoService::novaMensagem(
                $admin,
                $egresso->id,
                $request->mensagem,
                route('egresso.mensagens.conversa', $admin->id)
            );

            return back()->with('success', 'Mensagem enviada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar mensagem: ' . $e->getMessage());
            return back()->with('error', 'Erro ao enviar mensagem.');
        }
    }


    public function enviarAudio(Request $request, Egresso $egresso)
    {
        $admin = Auth::user()->egresso;

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Sessão inválida.'], 403);
        }

        $request->validate([
            'audio'   => 'required|file|mimes:webm,ogg,mp3,wav,m4a|max:10240',
            'duracao' => 'nullable|integer|min:1|max:600',
        ]);

        $path = null;

        try {
            DB::beginTransaction();

            $file     = $request->file('audio');
            $ext      = strtolower($file->getClientOriginalExtension() ?: 'webm');
            $filename = 'audio_' . $admin->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;

            $path = $file->storeAs('mensagens/audio', $filename, 'public');

            if (!$path) throw new \Exception('Falha ao guardar áudio.');

            $mensagem = Mensagem::create([
                'remetente_id'    => $admin->id,
                'destinatario_id' => $egresso->id,
                'mensagem'        => null,
                'tipo'            => 'audio',
                'audio_url'       => $path,
                'audio_duracao'   => $request->input('duracao', 0),
                'lida'            => false,
            ]);

            DB::commit();

            // ✅ Notificar egresso
            NotificacaoService::novaMensagemAudio(
                $admin,
                $egresso->id,
                route('egresso.mensagens.conversa', $admin->id)
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

            Log::error('Erro ao enviar áudio (admin): ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar áudio.'
            ], 500);
        }
    }


    public function enviarFicheiro(Request $request, Egresso $egresso)
    {
        $admin = Auth::user()->egresso;

        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Sessão inválida.'], 403);
        }

        $request->validate([
            'ficheiro' => 'required|file|max:51200',
        ]);

        $path = null;

        try {
            DB::beginTransaction();

            $file     = $request->file('ficheiro');
            $ext      = strtolower($file->getClientOriginalExtension());
            $nomeOrig = $file->getClientOriginalName();
            $tamanho  = $file->getSize();
            $mime     = $file->getMimeType();

            $filename = 'ficheiro_' . $admin->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;
            $path     = $file->storeAs('mensagens/ficheiros', $filename, 'public');

            if (!$path) throw new \Exception('Falha ao guardar o ficheiro.');

            $mensagem = Mensagem::create([
                'remetente_id'     => $admin->id,
                'destinatario_id'  => $egresso->id,
                'mensagem'         => null,
                'tipo'             => 'ficheiro',
                'ficheiro_url'     => $path,
                'ficheiro_nome'    => $nomeOrig,
                'ficheiro_tamanho' => $tamanho,
                'ficheiro_tipo'    => $mime,
                'lida'             => false,
            ]);

            DB::commit();

            // ✅ Notificar egresso
            NotificacaoService::novoFicheiro(
                $admin,
                $egresso->id,
                $nomeOrig,
                route('egresso.mensagens.conversa', $admin->id)
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

            Log::error('Erro ao enviar ficheiro (admin): ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar ficheiro.'
            ], 500);
        }
    }


    public function editar(Request $request, $mensagemId)
    {
        $admin = Auth::user()->egresso;

        $mensagem = Mensagem::where('id', $mensagemId)
            ->where('remetente_id', $admin->id)
            ->first();

        if (!$mensagem) {
            return response()->json(['success' => false, 'message' => 'Mensagem não encontrada.'], 404);
        }

        if ($mensagem->tipo === 'audio' || $mensagem->tipo === 'ficheiro') {
            return response()->json(['success' => false, 'message' => 'Não é possível editar este tipo.'], 403);
        }

        if ($mensagem->created_at->diffInMinutes(now()) > 5) {
            return response()->json(['success' => false, 'message' => 'Mensagens com mais de 5 minutos não podem ser editadas.'], 403);
        }

        $request->validate(['mensagem' => 'required|string|max:1000']);

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

            return response()->json(['success' => false, 'message' => 'Erro ao editar mensagem.'], 500);
        }
    }


    public function eliminar($mensagemId)
    {
        $admin = Auth::user()->egresso;

        $mensagem = Mensagem::where('id', $mensagemId)
            ->where('remetente_id', $admin->id)
            ->first();

        if (!$mensagem) {
            return response()->json(['success' => false, 'message' => 'Mensagem não encontrada.'], 404);
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

            return response()->json(['success' => true, 'message' => 'Mensagem eliminada com sucesso!']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao eliminar mensagem: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Erro ao eliminar mensagem.'], 500);
        }
    }
}