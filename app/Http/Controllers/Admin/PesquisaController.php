<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesquisa;
use App\Models\PesquisaPergunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PesquisaResposta;

class PesquisaController extends Controller
{
    /**
     * Lista todas as pesquisas.
     */
    public function index()
    {
        $pesquisas = Pesquisa::withCount([
            'perguntas',
            'respostas',
        ])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.pesquisas.index', compact('pesquisas'));
    }

    /**
     * Formulário para criar uma nova pesquisa.
     */
    public function create()
    {
        return view('admin.pesquisas.create');
    }

    /**
     * Guarda uma nova pesquisa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'titulo' => 'required|string|max:200',
                'descricao' => 'nullable|string',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after:data_inicio',
            ],
            [
                'titulo.required' => 'O título da pesquisa é obrigatório.',
                'titulo.max' => 'O título não pode ultrapassar 200 caracteres.',
                'data_inicio.required' => 'A data de início é obrigatória.',
                'data_fim.required' => 'A data de término é obrigatória.',
                'data_fim.after' => 'A data de término deve ser posterior à data de início.',
            ]
        );

        $pesquisa = Pesquisa::create([
            'admin_id' => Auth::id(),
            'titulo' => trim($validated['titulo']),
            'descricao' => $validated['descricao'] ?? null,
            'data_inicio' => $validated['data_inicio'],
            'data_fim' => $validated['data_fim'],
            'ativa' => true,
        ]);

        return redirect()
            ->route('admin.pesquisas.perguntas', $pesquisa->id)
            ->with(
                'success',
                'Pesquisa criada com sucesso! Agora adicione as perguntas.'
            );
    }

    /**
     * Mostra as perguntas de uma pesquisa.
     */
    public function perguntas($id)
    {
        $pesquisa = Pesquisa::findOrFail($id);

        $perguntas = $pesquisa
            ->perguntas()
            ->orderBy('ordem')
            ->get();

        return view(
            'admin.pesquisas.perguntas',
            compact('pesquisa', 'perguntas')
        );
    }

    /**
     * Adiciona uma pergunta à pesquisa.
     *
     * Tipos disponíveis:
     * - texto
     * - sim_nao
     * - multipla_escolha
     * - selecao_multipla
     * - escala
     */
    public function addPergunta(Request $request, $pesquisaId)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDAR DADOS
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate(
            [
                'pergunta' => 'required|string|max:500',
                'tipo' => [
                    'required',
                    'in:texto,sim_nao,multipla_escolha,selecao_multipla,escala',
                ],
                'opcoes' => 'nullable|array',
                'opcoes.*' => 'nullable|string|max:255',
                'obrigatoria' => 'nullable|boolean',
            ],
            [
                'pergunta.required' => 'Digite a pergunta.',
                'pergunta.max' => 'A pergunta não pode ultrapassar 500 caracteres.',
                'tipo.required' => 'Selecione o tipo de resposta.',
                'tipo.in' => 'O tipo de resposta selecionado é inválido.',
                'opcoes.*.max' => 'Cada opção pode ter no máximo 255 caracteres.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | LOCALIZAR PESQUISA
        |--------------------------------------------------------------------------
        */
        $pesquisa = Pesquisa::findOrFail($pesquisaId);

        /*
        |--------------------------------------------------------------------------
        | PREPARAR OPÇÕES
        |--------------------------------------------------------------------------
        */
        $opcoes = null;

        switch ($validated['tipo']) {
            case 'texto':
                $opcoes = null;
                break;

            case 'sim_nao':
                $opcoes = ['Sim', 'Não'];
                break;

            case 'multipla_escolha':
            case 'selecao_multipla':
                $opcoes = $this->limparOpcoes(
                    $request->input('opcoes', [])
                );

                if (count($opcoes) < 2) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Adicione pelo menos 2 opções para perguntas de múltipla escolha.'
                        );
                }
                break;

            case 'escala':
                $opcoes = ['1', '2', '3', '4', '5'];
                break;

            default:
                $opcoes = null;
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | CONVERTER OPÇÕES PARA JSON
        |--------------------------------------------------------------------------
        */
        $opcoesJson = $opcoes !== null
            ? json_encode($opcoes, JSON_UNESCAPED_UNICODE)
            : null;

        /*
        |--------------------------------------------------------------------------
        | DEFINIR ORDEM DA PERGUNTA
        |--------------------------------------------------------------------------
        */
        $ultimaOrdem = $pesquisa
            ->perguntas()
            ->max('ordem');

        $ordem = ($ultimaOrdem ?? 0) + 1;

        /*
        |--------------------------------------------------------------------------
        | CRIAR PERGUNTA
        |--------------------------------------------------------------------------
        */
        PesquisaPergunta::create([
            'pesquisa_id' => $pesquisa->id,
            'pergunta' => trim($validated['pergunta']),
            'tipo' => $validated['tipo'],
            'opcoes' => $opcoesJson,
            'obrigatoria' => $request->boolean('obrigatoria'),
            'ordem' => $ordem,
        ]);

        /*
        |--------------------------------------------------------------------------
        | RETORNO
        |--------------------------------------------------------------------------
        */
        return back()->with(
            'success',
            '✅ Pergunta adicionada com sucesso!'
        );
    }

