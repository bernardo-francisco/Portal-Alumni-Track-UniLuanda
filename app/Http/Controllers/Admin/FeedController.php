<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MuralNoticia;
use App\Models\MuralCurtida;
use App\Models\MuralComentario;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Lista todas as publicações do mural.
     */
    public function index(Request $request)
    {
        $query = MuralNoticia::with(['egresso.user', 'egresso.curso', 'admin'])
            ->withCount(['muralComentarios as comentarios_count', 'muralCurtidas as curtidas_count']);

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Busca por conteúdo ou nome
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('conteudo', 'LIKE', "%{$search}%")
                  ->orWhere('titulo', 'LIKE', "%{$search}%")
                  ->orWhereHas('egresso', function ($q2) use ($search) {
                      $q2->where('nome_completo', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('admin', function ($q3) use ($search) {
                      $q3->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $publicacoes = $query->orderBy('created_at', 'desc')->paginate(10);

        // Estatísticas reais
        $totalPublicacoes = MuralNoticia::count();

        $totalCurtidas = MuralCurtida::count();

        $totalComentarios = MuralComentario::count();

        $totalEgressos = MuralNoticia::whereNotNull('egresso_id')
            ->distinct('egresso_id')
            ->count('egresso_id');

        return view('admin.feed.index', compact(
            'publicacoes',
            'totalPublicacoes',
            'totalCurtidas',
            'totalComentarios',
            'totalEgressos'
        ));
    }

    /**
     * Mostra os detalhes de uma publicação.
     */
    public function show($id)
    {
        $publicacao = MuralNoticia::with([
            'egresso.user',
            'egresso.curso',
            'admin',
            'muralComentarios.egresso.user',
            'muralComentarios.egresso.curso',
        ])
        ->withCount(['muralComentarios as comentarios_count', 'muralCurtidas as curtidas_count'])
        ->findOrFail($id);

        // Contar publicações do egresso (se aplicável)
        if ($publicacao->egresso) {
            $publicacao->egresso->loadCount('publicacoes');
        }

        return view('admin.feed.show', compact('publicacao'));
    }

    /**
     * Elimina uma publicação.
     */
    public function destroy($id)
    {
        $publicacao = MuralNoticia::findOrFail($id);

        // Remover imagem se existir
        if ($publicacao->imagem_url && file_exists(public_path($publicacao->imagem_url))) {
            @unlink(public_path($publicacao->imagem_url));
        }

        // Remover curtidas e comentários associados
        MuralCurtida::where('mural_noticia_id', $publicacao->id)->delete();
        MuralComentario::where('mural_noticia_id', $publicacao->id)->delete();

        $publicacao->delete();

        return redirect()->route('admin.feed.index')
            ->with('success', 'Publicação eliminada com sucesso!');
    }
}