<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Conexao;
use App\Models\Localizacao;
use App\Models\Profissional;
use App\Models\Publicacao;
use App\Models\Comentario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EgressoController extends Controller
{
    /**
     * Mostrar perfil público de um egresso (para outros egressos visualizarem)
     */
    public function show($id)
    {
        $egressoLogado = Auth::user()->egresso;

        if (!$egressoLogado) {
            return redirect()->route('egresso.perfil.editar')
                            ->with('warning', 'Complete seu perfil primeiro.');
        }

        // Buscar o egresso que está sendo visualizado
        $egresso = Egresso::with(['curso', 'curso.unidade'])->findOrFail($id);

        // Verificar se é o próprio perfil
        $isProprio = $egressoLogado->id == $egresso->id;

        // Se for o próprio, redirecionar para o perfil de edição
        if ($isProprio) {
            return redirect()->route('egresso.perfil')
                            ->with('info', 'Este é o seu perfil.');
        }

        // Verificar se é da rede do egresso logado
        $isConexao = Conexao::where(function($q) use ($egressoLogado, $egresso) {
                                $q->where('solicitante_id', $egressoLogado->id)
                                  ->where('destinatario_id', $egresso->id);
                            })
                            ->orWhere(function($q) use ($egressoLogado, $egresso) {
                                $q->where('solicitante_id', $egresso->id)
                                  ->where('destinatario_id', $egressoLogado->id);
                            })
                            ->where('status', 'aceito')
                            ->exists();

        // Verificar se já existe pedido de conexão pendente
        $pedidoPendente = Conexao::where(function($q) use ($egressoLogado, $egresso) {
                                    $q->where('solicitante_id', $egressoLogado->id)
                                      ->where('destinatario_id', $egresso->id);
                                })
                                ->orWhere(function($q) use ($egressoLogado, $egresso) {
                                    $q->where('solicitante_id', $egresso->id)
                                      ->where('destinatario_id', $egressoLogado->id);
                                })
                                ->where('status', 'pendente')
                                ->exists();

        // Buscar localização atual
        $localizacao = Localizacao::where('egresso_id', $egresso->id)
            ->where('is_current', true)
            ->first();

        // Buscar histórico profissional (apenas se for da rede, ou mostrar resumo)
        $profissional = null;
        if ($isConexao) {
            $profissional = Profissional::where('egresso_id', $egresso->id)
                ->orderBy('is_current', 'desc')
                ->orderBy('data_inicio', 'desc')
                ->get();
        } else {
            // Se não for da rede, mostrar apenas o cargo atual (se tiver)
            $profissional = Profissional::where('egresso_id', $egresso->id)
                ->where('is_current', true)
                ->orderBy('data_inicio', 'desc')
                ->get();
        }

        // Buscar publicações do egresso (apenas as mais recentes)
        $publicacoes = Publicacao::where('egresso_id', $egresso->id)
            ->with(['egresso', 'comentarios', 'curtidas'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Adicionar contagem de curtidas e comentários
        foreach ($publicacoes as $pub) {
            $pub->total_curtidas = $pub->curtidas->count();
            $pub->total_comentarios = $pub->comentarios->count();
            $pub->curtiu = DB::table('curtidas')
                ->where('publicacao_id', $pub->id)
                ->where('egresso_id', $egressoLogado->id)
                ->exists();
        }

        // Estatísticas do egresso
        $stats = [
            'total_publicacoes' => Publicacao::where('egresso_id', $egresso->id)->count(),
            'total_comentarios' => Comentario::where('egresso_id', $egresso->id)->count(),
            'total_conexoes' => Conexao::where(function($q) use ($egresso) {
                                    $q->where('solicitante_id', $egresso->id)
                                      ->orWhere('destinatario_id', $egresso->id);
                                })
                                ->where('status', 'aceito')
                                ->count(),
            'tem_localizacao' => Localizacao::where('egresso_id', $egresso->id)
                                    ->where('is_current', true)
                                    ->exists(),
        ];

        return view('egresso.egressos.show', compact(
            'egresso',
            'isProprio',
            'isConexao',
            'pedidoPendente',
            'localizacao',
            'profissional',
            'publicacoes',
            'stats'
        ));
    }

    /**
     * Buscar egressos para autocomplete (AJAX)
     */
    public function buscar(Request $request)
    {
        $termo = $request->input('q', '');
        $egressoLogado = Auth::user()->egresso;

        if (empty($termo) || strlen($termo) < 2) {
            return response()->json([]);
        }

        $egressos = Egresso::where('id', '!=', $egressoLogado->id)
            ->where(function($q) use ($termo) {
                $q->where('nome_completo', 'LIKE', "%{$termo}%")
                  ->orWhere('numero_processo', 'LIKE', "%{$termo}%")
                  ->orWhere('email', 'LIKE', "%{$termo}%");
            })
            ->with(['curso', 'curso.unidade'])
            ->take(10)
            ->get()
            ->map(function($e) use ($egressoLogado) {
                // Verificar se é da rede
                $isConexao = Conexao::where(function($q) use ($egressoLogado, $e) {
                                        $q->where('solicitante_id', $egressoLogado->id)
                                          ->where('destinatario_id', $e->id);
                                    })
                                    ->orWhere(function($q) use ($egressoLogado, $e) {
                                        $q->where('solicitante_id', $e->id)
                                          ->where('destinatario_id', $egressoLogado->id);
                                    })
                                    ->where('status', 'aceito')
                                    ->exists();

                return [
                    'id' => $e->id,
                    'nome' => $e->nome_completo,
                    'numero_processo' => $e->numero_processo,
                    'curso' => $e->curso->nome ?? null,
                    'unidade' => $e->curso->unidade->sigla ?? null,
                    'foto' => $e->foto_url ? asset($e->foto_url) : null,
                    'is_conexao' => $isConexao,
                ];
            });

        return response()->json($egressos);
    }
}