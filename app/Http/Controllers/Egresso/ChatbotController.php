<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Página do chatbot.
     */
    public function index()
    {
        return view('egresso.chatbot.index');
    }

    /**
     * Recebe e interpreta a mensagem do egresso.
     */
    public function enviar(Request $request): JsonResponse
    {
        $request->validate([
            'mensagem' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],
        ]);

        try {
            $mensagemOriginal = trim($request->input('mensagem'));

            $mensagem = $this->normalizarTexto($mensagemOriginal);

            if ($mensagem === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor, escreva uma pergunta.',
                ], 422);
            }

            /*
             * Identifica a intenção da pergunta.
             */
            $analise = $this->identificarIntencao($mensagem);

            /*
             * Gera a resposta correspondente.
             */
            $resposta = $this->gerarResposta(
                $analise['intencao'],
                $mensagemOriginal,
                $analise
            );

            /*
             * Registo simples para facilitar diagnóstico.
             */
            Log::info('Chatbot Egresso', [
                'user_id' => Auth::id(),
                'mensagem' => $mensagemOriginal,
                'mensagem_normalizada' => $mensagem,
                'intencao' => $analise['intencao'],
                'pontuacao' => $analise['pontuacao'],
            ]);

            return response()->json([
                'success' => true,
                'resposta' => $resposta,
                'intencao' => $analise['intencao'],
                'confidence' => $analise['confidence'],
            ]);

        } catch (\Throwable $e) {

            Log::error('Erro no Chatbot Egresso', [
                'user_id' => Auth::id(),
                'mensagem' => $request->input('mensagem'),
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um problema ao processar a sua pergunta. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Verifica novas mensagens/notificações relacionadas ao chatbot.
     */
    public function verificarNovas(): JsonResponse
    {
        /*
         * Mantemos este método leve.
         *
         * Não fazemos consultas pesadas aqui.
         * O objetivo é apenas permitir que o frontend
         * verifique se existem novas mensagens.
         */

        return response()->json([
            'success' => true,
            'total' => 0,
        ]);
    }

    // ============================================================
    // NORMALIZAÇÃO
    // ============================================================

    /**
     * Normaliza o texto antes da análise.
     *
     * Exemplo:
     *
     * "Como Utilizar o Sistema?"
     *
     * vira:
     *
     * "como utilizar o sistema"
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = Str::lower(trim($texto));

        /*
         * Remove acentos.
         */
        $texto = Str::ascii($texto);

        /*
         * Remove pontuação desnecessária.
         */
        $texto = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texto);

        /*
         * Remove espaços duplicados.
         */
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

    // ============================================================
    // IDENTIFICAÇÃO DA INTENÇÃO
    // ============================================================

    /**
     * Identifica o assunto principal da pergunta.
     */
    private function identificarIntencao(string $mensagem): array
    {
        $intencoes = $this->baseDeIntencoes();

        $resultados = [];

        foreach ($intencoes as $nome => $dados) {

            $pontuacao = 0;

            /*
             * Frases completas têm maior peso.
             */
            foreach ($dados['frases'] as $frase) {

                $fraseNormalizada = $this->normalizarTexto($frase);

                if (
                    $mensagem === $fraseNormalizada ||
                    Str::contains($mensagem, $fraseNormalizada)
                ) {
                    $pontuacao += 10;
                }
            }

            /*
             * Palavras importantes.
             */
            foreach ($dados['palavras'] as $palavra) {

                $palavraNormalizada = $this->normalizarTexto($palavra);

                if ($this->possuiPalavra($mensagem, $palavraNormalizada)) {
                    $pontuacao += 2;
                }
            }

            /*
             * Combinações de palavras.
             */
            foreach ($dados['combinacoes'] as $combinacao) {

                $encontradas = 0;

                foreach ($combinacao as $palavra) {

                    $palavraNormalizada = $this->normalizarTexto($palavra);

                    if ($this->possuiPalavra($mensagem, $palavraNormalizada)) {
                        $encontradas++;
                    }
                }

                if ($encontradas >= 2) {
                    $pontuacao += 6;
                }
            }

            $resultados[$nome] = $pontuacao;
        }

        /*
         * Ordena da maior pontuação para a menor.
         */
        arsort($resultados);

        $melhorIntencao = array_key_first($resultados);
        $melhorPontuacao = $resultados[$melhorIntencao] ?? 0;

        /*
         * Se a pontuação for muito baixa,
         * verificamos intenções especiais.
         */
        if ($melhorPontuacao < 2) {
            $melhorIntencao = $this->detectarIntencaoEspecial($mensagem);
            $melhorPontuacao = 1;
        }

        /*
         * Calcula uma confiança simples.
         */
        $confidence = min(
            100,
            max(
                20,
                $melhorPontuacao * 5
            )
        );

        return [
            'intencao' => $melhorIntencao,
            'pontuacao' => $melhorPontuacao,
            'confidence' => $confidence,
            'ranking' => $resultados,
        ];
    }

    /**
     * Verifica se uma palavra existe realmente no texto.
     */
    private function possuiPalavra(string $texto, string $palavra): bool
    {
        if ($palavra === '') {
            return false;
        }

        return preg_match(
            '/(?<!\p{L})' . preg_quote($palavra, '/') . '(?!\p{L})/u',
            $texto
        ) === 1;
    }

    // ============================================================
    // BASE DE INTENÇÕES
    // ============================================================

    private function baseDeIntencoes(): array
    {
        return [

            // ----------------------------------------------------
            // SAUDAÇÃO
            // ----------------------------------------------------

            'saudacao' => [

                'frases' => [
                    'ola',
                    'ola chatbot',
                    'bom dia',
                    'boa tarde',
                    'boa noite',
                    'como estas',
                    'como voce esta',
                    'tudo bem',
                    'ola tudo bem',
                    'oi',
                    'oi chatbot',
                ],

                'palavras' => [
                    'ola',
                    'oi',
                    'bom',
                    'boa',
                ],

                'combinacoes' => [
                    ['bom', 'dia'],
                    ['boa', 'tarde'],
                    ['boa', 'noite'],
                    ['tudo', 'bem'],
                ],
            ],

            // ----------------------------------------------------
            // AJUDA / COMO UTILIZAR O SISTEMA
            // ----------------------------------------------------

            'ajuda_sistema' => [

                'frases' => [
                    'como utilizar o sistema',
                    'como usar o sistema',
                    'como utilizar a plataforma',
                    'como usar a plataforma',
                    'como funciona o sistema',
                    'como funciona a plataforma',
                    'como funciona este sistema',
                    'como funciona esta plataforma',
                    'ensina me a usar o sistema',
                    'ensina me a utilizar o sistema',
                    'preciso de ajuda',
                    'preciso de ajuda com o sistema',
                    'quero ajuda',
                    'ajuda com o sistema',
                    'o que posso fazer aqui',
                    'como posso usar o sistema',
                    'como posso utilizar o sistema',
                    'quero saber como funciona',
                    'explique me como funciona',
                    'podes explicar o sistema',
                    'pode explicar o sistema',
                ],

                'palavras' => [
                    'ajuda',
                    'utilizar',
                    'utilizo',
                    'usar',
                    'uso',
                    'funciona',
                    'funcionamento',
                    'plataforma',
                    'sistema',
                    'explicar',
                    'explica',
                    'ensinar',
                    'ensina',
                ],

                'combinacoes' => [
                    ['como', 'utilizar'],
                    ['como', 'usar'],
                    ['como', 'funciona'],
                    ['ajuda', 'sistema'],
                    ['ajuda', 'plataforma'],
                    ['usar', 'plataforma'],
                    ['utilizar', 'plataforma'],
                ],
            ],

            // ----------------------------------------------------
            // OPORTUNIDADES
            // ----------------------------------------------------

            'oportunidades' => [

                'frases' => [
                    'quero ver oportunidades',
                    'onde vejo oportunidades',
                    'onde estão as oportunidades',
                    'como ver oportunidades',
                    'procurar oportunidades',
                    'ver oportunidades de emprego',
                    'quero procurar emprego',
                    'quero encontrar emprego',
                    'há oportunidades disponíveis',
                    'existem oportunidades disponíveis',
                ],

                'palavras' => [
                    'oportunidade',
                    'oportunidades',
                    'emprego',
                    'empregos',
                    'vaga',
                    'vagas',
                    'trabalho',
                    'trabalhos',
                    'recrutamento',
                ],

                'combinacoes' => [
                    ['ver', 'oportunidades'],
                    ['procurar', 'emprego'],
                    ['encontrar', 'emprego'],
                    ['vaga', 'emprego'],
                    ['oportunidades', 'emprego'],
                ],
            ],

            // ----------------------------------------------------
            // CANDIDATURAS
            // ----------------------------------------------------

            'candidaturas' => [

                'frases' => [
                    'como me candidatar',
                    'como posso me candidatar',
                    'como fazer uma candidatura',
                    'como faço uma candidatura',
                    'quero me candidatar',
                    'quero candidatar me',
                    'onde vejo minhas candidaturas',
                    'onde vejo as minhas candidaturas',
                    'como acompanhar candidatura',
                    'como acompanhar minhas candidaturas',
                    'qual o estado da minha candidatura',
                    'qual o estado das minhas candidaturas',
                    'quero ver minhas candidaturas',
                    'quero ver as minhas candidaturas',
                ],

                'palavras' => [
                    'candidatura',
                    'candidaturas',
                    'candidatar',
                    'candidatei',
                    'candidato',
                    'candidatarse',
                ],

                'combinacoes' => [
                    ['como', 'candidatar'],
                    ['fazer', 'candidatura'],
                    ['ver', 'candidaturas'],
                    ['estado', 'candidatura'],
                    ['acompanhar', 'candidatura'],
                ],
            ],

            // ----------------------------------------------------
            // PERFIL
            // ----------------------------------------------------

            'perfil' => [

                'frases' => [
                    'como editar o meu perfil',
                    'como editar meu perfil',
                    'como atualizar o meu perfil',
                    'como atualizar meu perfil',
                    'quero editar o meu perfil',
                    'quero alterar o meu perfil',
                    'como alterar os meus dados',
                    'como alterar meus dados',
                    'quero atualizar os meus dados',
                    'quero mudar meus dados',
                    'onde fica o meu perfil',
                ],

                'palavras' => [
                    'perfil',
                    'dados',
                    'editar',
                    'editar',
                    'atualizar',
                    'alterar',
                    'mudar',
                ],

                'combinacoes' => [
                    ['editar', 'perfil'],
                    ['atualizar', 'perfil'],
                    ['alterar', 'perfil'],
                    ['mudar', 'perfil'],
                    ['editar', 'dados'],
                    ['atualizar', 'dados'],
                ],
            ],

            // ----------------------------------------------------
            // NOTIFICAÇÕES
            // ----------------------------------------------------

            'notificacoes' => [

                'frases' => [
                    'onde vejo notificações',
                    'onde estão as notificações',
                    'como ver notificações',
                    'quero ver notificações',
                    'tenho notificações',
                    'há novas notificações',
                    'como funcionam as notificações',
                ],

                'palavras' => [
                    'notificação',
                    'notificações',
                    'aviso',
                    'avisos',
                    'alerta',
                    'alertas',
                ],

                'combinacoes' => [
                    ['ver', 'notificações'],
                    ['novas', 'notificações'],
                    ['notificações', 'avisos'],
                ],
            ],

            // ----------------------------------------------------
            // EVENTOS
            // ----------------------------------------------------

            'eventos' => [

                'frases' => [
                    'quero ver eventos',
                    'onde vejo eventos',
                    'onde estão os eventos',
                    'como ver eventos',
                    'há eventos',
                    'existem eventos',
                    'quais são os eventos',
                    'quais eventos estão disponíveis',
                    'como participar num evento',
                    'como participar em eventos',
                ],

                'palavras' => [
                    'evento',
                    'eventos',
                    'participar',
                    'atividade',
                    'atividades',
                ],

                'combinacoes' => [
                    ['ver', 'eventos'],
                    ['participar', 'evento'],
                    ['participar', 'eventos'],
                    ['eventos', 'disponíveis'],
                ],
            ],

            // ----------------------------------------------------
            // REDE DE CONTACTOS
            // ----------------------------------------------------

            'rede' => [

                'frases' => [
                    'como funciona a rede',
                    'como funciona a rede de contactos',
                    'quero ver a rede',
                    'quero conectar com outros egressos',
                    'como encontrar outros egressos',
                    'como fazer contactos',
                    'como adicionar contactos',
                    'como encontrar colegas',
                ],

                'palavras' => [
                    'rede',
                    'contactos',
                    'contatos',
                    'conectar',
                    'conexão',
                    'conexoes',
                    'colegas',
                    'egressos',
                ],

                'combinacoes' => [
                    ['rede', 'contactos'],
                    ['rede', 'contatos'],
                    ['conectar', 'egressos'],
                    ['encontrar', 'egressos'],
                    ['fazer', 'contactos'],
                ],
            ],

            // ----------------------------------------------------
            // MENSAGENS
            // ----------------------------------------------------

            'mensagens' => [

                'frases' => [
                    'como enviar mensagem',
                    'como enviar uma mensagem',
                    'onde estão as mensagens',
                    'como ver mensagens',
                    'quero enviar mensagem',
                    'quero mandar mensagem',
                    'como falar com outro egresso',
                ],

                'palavras' => [
                    'mensagem',
                    'mensagens',
                    'conversa',
                    'conversas',
                    'enviar',
                    'mandar',
                    'falar',
                ],

                'combinacoes' => [
                    ['enviar', 'mensagem'],
                    ['mandar', 'mensagem'],
                    ['ver', 'mensagens'],
                    ['mensagem', 'egresso'],
                ],
            ],

            // ----------------------------------------------------
            // SERVIÇOS
            // ----------------------------------------------------

            'servicos' => [

                'frases' => [
                    'quais serviços estão disponíveis',
                    'onde vejo os serviços',
                    'como solicitar um serviço',
                    'como pedir um serviço',
                    'quero solicitar um serviço',
                    'quero ver serviços',
                    'como funcionam os serviços',
                ],

                'palavras' => [
                    'serviço',
                    'serviços',
                    'solicitar',
                    'pedido',
                    'pedidos',
                ],

                'combinacoes' => [
                    ['ver', 'serviços'],
                    ['solicitar', 'serviço'],
                    ['pedir', 'serviço'],
                    ['serviço', 'disponível'],
                ],
            ],

            // ----------------------------------------------------
            // PESQUISAS
            // ----------------------------------------------------

            'pesquisas' => [

                'frases' => [
                    'onde estão as pesquisas',
                    'como responder pesquisa',
                    'como responder uma pesquisa',
                    'quero responder pesquisa',
                    'quero ver pesquisas',
                    'há pesquisas disponíveis',
                ],

                'palavras' => [
                    'pesquisa',
                    'pesquisas',
                    'questionário',
                    'questionarios',
                    'responder',
                ],

                'combinacoes' => [
                    ['responder', 'pesquisa'],
                    ['ver', 'pesquisas'],
                    ['pesquisas', 'disponíveis'],
                ],
            ],

            // ----------------------------------------------------
            // FEEDBACK
            // ----------------------------------------------------

            'feedback' => [

                'frases' => [
                    'como enviar feedback',
                    'como dar feedback',
                    'quero enviar feedback',
                    'quero dar uma sugestão',
                    'como fazer uma reclamação',
                    'quero fazer uma reclamação',
                ],

                'palavras' => [
                    'feedback',
                    'sugestão',
                    'sugestões',
                    'reclamação',
                    'reclamações',
                    'opinião',
                ],

                'combinacoes' => [
                    ['enviar', 'feedback'],
                    ['dar', 'feedback'],
                    ['enviar', 'sugestão'],
                    ['fazer', 'reclamação'],
                ],
            ],

            // ----------------------------------------------------
            // MAPA
            // ----------------------------------------------------

            'mapa' => [

                'frases' => [
                    'como usar o mapa',
                    'onde fica o mapa',
                    'quero ver o mapa',
                    'como encontrar egressos no mapa',
                    'como procurar alguém no mapa',
                ],

                'palavras' => [
                    'mapa',
                    'localização',
                    'localizacao',
                    'cidade',
                    'pais',
                ],

                'combinacoes' => [
                    ['ver', 'mapa'],
                    ['usar', 'mapa'],
                    ['encontrar', 'mapa'],
                    ['egressos', 'mapa'],
                ],
            ],

            // ----------------------------------------------------
            // MURAL
            // ----------------------------------------------------

            'mural' => [

                'frases' => [
                    'onde fica o mural',
                    'quero ver o mural',
                    'como ver notícias',
                    'onde estão as notícias',
                    'quero ver notícias',
                ],

                'palavras' => [
                    'mural',
                    'notícia',
                    'notícias',
                    'publicação',
                    'publicações',
                ],

                'combinacoes' => [
                    ['ver', 'mural'],
                    ['ver', 'notícias'],
                    ['mural', 'notícias'],
                ],
            ],

            // ----------------------------------------------------
            // CHATBOT
            // ----------------------------------------------------

            'chatbot' => [

                'frases' => [
                    'o que és',
                    'quem és',
                    'o que voce faz',
                    'o que podes fazer',
                    'o que pode fazer',
                    'como funciona o chatbot',
                    'como funciona este chatbot',
                    'para que serve o chatbot',
                    'para que serves',
                ],

                'palavras' => [
                    'chatbot',
                    'assistente',
                    'assistente virtual',
                    'ia',
                    'inteligência',
                    'inteligencia',
                ],

                'combinacoes' => [
                    ['chatbot', 'funciona'],
                    ['chatbot', 'fazer'],
                    ['assistente', 'fazer'],
                ],
            ],
        ];
    }

    // ============================================================
    // INTENÇÕES ESPECIAIS
    // ============================================================

    private function detectarIntencaoEspecial(string $mensagem): string
    {
        /*
         * Perguntas extremamente curtas.
         */

        if (
            $this->possuiPalavra($mensagem, 'ajuda') ||
            $this->possuiPalavra($mensagem, 'socorro')
        ) {
            return 'ajuda_sistema';
        }

        if (
            $this->possuiPalavra($mensagem, 'ola') ||
            $this->possuiPalavra($mensagem, 'oi')
        ) {
            return 'saudacao';
        }

        return 'desconhecida';
    }

    // ============================================================
    // RESPOSTAS
    // ============================================================

    private function gerarResposta(
        string $intencao,
        string $mensagemOriginal,
        array $analise = []
    ): string {

        switch ($intencao) {

            case 'saudacao':
                return $this->respostaSaudacao();

            case 'ajuda_sistema':
                return $this->respostaAjudaSistema();

            case 'oportunidades':
                return $this->respostaOportunidades();

            case 'candidaturas':
                return $this->respostaCandidaturas();

            case 'perfil':
                return $this->respostaPerfil();

            case 'notificacoes':
                return $this->respostaNotificacoes();

            case 'eventos':
                return $this->respostaEventos();

            case 'rede':
                return $this->respostaRede();

            case 'mensagens':
                return $this->respostaMensagens();

            case 'servicos':
                return $this->respostaServicos();

            case 'pesquisas':
                return $this->respostaPesquisas();

            case 'feedback':
                return $this->respostaFeedback();

            case 'mapa':
                return $this->respostaMapa();

            case 'mural':
                return $this->respostaMural();

            case 'chatbot':
                return $this->respostaChatbot();

            default:
                return $this->respostaDesconhecida();
        }
    }

    // ============================================================
    // RESPOSTAS ESPECÍFICAS
    // ============================================================

    private function respostaSaudacao(): string
    {
        return
            "Ola! Seja bem-vindo ao Alumni Track.

Eu sou o assistente virtual da plataforma e posso ajuda-lo a encontrar informacoes e utilizar as principais funcionalidades do sistema.

Pode perguntar, por exemplo:
- Como utilizar o sistema?
- Como me candidatar a uma oportunidade?
- Onde vejo as minhas candidaturas?
- Como editar o meu perfil?
- Como vejo os eventos?

Como posso ajuda-lo?";
    }

    private function respostaAjudaSistema(): string
    {
        return
            "Claro! Vou explicar como utilizar o Alumni Track.

Principais funcionalidades:

Oportunidades
Consulte oportunidades de emprego disponiveis e veja os detalhes de cada uma.

Candidaturas
Depois de se candidatar a uma oportunidade, pode acompanhar as suas candidaturas e consultar o estado delas.

Perfil
Consulte e atualize os seus dados pessoais, academicos e profissionais.

Notificacoes
Consulte avisos e atualizacoes relacionados com a sua conta e com a plataforma.

Eventos
Consulte os eventos disponiveis e veja as informacoes de cada evento.

Rede de contactos
Encontre outros egressos e estabeleca contactos atraves da plataforma.

Mensagens
Utilize o sistema de mensagens para comunicar com outros utilizadores quando essa funcionalidade estiver disponivel para si.

Pesquisas
Consulte e responda as pesquisas disponibilizadas pela plataforma.

Pode perguntar diretamente:
- \"Como me candidato?\"
- \"Como editar o meu perfil?\"
- \"Onde vejo oportunidades?\"
- \"Onde vejo as minhas candidaturas?\"
- \"Como vejo os eventos?\"
- \"Como funciona a rede?\"

Estou pronto para ajuda-lo.";
    }

    private function respostaOportunidades(): string
    {
        return
            "Oportunidades

Na area de Oportunidades pode consultar ofertas disponiveis no sistema.

Para procurar uma oportunidade:
1. Abra Oportunidades.
2. Consulte a lista disponivel.
3. Abra a oportunidade que lhe interessa.
4. Leia os requisitos e informacoes.
5. Se cumprir os requisitos, pode selecionar Candidatar-se.

Se quiser, tambem posso explicar como fazer uma candidatura passo a passo.";
    }

    private function respostaCandidaturas(): string
    {
        return
            "Candidaturas

Para se candidatar a uma oportunidade:

1. Entre em Oportunidades.
2. Escolha a oportunidade pretendida.
3. Consulte os requisitos e o prazo.
4. Clique em Candidatar-se.
5. Confirme a candidatura, quando solicitado.

Depois pode consultar o resultado em Minhas Candidaturas.

Os estados da candidatura podem variar conforme o processamento realizado pela instituicao.";
    }

    private function respostaPerfil(): string
    {
        return
            "Perfil

Na area de Perfil pode consultar e atualizar os seus dados.

Normalmente podera encontrar informacoes como:
- Dados pessoais
- Formacao academica
- Informacao profissional
- Localizacao

Para atualizar os seus dados, entre no seu Perfil e procure a opcao de edicao.";
    }

    private function respostaNotificacoes(): string
    {
        return
            "Notificacoes

As notificacoes servem para apresentar avisos e atualizacoes importantes da plataforma.

Abra Notificacoes para consultar as mensagens disponiveis.

Recomendo verificar regularmente esta area para nao perder atualizacoes importantes.";
    }

    private function respostaEventos(): string
    {
        return
            "Eventos

Na area de Eventos pode consultar as atividades disponibilizadas pela plataforma.

Pode encontrar informacoes como:
- Nome do evento
- Data
- Local
- Descricao
- Outras informacoes relevantes

Abra Eventos para consultar os eventos disponiveis.";
    }

    private function respostaRede(): string
    {
        return
            "Rede de contactos

A Rede permite encontrar outros egressos e estabelecer contactos dentro da plataforma.

Pode utilizar esta funcionalidade para:
- Encontrar colegas;
- Conhecer outros egressos;
- Estabelecer contactos profissionais;
- Consultar perfis publicos disponiveis.

Abra Rede para comecar.";
    }

    private function respostaMensagens(): string
    {
        return
            "Mensagens

A area de Mensagens permite comunicar com outros utilizadores da plataforma.

Abra Mensagens para consultar as suas conversas ou iniciar uma nova conversa, quando disponivel.

Se quiser, posso tambem explicar como funciona a comunicacao entre egressos.";
    }

    private function respostaServicos(): string
    {
        return
            "Servicos

Na area de Servicos pode consultar os servicos disponibilizados pela plataforma e, quando aplicavel, fazer uma solicitacao.

Abra Servicos para consultar as opcoes disponiveis.

Se ja fez uma solicitacao, procure tambem a area de Meus Pedidos.";
    }

    private function respostaPesquisas(): string
    {
        return
            "Pesquisas

A area de Pesquisas permite consultar questionarios disponibilizados pela plataforma.

Quando existir uma pesquisa disponivel:
1. Abra Pesquisas.
2. Escolha a pesquisa.
3. Responda as perguntas.
4. Guarde/envie as respostas.

Algumas pesquisas podem estar disponiveis apenas durante determinado periodo.";
    }

    private function respostaFeedback(): string
    {
        return
            "Feedback

O feedback permite partilhar opinioes, sugestoes ou comunicar problemas relacionados com a plataforma.

Procure a area de Feedback para enviar a sua opiniao.

Quanto mais clara for a sua descricao, mais facil sera compreender e analisar a situacao.";
    }

    private function respostaMapa(): string
    {
        return
            "Mapa

O mapa permite consultar informacoes de localizacao disponiveis na plataforma.

Pode utiliza-lo para procurar egressos ou visualizar localizacoes disponibilizadas pelo sistema.

Abra Mapa para utilizar essa funcionalidade.";
    }

    private function respostaMural(): string
    {
        return
            "Mural

O Mural reune publicacoes e noticias disponibilizadas na plataforma.

Abra Mural para consultar as publicacoes disponiveis.

E uma boa area para acompanhar novidades e informacoes divulgadas no sistema.";
    }

    private function respostaChatbot(): string
    {
        return
            "Sobre mim

Sou o assistente virtual do Alumni Track.

Posso ajuda-lo a compreender e utilizar funcionalidades como:
Oportunidades
Candidaturas
Perfil
Notificacoes
Eventos
Rede de contactos
Mensagens
Servicos
Pesquisas
Mapa
Mural

Faca uma pergunta normalmente. Nao precisa utilizar exatamente as mesmas palavras que aparecem nos menus.";
    }

    private function respostaDesconhecida(): string
    {
        return
            "Ainda nao consegui identificar exatamente o que pretende saber.

Mas posso ajuda-lo com estas areas:

Oportunidades - procurar ofertas e oportunidades.
Candidaturas - candidatar-se e acompanhar candidaturas.
Perfil - consultar e atualizar os seus dados.
Notificacoes - consultar avisos.
Eventos - consultar eventos.
Rede - encontrar egressos e contactos.
Mensagens - comunicar com outros utilizadores.
Servicos - consultar e solicitar servicos.
Pesquisas - responder questionarios.
Mapa - consultar localizacoes.
Mural - consultar noticias e publicacoes.

Experimente perguntar de forma natural, por exemplo:
- \"Como utilizar o sistema?\"
- \"Como me candidato?\"
- \"Onde vejo minhas candidaturas?\"
- \"Como atualizo meu perfil?\"";
    }
}