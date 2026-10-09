<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\MuralNoticia;
use App\Models\MuralCurtida;
use App\Models\MuralComentario;
use App\Models\Notificacao;
use App\Helpers\NotificarAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedController extends Controller
{
    /**
     * Mostrar o feed com publicações do mural
     */
    public function index()
    {
        $egresso = Auth::user()->egresso;

        $publicacoes = MuralNoticia::where('publicado', true)
            ->with([
                'admin' => function ($q) {
                    $q->select('id', 'name', 'photo_url');
                },
                'egresso' => function ($q) {
                    $q->select('id', 'nome_completo', 'foto_url');
                }
            ])
            ->withCount([
                'muralComentarios as total_comentarios',
                'muralCurtidas as total_curtidas'
            ])
            ->orderBy('destaque', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $curtidas = MuralCurtida::where('egresso_id', $egresso->id)
            ->pluck('mural_noticia_id')
            ->toArray();

        return view('egresso.feed.index', compact('publicacoes', 'curtidas'));
    }

    /**
     * Criar uma nova publicação no mural (com suporte a imagem)
     */
    public function store(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return back()->with('error', 'Perfil de egresso não encontrado.');
        }

        $validated = $request->validate([
            'conteudo' => 'required|string|max:2000',
            'imagem'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        try {
            DB::beginTransaction();

            /* =========================================================
               PROCESSAR IMAGEM
               ========================================================= */
            $imagemUrl = null;

            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                $uploadPath = public_path('uploads/feed');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imagem = $request->file('imagem');
                $nome = 'feed_' . uniqid() . '_' . time() . '.' . $imagem->getClientOriginalExtension();
                $imagem->move($uploadPath, $nome);

                $imagemUrl = 'uploads/feed/' . $nome;
            }

            /* =========================================================
               CRIAR PUBLICAÇÃO
               ========================================================= */
            $publicacao = MuralNoticia::create([
                'admin_id'    => null,
                'egresso_id'  => $egresso->id,
                'titulo'      => 'Publicação de ' . $egresso->nome_completo,
                'conteudo'    => $validated['conteudo'],
                'tipo'        => 'noticia',
                'imagem_url'  => $imagemUrl,
                'publicado'   => true,
            ]);

            /* =========================================================
               🔔 NOTIFICAR ADMIN
               ========================================================= */
            NotificarAdmin::todos(
                'Nova publicação no mural',
                $egresso->nome_completo . ' publicou algo novo no mural.',
                'sistema',
                route('admin.feed.show', $publicacao->id)
            );

            DB::commit();

            return back()->with('success', '✅ Publicação criada com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar publicação:', [
                'error'      => $e->getMessage(),
                'egresso_id' => $egresso->id,
            ]);

            return back()->with('error', 'Erro ao criar publicação: ' . $e->getMessage());
        }
    }

    /**
     * Curtir uma publicação do mural
     */
    public function curtir(Request $request, MuralNoticia $publicacao)
    {
        $egresso = Auth::user()->egresso;

        try {
            DB::beginTransaction();

            $curtida = MuralCurtida::where('mural_noticia_id', $publicacao->id)
                ->where('egresso_id', $egresso->id)
                ->first();

            if ($curtida) {
                $curtida->delete();
                $mensagem = 'Curtida removida.';
                $curtido = false;
            } else {
                MuralCurtida::create([
                    'mural_noticia_id' => $publicacao->id,
                    'egresso_id'       => $egresso->id,
                ]);

                /* =========================================================
                   NOTIFICAR O AUTOR (só se não for o próprio)
                   ========================================================= */
                $this->notificarAutorPublicacao(
                    $publicacao,
                    $egresso,
                    'curtida',
                    'Nova curtida',
                    $egresso->nome_completo . ' curtiu a sua publicação.'
                );

                $mensagem = 'Curtida adicionada.';
                $curtido = true;
            }

            DB::commit();

            $total = MuralCurtida::where('mural_noticia_id', $publicacao->id)->count();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'curtido' => $curtido,
                    'total'   => $total,
                    'message' => $mensagem,
                ]);
            }

            return back()->with('success', $mensagem);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao curtir:', [
                'mural_noticia_id' => $publicacao->id,
                'error'            => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar curtida: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao processar curtida: ' . $e->getMessage());
        }
    }

    /**
     * Comentar uma publicação do mural
     */
    public function comentar(Request $request, MuralNoticia $publicacao)
    {
        $egresso = Auth::user()->egresso;

        $validated = $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $comentario = MuralComentario::create([
                'mural_noticia_id' => $publicacao->id,
                'egresso_id'       => $egresso->id,
                'conteudo'         => $validated['conteudo'],
            ]);

            /* =========================================================
               NOTIFICAR O AUTOR (só se não for o próprio)
               ========================================================= */
            $this->notificarAutorPublicacao(
                $publicacao,
                $egresso,
                'comentario',
                'Novo comentário',
                $egresso->nome_completo . ' comentou a sua publicação.'
            );

            DB::commit();

            $total = MuralComentario::where('mural_noticia_id', $publicacao->id)->count();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'comentario' => [
                        'id'         => $comentario->id,
                        'conteudo'   => $comentario->conteudo,
                        'egresso'    => [
                            'nome' => $egresso->nome_completo,
                            'foto' => $egresso->foto_url,
                        ],
                        'created_at' => $comentario->created_at->diffForHumans(),
                    ],
                    'total'   => $total,
                    'message' => 'Comentário adicionado.',
                ]);
            }

            return back()->with('success', 'Comentário adicionado.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao comentar:', [
                'mural_noticia_id' => $publicacao->id,
                'error'            => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar comentário: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erro ao adicionar comentário: ' . $e->getMessage());
        }
    }

    /**
     * Editar comentário
     */
    public function editarComentario(Request $request, MuralComentario $comentario)
    {
        $egresso = Auth::user()->egresso;

        if ($comentario->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        $validated = $request->validate([
            'conteudo' => 'required|string|max:1000',
        ]);

        try {
            $comentario->update([
                'conteudo'   => $validated['conteudo'],
                'editado'    => true,
                'editado_em' => now(),
            ]);

            return back()->with('success', 'Comentário actualizado.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao actualizar comentário: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar comentário
     */
    public function eliminarComentario(Request $request, MuralComentario $comentario)
    {
        $egresso = Auth::user()->egresso;

        if ($comentario->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            $comentario->delete();

            return back()->with('success', 'Comentário removido.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover comentário: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar uma publicação (conteúdo + imagem opcional).
     */
    public function update(Request $request, MuralNoticia $publicacao)
    {
        $egresso = Auth::user()->egresso;

        // Só o autor pode editar
        if ($publicacao->admin_id !== null) {
            return back()->with('error', 'Não autorizado.');
        }

        if ($publicacao->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        $validated = $request->validate([
            'conteudo'       => 'required|string|max:2000',
            'imagem'         => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remover_imagem' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $imagemUrl = $publicacao->imagem_url;

            /* =========================================================
               REMOVER IMAGEM (checkbox)
               ========================================================= */
            if ($request->boolean('remover_imagem') && $imagemUrl) {
                if (file_exists(public_path($imagemUrl))) {
                    @unlink(public_path($imagemUrl));
                }
                $imagemUrl = null;
            }

            /* =========================================================
               SUBSTITUIR IMAGEM
               ========================================================= */
            if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                if ($imagemUrl && file_exists(public_path($imagemUrl))) {
                    @unlink(public_path($imagemUrl));
                }

                $uploadPath = public_path('uploads/feed');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $imagem = $request->file('imagem');
                $nome = 'feed_' . uniqid() . '_' . time() . '.' . $imagem->getClientOriginalExtension();
                $imagem->move($uploadPath, $nome);

                $imagemUrl = 'uploads/feed/' . $nome;
            }

            $publicacao->update([
                'conteudo'   => $validated['conteudo'],
                'imagem_url' => $imagemUrl,
            ]);

            DB::commit();

            return back()->with('success', '✅ Publicação atualizada com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao actualizar publicação:', [
                'publicacao_id' => $publicacao->id,
                'error'         => $e->getMessage(),
            ]);

            return back()->with('error', 'Erro ao actualizar publicação: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar uma publicação (remove imagem + notifica admin).
     */
    public function destroy(MuralNoticia $publicacao)
    {
        $egresso = Auth::user()->egresso;

        if ($publicacao->admin_id !== null || $publicacao->egresso_id !== $egresso->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            DB::beginTransaction();

            // Remover imagem
            if ($publicacao->imagem_url && file_exists(public_path($publicacao->imagem_url))) {
                @unlink(public_path($publicacao->imagem_url));
            }

            // Remover curtidas e comentários
            MuralCurtida::where('mural_noticia_id', $publicacao->id)->delete();
            MuralComentario::where('mural_noticia_id', $publicacao->id)->delete();

            $publicacao->delete();

            /* =========================================================
               🔔 NOTIFICAR ADMIN
               ========================================================= */
            NotificarAdmin::todos(
                'Publicação removida pelo autor',
                $egresso->nome_completo . ' removeu uma publicação do mural.',
                'sistema',
                route('admin.feed.index')
            );

            DB::commit();

            return back()->with('success', 'Publicação removida com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao remover publicação: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar uma publicação do mural (alias)
     */
    public function atualizar(Request $request, MuralNoticia $publicacao)
    {
        return $this->update($request, $publicacao);
    }

    /**
     * ============================================================
     * HELPER: Notificar o autor da publicação
     * ============================================================
     *
     * Notifica:
     * - Se for publicação de egresso → notifica o egresso autor
     * - Se for publicação de admin  → notifica todos os admins
     */
    private function notificarAutorPublicacao(
        MuralNoticia $publicacao,
        $autorAcao,
        string $tipo,
        string $titulo,
        string $mensagem
    ): void {
        try {
            // Publicação de egresso → notificar o egresso autor
            if ($publicacao->egresso_id && $publicacao->egresso_id != $autorAcao->id) {
                Notificacao::create([
                    'egresso_id' => $publicacao->egresso_id,
                    'tipo'       => $tipo,
                    'titulo'     => $titulo,
                    'mensagem'   => $mensagem,
                    'link'       => route('egresso.feed'),
                    'lida'       => false,
                ]);
            }

            // Publicação de admin → notificar todos os admins
            if ($publicacao->admin_id) {
                NotificarAdmin::todos(
                    $titulo,
                    $mensagem,
                    $tipo,
                    route('admin.feed.show', $publicacao->id)
                );
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar autor da publicação: ' . $e->getMessage());
        }
    }
}