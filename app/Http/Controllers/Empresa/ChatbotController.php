<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    /**
     * ============================================================
     * ENVIAR MENSAGEM PARA O CHATBOT
     * ============================================================
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'mensagem' => 'required|string|max:2000',
        ]);

        $empresa = Auth::user()->empresa;

        // Gerar resposta baseada na mensagem
        $resposta = $this->gerarResposta($request->mensagem, $empresa);

        return response()->json([
            'sucesso'   => true,
            'resposta'  => $resposta,
        ]);
    }


    /**
     * ============================================================
     * VERIFICAR NOVAS MENSAGENS (para o badge)
     * ============================================================
     */
    public function verificar(Request $request)
    {
        return response()->json([
            'success'   => true,
            'total'     => 0,      // Podes mudar se tiveres notificações
            'tem_novas' => false,
        ]);
    }


    /**
     * ============================================================
     * GERAR RESPOSTA BASEADA EM PALAVRAS-CHAVE
     * ============================================================
     */
    private function gerarResposta($mensagem, $empresa)
    {
        $m = mb_strtolower(trim($mensagem));

        // ----------------------------------------------------------
        // SAUDAÇÕES
        // ----------------------------------------------------------
        if (
            str_contains($m, 'olá') ||
            str_contains($m, 'ola') ||
            str_contains($m, 'oi') ||
            str_contains($m, 'bom dia') ||
            str_contains($m, 'boa tarde') ||
            str_contains($m, 'boa noite')
        ) {
            return 'Olá! 👋 Em que posso ajudar-te hoje?';
        }

        // ----------------------------------------------------------
        // OPORTUNIDADES
        // ----------------------------------------------------------
        if (str_contains($m, 'oportunidade') || str_contains($m, 'vaga') || str_contains($m, 'publicar')) {
            return 'Podes gerir as tuas oportunidades no menu <strong>Oportunidades</strong> (menu lateral). '
                . 'Aí podes criar, editar, ativar/desativar e apagar as tuas vagas publicadas. '
                . 'Clica em <strong>Nova Oportunidade</strong> para adicionar uma nova.';
        }

        // ----------------------------------------------------------
        // CANDIDATURAS
        // ----------------------------------------------------------
        if (str_contains($m, 'candidatura') || str_contains($m, 'candidato')) {
            return 'As candidaturas recebidas aparecem na secção <strong>Candidaturas</strong>. '
                . 'Podes ver o perfil completo de cada candidato, mudar o estado (pendente, em análise, entrevista, aprovado, rejeitado) '
                . 'e enviar mensagens.';
        }

        // ----------------------------------------------------------
        // APROVAR / REJEITAR
        // ----------------------------------------------------------
        if (str_contains($m, 'aprovar') || str_contains($m, 'aprovação') || str_contains($m, 'aceitar')) {
            return 'Para aprovar uma candidatura, vai a <strong>Candidaturas</strong>, '
                . 'abre a candidatura pretendida e clica no botão <strong>Aprovar</strong>. '
                . 'Podes também agendar uma entrevista ou enviar mensagem ao candidato.';
        }

        if (str_contains($m, 'rejeitar') || str_contains($m, 'recusar')) {
            return 'Para rejeitar uma candidatura, vai a <strong>Candidaturas</strong>, '
                . 'abre a candidatura e clica em <strong>Rejeitar</strong>. '
                . 'Podes indicar o motivo da rejeição (opcional).';
        }

        // ----------------------------------------------------------
        // PERFIL
        // ----------------------------------------------------------
        if (str_contains($m, 'perfil') || str_contains($m, 'empresa') || str_contains($m, 'logo') || str_contains($m, 'dados')) {
            return 'Podes atualizar os dados da tua empresa em <strong>Perfil da Empresa</strong> (menu lateral). '
                . 'Aí podes alterar o nome, sector, descrição, contactos, localização e logotipo. '
                . 'Mantém os dados atualizados para atrair mais candidatos.';
        }

        // ----------------------------------------------------------
        // DASHBOARD
        // ----------------------------------------------------------
        if (str_contains($m, 'dashboard') || str_contains($m, 'painel') || str_contains($m, 'resumo')) {
            return 'O <strong>Dashboard</strong> mostra-te um resumo das tuas atividades: '
                . 'total de oportunidades publicadas, candidaturas recebidas, pendentes, aprovadas e a taxa de aprovação. '
                . 'Também tens gráficos de distribuição por estado e tipo.';
        }

        // ----------------------------------------------------------
        // SUPORTE
        // ----------------------------------------------------------
        if (str_contains($m, 'suporte') || str_contains($m, 'contacto') || str_contains($m, 'ajuda')) {
            return 'Para contactar o suporte, podes:'
                . '<br>📧 Email: <strong>alumni@uniluanda.ao</strong>'
                . '<br>📞 Telefone: <strong>+244 923 688 049</strong>'
                . '<br>🕐 Horário: Seg-Sex, 8h-15h';
        }

        // ----------------------------------------------------------
        // CONTA
        // ----------------------------------------------------------
        if (str_contains($m, 'conta') || str_contains($m, 'password') || str_contains($m, 'senha')) {
            return 'Para alterar a password ou gerir a conta, vai ao menu <strong>Perfil da Empresa</strong> '
                . 'e clica em <strong>Segurança</strong>. Se tiveres problemas, contacta o suporte.';
        }

        // ----------------------------------------------------------
        // ESTATÍSTICAS
        // ----------------------------------------------------------
        if (str_contains($m, 'estatística') || str_contains($m, 'relatório') || str_contains($m, 'gráfico')) {
            return 'Podes consultar as estatísticas no <strong>Dashboard</strong>. '
                . 'Vês gráficos de: status das candidaturas, oportunidades por tipo e taxa de aprovação.';
        }

        // ----------------------------------------------------------
        // RESPOSTA PADRÃO
        // ----------------------------------------------------------
        return 'Não tenho a certeza sobre isso. 🤔<br><br>'
            . 'Podes tentar reformular a pergunta ou clicar numa das sugestões acima. '
            . 'Para questões mais específicas, contacta o suporte em <strong>Suporte</strong>.';
    }
}