<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\MuralNoticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MuralController extends Controller
{
    public function index(Request $request)
    {
        $query = MuralNoticia::where('publicado', true);

        // Filtro por tipo (noticia, evento, edital)
        if ($request->filled('tipo') && in_array($request->tipo, ['noticia', 'evento', 'edital'])) {
            $query->where('tipo', $request->tipo);
        }

        $publicacoes = $query
            ->orderBy('destaque', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query()); // preserva ?tipo=... na paginação

        return view('egresso.mural.index', compact('publicacoes'));
    }

    public function show(MuralNoticia $publicacao)
    {
        // Só publicações publicadas (ou do próprio egresso)
        if (!$publicacao->publicado && $publicacao->egresso_id !== Auth::user()->egresso->id) {
            abort(404);
        }

        // Carrega relações para evitar N+1
        $publicacao->load(['muralComentarios.egresso', 'muralCurtidas', 'egresso', 'admin']);

        // Verifica se o utilizador atual já curtiu
        $curtidoPeloUser = $publicacao->muralCurtidas
            ->where('egresso_id', Auth::user()->egresso->id)
            ->isNotEmpty();

        return view('egresso.mural.show', compact('publicacao', 'curtidoPeloUser'));
    }
}