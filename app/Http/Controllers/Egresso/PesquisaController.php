<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Pesquisa;
use App\Models\PesquisaResposta;
use App\Helpers\NotificarAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PesquisaController extends Controller
{
    public function index()
    {
        $egresso = Auth::user()->egresso;

        $pesquisas = Pesquisa::where('ativa', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $respostas = PesquisaResposta::where('egresso_id', $egresso->id)
            ->pluck('pesquisa_id')
            ->unique()
            ->toArray();

        return view('egresso.pesquisas.index', compact('pesquisas', 'respostas'));
    }

    public function responder($id)
    {
        $pesquisa = Pesquisa::with(['perguntas'])->findOrFail($id);

        $egresso = Auth::user()->egresso;

        $jaRespondeu = PesquisaResposta::where('pesquisa_id', $id)
            ->where('egresso_id', $egresso->id)
            ->exists();

        if ($jaRespondeu) {
            return redirect()->route('egresso.pesquisas.index')
                ->with('error', 'Você já respondeu esta pesquisa.');
        }

        $perguntas = $pesquisa->perguntas()->orderBy('ordem')->get();

        return view('egresso.pesquisas.responder', compact('pesquisa', 'perguntas'));
    }

    public function salvar(Request $request, $id)
    {
        $pesquisa = Pesquisa::findOrFail($id);
        $egresso = Auth::user()->egresso;

        $jaRespondeu = PesquisaResposta::where('pesquisa_id', $id)
            ->where('egresso_id', $egresso->id)
            ->exists();

        if ($jaRespondeu) {
            return redirect()->route('egresso.pesquisas.index')
                ->with('error', 'Você já respondeu esta pesquisa.');
        }

        $perguntas = $pesquisa->perguntas()->get();

        DB::beginTransaction();

        try {
            foreach ($perguntas as $pergunta) {
                $resposta = $request->input("resposta.{$pergunta->id}");

                if ($pergunta->obrigatoria && empty($resposta)) {
                    DB::rollBack();
                    return back()->with('error', 'Por favor, responda todas as perguntas obrigatórias.');
                }

                if (!empty($resposta)) {
                    $respostaTexto = is_array($resposta) ? implode(', ', $resposta) : $resposta;

                    PesquisaResposta::create([
                        'pesquisa_id' => $pesquisa->id,
                        'egresso_id' => $egresso->id,
                        'pergunta_id' => $pergunta->id,
                        'resposta' => $respostaTexto,
                    ]);
                }
            }

            /* =========================================================
               🔔 NOTIFICAR ADMIN — NOVO
               ========================================================= */
            NotificarAdmin::todos(
                'Nova resposta em pesquisa',
                $egresso->nome_completo . ' respondeu à pesquisa "' . $pesquisa->titulo . '".',
                'sistema',
                route('admin.pesquisas.resultados', $pesquisa->id)
            );
            /* ========================================================= */

            DB::commit();

            return redirect()->route('egresso.pesquisas.index')
                ->with('success', 'Respostas enviadas com sucesso! Obrigado pela participação.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao enviar respostas. Tente novamente.');
        }
    }
}