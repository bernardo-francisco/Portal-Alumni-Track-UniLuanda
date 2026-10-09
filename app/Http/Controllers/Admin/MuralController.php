<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MuralNoticia;
use App\Models\MuralCurtida;
use App\Models\MuralComentario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MuralController extends Controller
{
    public function index()
    {
        $publicacoes = MuralNoticia::orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.mural.index', compact('publicacoes'));
    }

    public function create()
    {
        return view('admin.mural.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'      => 'required|string|max:200',
            'conteudo'    => 'required|string',
            'tipo'        => 'required|in:noticia,evento,edital',
            'imagem'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'data_evento' => 'nullable|date',
            'local'       => 'nullable|string|max:200',
            'destaque'    => 'nullable|boolean',
            'publicado'   => 'nullable|boolean',
        ]);

        $imagemUrl = null;
        if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
            $imagem = $request->file('imagem');
            $nome = 'mural_' . uniqid() . '_' . time() . '.' . $imagem->getClientOriginalExtension();
            $imagem->move(public_path('uploads/mural'), $nome);
            $imagemUrl = 'uploads/mural/' . $nome;
        }

        MuralNoticia::create([
            'admin_id'    => Auth::id(),
            'titulo'      => $validated['titulo'],
            'conteudo'    => $validated['conteudo'],
            'tipo'        => $validated['tipo'],
            'imagem_url'  => $imagemUrl,
            'data_evento' => $validated['data_evento'] ?? null,
            'local'       => $validated['local'] ?? null,
            'destaque'    => $request->has('destaque'),
            'publicado'   => $request->has('publicado'),
        ]);

        return redirect()->route('admin.mural.index')
            ->with('success', 'Publicação criada com sucesso!');
    }

    public function show($id)
    {
        $publicacao = MuralNoticia::findOrFail($id);

        return view('admin.mural.show', compact('publicacao'));
    }

    public function edit($id)
    {
        $publicacao = MuralNoticia::findOrFail($id);

        // 🔒 Bloquear edição de publicações de egressos
        if ($publicacao->egresso_id !== null) {
            return redirect()->route('admin.mural.show', $publicacao->id)
                ->with('error', 'Não pode editar publicações criadas por egressos. Apenas pode visualizá-las ou eliminá-las.');
        }

        return view('admin.mural.edit', compact('publicacao'));
    }

    public function update(Request $request, $id)
    {
        $publicacao = MuralNoticia::findOrFail($id);

        // 🔒 Bloquear edição de publicações de egressos
        if ($publicacao->egresso_id !== null) {
            return redirect()->route('admin.mural.show', $publicacao->id)
                ->with('error', 'Não pode editar publicações criadas por egressos.');
        }

        $validated = $request->validate([
            'titulo'      => 'required|string|max:200',
            'conteudo'    => 'required|string',
            'tipo'        => 'required|in:noticia,evento,edital',
            'imagem'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'data_evento' => 'nullable|date',
            'local'       => 'nullable|string|max:200',
            'destaque'    => 'nullable|boolean',
            'publicado'   => 'nullable|boolean',
        ]);

        $imagemUrl = $publicacao->imagem_url;
        if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
            if ($imagemUrl && file_exists(public_path($imagemUrl))) {
                @unlink(public_path($imagemUrl));
            }
            $imagem = $request->file('imagem');
            $nome = 'mural_' . uniqid() . '_' . time() . '.' . $imagem->getClientOriginalExtension();
            $imagem->move(public_path('uploads/mural'), $nome);
            $imagemUrl = 'uploads/mural/' . $nome;
        }

        $publicacao->update([
            'titulo'      => $validated['titulo'],
            'conteudo'    => $validated['conteudo'],
            'tipo'        => $validated['tipo'],
            'imagem_url'  => $imagemUrl,
            'data_evento' => $validated['data_evento'] ?? null,
            'local'       => $validated['local'] ?? null,
            'destaque'    => $request->has('destaque'),
            'publicado'   => $request->has('publicado'),
        ]);

        return redirect()->route('admin.mural.index')
            ->with('success', 'Publicação atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $publicacao = MuralNoticia::findOrFail($id);

        // Remover imagem
        if ($publicacao->imagem_url && file_exists(public_path($publicacao->imagem_url))) {
            @unlink(public_path($publicacao->imagem_url));
        }

        // 🔔 Notificar o egresso (se for publicação dele) antes de eliminar
        if ($publicacao->egresso_id && $publicacao->egresso) {
            try {
                $publicacao->egresso->notificar(
                    'Publicação removida pela administração',
                    'A sua publicação "' . ($publicacao->titulo ?? 'sem título') . '" foi removida por violar as regras da plataforma.',
                    'sistema',
                    null
                );
            } catch (\Exception $e) {
                Log::warning('Erro ao notificar egresso sobre eliminação: ' . $e->getMessage());
            }
        }

        // Remover curtidas e comentários associados
        MuralCurtida::where('mural_noticia_id', $publicacao->id)->delete();
        MuralComentario::where('mural_noticia_id', $publicacao->id)->delete();

        $publicacao->delete();

        return redirect()->route('admin.mural.index')
            ->with('success', 'Publicação eliminada com sucesso!');
    }
}