    /**
     * Limpa e organiza opções personalizadas.
     *
     * Remove:
     * - campos vazios;
     * - espaços desnecessários;
     * - opções duplicadas.
     */
    private function limparOpcoes(array $opcoes): array
    {
        $resultado = [];

        foreach ($opcoes as $opcao) {
            $opcao = trim($opcao);

            if ($opcao === '') {
                continue;
            }

            if (!in_array($opcao, $resultado, true)) {
                $resultado[] = $opcao;
            }
        }

        return array_values($resultado);
    }

    /**
     * Elimina uma pergunta.
     */
    public function destroyPergunta($id)
    {
        $pergunta = PesquisaPergunta::findOrFail($id);

        $pesquisaId = $pergunta->pesquisa_id;

        $pergunta->delete();

        /*
        |--------------------------------------------------------------------------
        | REORGANIZAR ORDEM DAS PERGUNTAS
        |--------------------------------------------------------------------------
        */
        $perguntas = PesquisaPergunta::where(
            'pesquisa_id',
            $pesquisaId
        )
            ->orderBy('ordem')
            ->get();

        foreach ($perguntas as $index => $item) {
            $item->update([
                'ordem' => $index + 1,
            ]);
        }

        return back()->with(
            'success',
            '✅ Pergunta eliminada com sucesso!'
        );
    }

    /**
     * Mostra os resultados da pesquisa.
     */
    /**
 * Mostra os resultados da pesquisa (com dados prontos para gráficos).
 */
public function resultados($id)
{
    $pesquisa = Pesquisa::withCount('respostas')->findOrFail($id);

    $perguntas = $pesquisa
        ->perguntas()
        ->orderBy('ordem')
        ->get();

    $dados = [];      // dados agregados por pergunta
    $tipos = [];      // tipo de cada pergunta (facilita na view)

    foreach ($perguntas as $pergunta) {

        $tipos[$pergunta->id] = $pergunta->tipo;

        // ---------------------------------------------------------
        // Perguntas de escolha / escala → agregação por resposta
        // ---------------------------------------------------------
        if (in_array($pergunta->tipo, [
            'sim_nao',
            'multipla_escolha',
            'selecao_multipla',
            'escala',
        ], true)) {

            $dados[$pergunta->id] = $pergunta
                ->respostas()
                ->select('resposta', DB::raw('COUNT(*) as total'))
                ->whereNotNull('resposta')
                ->where('resposta', '!=', '')
                ->groupBy('resposta')
                ->orderByDesc('total')
                ->get();

        // ---------------------------------------------------------
        // Texto livre → lista simples
        // ---------------------------------------------------------
        } elseif ($pergunta->tipo === 'texto') {

            $dados[$pergunta->id] = $pergunta
                ->respostas()
                ->select('resposta', 'created_at')
                ->whereNotNull('resposta')
                ->where('resposta', '!=', '')
                ->orderByDesc('created_at')
                ->get();
        }
    }

    // Total de egressos que já responderam (distintos)
    $totalParticipantes = PesquisaResposta::where('pesquisa_id', $pesquisa->id)
        ->distinct('egresso_id')
        ->count('egresso_id');

    return view('admin.pesquisas.resultados', compact(
        'pesquisa',
        'perguntas',
        'dados',
        'tipos',
        'totalParticipantes'
    ));
}

    /**
     * Formulário de edição da pesquisa.
     */
    public function edit($id)
    {
        $pesquisa = Pesquisa::findOrFail($id);

        return view(
            'admin.pesquisas.edit',
            compact('pesquisa')
        );
    }

    /**
     * Atualiza uma pesquisa.
     */
    public function update(Request $request, $id)
    {
        $pesquisa = Pesquisa::findOrFail($id);

        $validated = $request->validate(
            [
                'titulo' => 'required|string|max:200',
                'descricao' => 'nullable|string',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after:data_inicio',
                'ativa' => 'nullable|boolean',
            ],
            [
                'titulo.required' => 'O título da pesquisa é obrigatório.',
                'titulo.max' => 'O título não pode ultrapassar 200 caracteres.',
                'data_inicio.required' => 'A data de início é obrigatória.',
                'data_fim.required' => 'A data de término é obrigatória.',
                'data_fim.after' => 'A data de término deve ser posterior à data de início.',
            ]
        );

        $pesquisa->update([
            'titulo' => trim($validated['titulo']),
            'descricao' => $validated['descricao'] ?? null,
            'data_inicio' => $validated['data_inicio'],
            'data_fim' => $validated['data_fim'],
            'ativa' => $request->boolean('ativa'),
        ]);

        return redirect()
            ->route('admin.pesquisas.index')
            ->with(
                'success',
                '✅ Pesquisa atualizada com sucesso!'
            );
    }

    /**
     * Elimina uma pesquisa.
     */
    public function destroy($id)
    {
        $pesquisa = Pesquisa::findOrFail($id);

        $pesquisa->delete();

        return redirect()
            ->route('admin.pesquisas.index')
            ->with(
                'success',
                '✅ Pesquisa eliminada com sucesso!'
            );
    }
}