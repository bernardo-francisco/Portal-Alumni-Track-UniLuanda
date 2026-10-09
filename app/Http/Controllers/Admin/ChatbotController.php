<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Processar pergunta enviada pelo administrador.
     */
    public function enviar(Request $request)
    {
        $admin = Auth::user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Nao autorizado.',
            ], 403);
        }

        $request->validate([
            'mensagem' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        try {

            $mensagemOriginal = trim(
                (string) $request->input('mensagem')
            );

            $mensagem = $this->normalizarPergunta(
                $mensagemOriginal
            );

            $intencao = $this->detectarIntencao(
                $mensagem
            );

            $resposta = $this->respostaPorIntencao(
                $intencao,
                $mensagemOriginal
            );

            Log::info('Chatbot Admin', [
                'admin_id' => $admin->id,
                'pergunta' => $mensagemOriginal,
                'normalizada' => $mensagem,
                'intencao' => $intencao,
            ]);

            return response()->json([
                'success' => true,
                'resposta' => [
                    'mensagem' => $resposta,
                    'intencao' => $intencao,
                ],
            ]);

        } catch (\Throwable $e) {

            Log::error('Erro no Chatbot Admin', [
                'admin_id' => $admin->id,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                    'Nao foi possivel processar a sua pergunta. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Verificar novas notificacoes do chatbot.
     */
    public function verificarNovas()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'total' => 0,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'total' => 0,
        ]);
    }

    /**
     * Normalizar pergunta.
     */
    private function normalizarPergunta(string $texto): string
    {
        $texto = Str::lower($texto);
        $texto = Str::ascii($texto);

        $texto = preg_replace(
            '/[^\p{L}\p{N}\s]/u',
            ' ',
            $texto
        );

        $texto = preg_replace(
            '/\s+/',
            ' ',
            $texto
        );

        return trim($texto);
    }

    /**
     * Detectar intencao da pergunta.
     * 
     * Ordem importante: Intencoes mais especificas primeiro,
     * depois as mais genericas.
     */
    private function detectarIntencao(string $pergunta): string
    {
        // ============================================================
        // 1. SAUDACAO
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'ola', 'oi', 'bom dia', 'boa tarde', 'boa noite',
            'ola assistente', 'ola chatbot'
        ])) {
            return 'saudacao';
        }

        // ============================================================
        // 2. AGRADECIMENTO
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'obrigado', 'obrigada', 'muito obrigado', 'muito obrigada',
            'agradeco', 'agradecido', 'agradecida'
        ])) {
            return 'agradecimento';
        }

        // ============================================================
        // 3. AJUDA
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'ajuda', 'preciso de ajuda', 'como utilizar', 'como usar',
            'como funciona', 'ajuda com o sistema', 'o que posso fazer'
        ])) {
            return 'ajuda_sistema';
        }

        // ============================================================
        // 4. OPORTUNIDADES (Mais especifico, vem antes de Unidades)
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'oportunidade',
            'oportunidades',
            'vaga',
            'vagas',
            'emprego',
            'empregos',
        ])) {
            return 'oportunidades';
        }

        // ============================================================
        // 5. VALIDACAO
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'validar egresso', 'validar um egresso', 'validacao',
            'validar cadastro', 'aprovar egresso', 'aprovar cadastro',
            'rejeitar egresso', 'rejeitar cadastro'
        ])) {
            return 'validacao';
        }

        // ============================================================
        // 6. EGRESSOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'egresso', 'egressos', 'ex aluno', 'ex alunos',
            'antigo aluno', 'antigos alunos'
        ])) {
            return 'egressos';
        }

        // ============================================================
        // 7. UNIDADES ORGANICAS (Mais generico, vem depois)
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'unidade organica', 'unidades organicas', 'unidade', 'unidades'
        ])) {
            return 'unidades';
        }

        // ============================================================
        // 8. CURSOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'curso', 'cursos'
        ])) {
            return 'cursos';
        }

        // ============================================================
        // 9. EVENTOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'evento', 'eventos'
        ])) {
            return 'eventos';
        }

        // ============================================================
        // 10. RELATORIOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'relatorio', 'relatorios', 'relatorio estatistico',
            'estatisticas', 'estatistica'
        ])) {
            return 'relatorios';
        }

        // ============================================================
        // 11. DASHBOARD
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'dashboard', 'painel', 'painel administrativo',
            'pagina inicial', 'inicio'
        ])) {
            return 'dashboard';
        }

        // ============================================================
        // 12. MAPA
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'mapa', 'localizacao', 'localizacao dos egressos',
            'onde estao os egressos'
        ])) {
            return 'mapa';
        }

        // ============================================================
        // 13. REDE DE CONTACTOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'rede', 'rede de contactos', 'rede de contatos',
            'contactos', 'contatos'
        ])) {
            return 'rede';
        }

        // ============================================================
        // 14. MENSAGENS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'mensagem', 'mensagens', 'comunicacao', 'comunicacoes'
        ])) {
            return 'mensagens';
        }

        // ============================================================
        // 15. PERFIL
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'perfil', 'meu perfil', 'dados da conta', 'conta'
        ])) {
            return 'perfil';
        }

        // ============================================================
        // 16. MURAL
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'mural', 'publicacao', 'publicacoes'
        ])) {
            return 'mural';
        }

        // ============================================================
        // 17. PESQUISAS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'pesquisa', 'pesquisas', 'questionario', 'questionarios',
            'inquerito'
        ])) {
            return 'pesquisas';
        }

        // ============================================================
        // 18. FEEDBACK
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'feedback', 'avaliacao', 'avaliacoes', 'avaliar'
        ])) {
            return 'feedback';
        }

        // ============================================================
        // 19. SERVICOS
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'servico', 'servicos'
        ])) {
            return 'servicos';
        }

        // ============================================================
        // 20. NOTIFICACOES
        // ============================================================
        if ($this->contemAlgum($pergunta, [
            'notificacao', 'notificacoes', 'alerta', 'alertas'
        ])) {
            return 'notificacoes';
        }

        return 'desconhecido';
    }

    /**
     * Verificar se algum termo existe na pergunta.
     */
    private function contemAlgum(string $texto, array $termos): bool
    {
        foreach ($termos as $termo) {
            if (Str::contains($texto, $termo)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Respostas do assistente administrativo.
     */
    private function respostaPorIntencao(string $intencao, ?string $pergunta = null): string
    {
        $pergunta = $pergunta ?? '';

        return match ($intencao) {

            'saudacao' =>
                'Ola! Sou o assistente administrativo da plataforma UniLuanda. Como posso ajuda-lo? Posso auxiliar com egressos, validacoes, oportunidades, eventos, relatorios e muito mais.',

            'agradecimento' =>
                'De nada! Estou sempre disponivel para ajuda-lo. Se precisar de mais alguma coisa, e so chamar.',

            'ajuda_sistema' =>
                'Posso ajuda-lo com as seguintes areas administrativas:

Egressos - Consultar e gerir ex-estudantes
Validacao - Aprovar/rejeitar cadastros pendentes
Unidades Organicas - Gerir faculdades e departamentos
Cursos - Gerir cursos da instituicao
Oportunidades - Gerir vagas e oportunidades
Eventos - Gerir eventos e inscricoes
Relatorios - Consultar estatisticas e relatorios
Mapa - Visualizar localizacao dos egressos
Rede - Acompanhar contactos entre egressos
Mural - Gerir publicacoes e noticias
Pesquisas - Criar e analisar questionarios
Feedback - Recolher opinioes dos utilizadores
Notificacoes - Acompanhar alertas do sistema

Digite o nome da area que deseja explorar ou faca uma pergunta especifica!',

            'dashboard' =>
                'Dashboard Administrativo

O Dashboard e o painel principal da administracao. Nele pode acompanhar:

Estatisticas principais - Numero de egressos, oportunidades, eventos e conexoes
Graficos e indicadores - Distribuicao por curso, unidade e localizacao
Notificacoes recentes - Alertas sobre novas atividades
Atividades recentes - Ultimas acoes realizadas no sistema

Dica: Utilize os filtros para personalizar a visualizacao dos dados.',

            'egressos' =>
                'Gestao de Egressos

A area de Egressos permite:

Consultar - Visualizar lista completa de ex-estudantes
Pesquisar - Filtrar por nome, curso, unidade, status
Editar - Atualizar informacoes de um egresso
Validar - Aprovar ou rejeitar cadastros pendentes
Exportar - Gerar relatorios em PDF/CSV
Importar - Adicionar multiplos egressos via ficheiro

Acao rapida: Aceda a "Egressos" no menu lateral para comecar.',

            'validacao' =>
                'Validacao de Egressos

A validacao e o processo de verificacao dos dados submetidos pelos egressos durante o registo.

Como funciona:
1. O egresso regista-se na plataforma (Nivel Prata)
2. O sistema marca o cadastro como "Pendente"
3. O administrador analisa os dados fornecidos
4. Decide entre Aprovar ou Reprovar o cadastro

Porque validar?
- Garantir que apenas ex-estudantes legitimos tenham acesso
- Manter a integridade dos dados da comunidade
- Evitar cadastros duplicados ou fraudulentos

Vá para "Validacao de Egressos" no menu para ver os pendentes.',

            'unidades' =>
                'Unidades Organicas

A area de Unidades Organicas permite gerir as faculdades, institutos e departamentos da instituicao.

Funcionalidades:
- Adicionar novas unidades
- Editar informacoes existentes
- Associar cursos a cada unidade
- Ativar/desativar unidades

Aceda a "Unidades" no menu administrativo para gerir.',

            'cursos' =>
                'Cursos

A area de Cursos permite gerir todos os cursos oferecidos pela instituicao.

Funcionalidades:
- Adicionar novos cursos
- Editar cursos existentes
- Associar cursos a unidades organicas
- Definir duracao e codigo do curso

Aceda a "Cursos" no menu para gerir a oferta formativa.',

            'oportunidades' =>
                'Oportunidades

A area de Oportunidades permite gerir vagas de emprego, estagios e bolsas disponibilizadas aos egressos.

Funcionalidades:
- Criar novas oportunidades
- Editar oportunidades existentes
- Definir requisitos e prazos
- Publicar ou arquivar oportunidades
- Visualizar candidaturas recebidas
- Acompanhar estatisticas de candidaturas

Aceda a "Oportunidades" no menu para comecar a gerir.',

            'eventos' =>
                'Eventos

A area de Eventos permite gerir eventos relacionados com a instituicao e a comunidade de egressos.

Funcionalidades:
- Criar novos eventos (presenciais, online, hibridos)
- Definir data, local e capacidade
- Gerir inscricoes de participantes
- Emitir comprovativos de participacao
- Notificar egressos sobre novos eventos

Aceda a "Eventos" no menu para criar e gerir eventos.',

            'relatorios' =>
                'Relatorios

A area de Relatorios permite consultar informacoes consolidadas da plataforma.

Tipos de relatorios disponiveis:
- Relatorio de Egressos (PDF/CSV)
- Relatorio de Profissionais
- Relatorio de Localizacoes
- Relatorio Completo
- Relatorio de Empregabilidade
- Relatorio por Curso
- Relatorio por Unidade
- Relatorio de Oportunidades

Aceda a "Relatorios" no menu para gerar e exportar dados.',

            'mapa' =>
                'Mapa de Egressos

O Mapa permite visualizar a distribuicao geografica dos egressos.

Funcionalidades:
- Visualizar egressos no mapa interativo
- Filtrar por curso, unidade ou pais
- Ver detalhes do egresso ao clicar no marcador
- Identificar conexoes entre egressos
- Exportar dados de localizacao

Aceda a "Mapa" no menu para explorar a distribuicao geografica.',

            'rede' =>
                'Rede de Contactos

A Rede de Contactos permite acompanhar as conexoes estabelecidas entre os egressos.

Funcionalidades:
- Visualizar conexoes entre egressos
- Acompanhar pedidos de conexao pendentes
- Analisar a rede de contactos da comunidade
- Identificar egressos mais conectados

Aceda a "Rede" no menu para gerir as conexoes.',

            'mensagens' =>
                'Mensagens

A area de Mensagens permite acompanhar as comunicacoes realizadas atraves da plataforma.

Funcionalidades:
- Visualizar mensagens enviadas/recebidas
- Responder a mensagens de egressos
- Acompanhar conversas
- Marcar mensagens como lidas/nao lidas

Aceda a "Mensagens" no menu para gerir a comunicacao.',

            'perfil' =>
                'Perfil Administrativo

No Perfil pode consultar e atualizar as informacoes da sua conta.

Funcionalidades:
- Editar dados pessoais
- Atualizar foto de perfil
- Alterar palavra-passe
- Configurar preferencias de notificacao

Aceda a "Perfil" no menu para atualizar os seus dados.',

            'mural' =>
                'Mural de Noticias

O Mural permite gerir publicacoes e noticias disponibilizadas na plataforma.

Funcionalidades:
- Criar novas publicacoes
- Editar publicacoes existentes
- Definir publicacoes em destaque
- Agendar publicacoes para data futura
- Categorizar por tipo (noticia, evento, edital)

Aceda a "Mural" no menu para gerir as publicacoes.',

            'pesquisas' =>
                'Pesquisas

A area de Pesquisas permite criar e gerir questionarios dirigidos aos egressos.

Funcionalidades:
- Criar novas pesquisas
- Adicionar perguntas (abertas, multipla escolha, escala)
- Definir data de inicio e fim
- Acompanhar respostas dos egressos
- Analisar resultados em tempo real
- Exportar dados das respostas

Aceda a "Pesquisas" no menu para criar questionarios.',

            'feedback' =>
                'Feedback

A area de Feedback permite recolher e analisar opinioes dos utilizadores.

Funcionalidades:
- Visualizar feedbacks enviados
- Aprovar ou rejeitar feedbacks publicos
- Responder a feedbacks
- Analisar avaliacoes e sugestoes

Aceda a "Feedback" no menu para gerir as avaliacoes.',

            'servicos' =>
                'Servicos

A plataforma disponibiliza servicos para facilitar a gestao dos egressos.

Servicos disponiveis:
- Solicitacao de documentos
- Pedidos de informacao
- Suporte tecnico
- Acompanhamento de pedidos

Aceda a "Servicos" no menu para gerir os pedidos dos egressos.',

            'notificacoes' =>
                'Notificacoes

As notificacoes mantem o administrador informado sobre atividades importantes.

Tipos de notificacoes:
- Novos cadastros pendentes
- Novas candidaturas
- Novas inscricoes em eventos
- Novas mensagens
- Atualizacoes do sistema

As notificacoes sao exibidas no topo do painel administrativo.',

            default =>
                'Ainda nao consegui identificar exatamente a sua pergunta.

Tente perguntar de forma mais especifica:

- "Como gerir os egressos?"
- "Como validar um egresso pendente?"
- "Onde encontro os relatorios?"
- "Como funciona o painel administrativo?"
- "Como criar uma oportunidade?"
- "Como gerir os eventos?"
- "O que sao as unidades organicas?"
- "Como funciona a validacao de cadastros?"
- "O que e o mural de noticias?"
- "Como criar uma pesquisa?"

Se preferir, digite apenas uma palavra-chave como: egressos, validacao, oportunidades, eventos, relatorios, unidades, cursos, mural, pesquisas, feedback, servicos, notificacoes, rede, mapa',
        };
    }
}