<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditarAcoesAdmin
{
    /**
     * Mapeamento: nome do controller → prefixo da acção
     */
    private array $mapaControllers = [
        'AdminEgressoController'      => 'egresso',
        'AdminEmpresaController'      => 'empresa',
        'AdminAdminController'        => 'admin',
        'AdminOportunidadeController' => 'oportunidade',
        'AdminEventoController'       => 'evento',
        'AdminCursoController'        => 'curso',
        'AdminUnidadeController'      => 'unidade',
        'AdminServicoController'      => 'servico',
        'AdminFeedbackController'     => 'feedback',
        'AdminPesquisaController'     => 'pesquisa',
        'AdminMuralController'        => 'mural',
        'AdminRelatorioController'    => 'relatorio',
        'AdminValidacaoController'    => 'validacao',
        'RelatorioController'         => 'relatorio',
        'CandidaturaController'       => 'candidatura',
        'InscricaoEventoController'   => 'inscricao',
        'ContactoAlumniController'    => 'contacto',
        'MensagemController'          => 'mensagem',
        'NotificacaoController'       => 'notificacao',
        'PerfilController'            => 'perfil',
        'VideoCallController'         => 'video_call',
        'RedeController'              => 'rede',
        'MapaController'              => 'mapa',
        'FeedController'              => 'feed',
    ];

    /**
     * Mapeamento: método → verbo da acção
     */
    private array $mapaMetodos = [
        'store'             => 'criar',
        'update'            => 'alterar',
        'destroy'           => 'apagar',
        'aprovar'           => 'aprovar',
        'rejeitar'          => 'rejeitar',
        'validar'           => 'validar',
        'mudarStatus'       => 'alterar_status',
        'marcarEntrevista'  => 'marcar_entrevista',
        'marcarPresenca'    => 'marcar_presenca',
        'aprovarInscricao'  => 'aprovar_inscricao',
        'rejeitarInscricao' => 'rejeitar_inscricao',
        'responder'         => 'responder',
        'verificar'         => 'verificar',
        'desverificar'      => 'desverificar',
        'reativar'          => 'reativar',
        'importar'          => 'importar',
        'exportar'          => 'exportar',
        'enviar'            => 'enviar',
        'enviarAudio'       => 'enviar_audio',
        'enviarFicheiro'    => 'enviar_ficheiro',
        'editar'            => 'editar_mensagem',
        'eliminar'          => 'eliminar_mensagem',
        'marcarLida'        => 'marcar_lida',
        'marcarTodas'       => 'marcar_todas_lidas',
        'marcarComoLida'    => 'marcar_lida',
        'marcarComoLidaAjax'=> 'marcar_lida',
        'marcarTodasComoLidas'=> 'marcar_todas_lidas',
        'atender'           => 'atender',
        'iniciar'           => 'iniciar',
        'aceitar'           => 'aceitar',
        'encerrar'          => 'encerrar',
        'recusar'           => 'recusar',
        'registarPeer'      => 'registar_peer',
        'conectar'          => 'conectar',
        'aprovar'           => 'aprovar',
        'reprovar'          => 'reprovar',
    ];

    /**
     * Mapeamento contextual: combina prefixo + input → acção específica
     * Ex: candidatura + status=aprovado → candidatura.aprovar
     */
    private array $mapaContextual = [
        'candidatura' => [
            'status' => [
                'aprovado'      => 'aprovar',
                'rejeitado'     => 'rejeitar',
                'entrevista'    => 'marcar_entrevista',
                'em_analise'    => 'colocar_em_analise',
                'pendente'      => 'reabrir',
            ],
        ],
        'validacao' => [
            'status' => [
                'aprovado'  => 'aprovar',
                'reprovado' => 'reprovar',
            ],
        ],
        'empresa' => [
            'status_validacao' => [
                'aprovado'  => 'aprovar',
                'reprovado' => 'reprovar',
            ],
        ],
        'feedback' => [
            'status' => [
                'aprovado'  => 'aprovar',
                'rejeitado' => 'rejeitar',
            ],
        ],
        'servico' => [
            'status' => [
                'atendido'   => 'atender',
                'cancelado'  => 'cancelar',
                'andamento'  => 'colocar_em_andamento',
            ],
        ],
        'inscricao' => [
            'presente' => [
                '1' => 'marcar_presente',
                '0' => 'marcar_ausente',
            ],
        ],
    ];

    /**
     * Apenas estes verbos HTTP geram auditoria
     */
    private array $verbosAuditaveis = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        // ============================================================
        // 🔍 DEBUG — Verificar se o middleware está a ser chamado
        // ============================================================
        Log::info('🔍 [AuditarAcoesAdmin] CHAMADO', [
            'url'    => $request->fullUrl(),
            'method' => $request->method(),
            'auth'   => Auth::id(),
            'admin'  => Auth::user()?->admin?->id,
        ]);

        // Apenas admins autenticados
        $user  = Auth::user();
        $admin = $user?->admin;

        if (!$user || !$admin) {
            return $next($request);
        }

        // Apenas verbos que alteram dados
        if (!in_array($request->method(), $this->verbosAuditaveis)) {
            return $next($request);
        }

        // Ignorar rotas de login/logout (evitar ruído)
        if ($request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        // Capturar antes da execução
        $response = $next($request);

        // Só audita se a resposta foi sucesso (2xx/3xx)
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        // Descobrir controller + método
        $route  = $request->route();
        $action = $route?->getActionName() ?? '';

        [$controller, $metodo] = $this->parseAction($action);

        if (!$controller) {
            return $response;
        }

        // Prefixo base
        $prefixo = $this->mapaControllers[$controller] ?? strtolower(str_replace('Controller', '', $controller));

        // ============================================================
        // ✨ CONTEXTO — Detetar ação específica via input do form
        // ============================================================
        $verboContextual = $this->detectarAcaoContextual($prefixo, $request);

        // Verbo base (fallback)
        $verbo = $verboContextual
              ?? $this->mapaMetodos[$metodo]
              ?? $metodo;

        $acao = "{$prefixo}.{$verbo}";

        // Descrição rica
        $descricao = $this->buildDescricaoRica($prefixo, $verbo, $metodo, $request, $controller);

        // Severidade
        $severidade = $this->determineSeveridade($verbo, $prefixo);

        // Dados
        $dadosAntes  = [];
        $dadosDepois = $this->sanitizeInput($request->all());

        // ============================================================
        // 🔍 DEBUG
        // ============================================================
        Log::info('🔍 [AuditarAcoesAdmin] Vai criar AuditLog', [
            'acao'       => $acao,
            'descricao'  => $descricao,
            'severidade' => $severidade,
            'admin_id'   => $admin->id,
        ]);

        try {
            AuditLog::create([
                'admin_id'     => $admin->id,
                'admin_email'  => $user->email,
                'admin_nome'   => $admin->nome_completo,

                'admin_unidade_id'   => $admin->unidade_id,
                'admin_unidade_sigla'=> $admin->unidade?->sigla,
                'admin_unidade_nome' => $admin->unidade?->nome,

                'acao'         => $acao,
                'alvo_tipo'    => $this->guessAlvoTipo($prefixo),
                'alvo_id'      => $this->guessAlvoId($request),
                'descricao'    => $descricao,

                'dados_antes'  => $dadosAntes,
                'dados_depois' => $dadosDepois,

                'ip'           => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 500),

                'severidade'   => $severidade,
            ]);

            Log::info('✅ [AuditarAcoesAdmin] AuditLog CRIADO');

        } catch (\Exception $e) {
            Log::error('❌ [AuditarAcoesAdmin] ERRO', [
                'message' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * ============================================================
     * ✨ Detectar ação contextual via input do formulário
     * ============================================================
     * Ex: candidatura + status=aprovado → "aprovar"
     */
    private function detectarAcaoContextual(string $prefixo, Request $request): ?string
    {
        if (!isset($this->mapaContextual[$prefixo])) {
            return null;
        }

        foreach ($this->mapaContextual[$prefixo] as $campo => $mapaValores) {
            $valor = (string) $request->input($campo, '');

            if ($valor !== '' && isset($mapaValores[$valor])) {
                return $mapaValores[$valor];
            }
        }

        return null;
    }

    /**
     * ============================================================
     * ✨ Descrição rica baseada no contexto
     * ============================================================
     */
    private function buildDescricaoRica(
        string $prefixo,
        string $verbo,
        string $metodo,
        Request $request,
        string $controller
    ): string {
        $alvoId = $this->guessAlvoId($request);
        $sufixo = $alvoId ? " #{$alvoId}" : '';

        // ====================================================
        // Casos especiais com nomes mais humanos
        // ====================================================
        $descricoesEspeciais = [
            'candidatura.aprovar'            => 'Aprovou candidatura',
            'candidatura.rejeitar'           => 'Rejeitou candidatura',
            'candidatura.marcar_entrevista'  => 'Marcou entrevista para candidatura',
            'candidatura.colocar_em_analise' => 'Colocou candidatura em análise',
            'candidatura.reabrir'            => 'Reabriu candidatura',

            'validacao.aprovar'  => 'Aprovou egresso pendente na validação',
            'validacao.reprovar' => 'Reprovou egresso pendente na validação',

            'empresa.aprovar'  => 'Aprovou empresa',
            'empresa.reprovar' => 'Reprovou empresa',

            'feedback.aprovar'  => 'Aprovou feedback',
            'feedback.rejeitar' => 'Rejeitou feedback',

            'servico.atender'                => 'Marcou serviço como atendido',
            'servico.cancelar'               => 'Cancelou serviço',
            'servico.colocar_em_andamento'   => 'Colocou serviço em andamento',

            'inscricao.aprovar_inscricao'    => 'Aprovou inscrição',
            'inscricao.rejeitar_inscricao'   => 'Rejeitou inscrição',
            'inscricao.marcar_presente'      => 'Marcou presença',
            'inscricao.marcar_ausente'       => 'Marcou ausência',

            'egresso.verificar'    => 'Verificou egresso',
            'egresso.desverificar' => 'Removeu verificação do egresso',
            'egresso.reativar'     => 'Reativou egresso',
            'egresso.importar'     => 'Importou egressos',
            'egresso.exportar'     => 'Exportou egressos',

            'mensagem.enviar'          => 'Enviou mensagem',
            'mensagem.enviar_audio'    => 'Enviou áudio',
            'mensagem.enviar_ficheiro' => 'Enviou ficheiro',
            'mensagem.editar_mensagem' => 'Editou mensagem',
            'mensagem.eliminar_mensagem' => 'Eliminou mensagem',

            'notificacao.marcar_lida'        => 'Marcou notificação como lida',
            'notificacao.marcar_todas_lidas' => 'Marcou todas as notificações como lidas',

            'video_call.iniciar'      => 'Iniciou videochamada',
            'video_call.aceitar'      => 'Aceitou videochamada',
            'video_call.encerrar'     => 'Encerrou videochamada',
            'video_call.recusar'      => 'Recusou videochamada',

            'rede.aceitar'  => 'Aceitou conexão',
            'rede.recusar'  => 'Recusou conexão',
            'rede.conectar' => 'Enviou pedido de conexão',
        ];

        if (isset($descricoesEspeciais[$prefixo . '.' . $verbo])) {
            return $descricoesEspeciais[$prefixo . '.' . $verbo] . $sufixo;
        }

        // ====================================================
        // Fallback genérico
        // ====================================================
        $verboLegivel = ucfirst(str_replace('_', ' ', $verbo));

        return $verboLegivel . ' ' . str_replace('_', ' ', $prefixo) . $sufixo;
    }

    /**
     * Extrai [ControllerName, methodName] do action string
     */
    private function parseAction(string $action): array
    {
        if (!str_contains($action, '@')) {
            return [null, null];
        }

        [$class, $method] = explode('@', $action);
        $parts = explode('\\', $class);
        $controller = end($parts);

        return [$controller, $method];
    }

    /**
     * Determina severidade baseada na acção
     */
    private function determineSeveridade(string $verbo, string $prefixo): string
    {
        // Crítico — destrutivas
        if (in_array($verbo, ['apagar', 'rejeitar', 'rejeitar_inscricao', 'reprovar', 'cancelar'])) {
            return 'critico';
        }

        // Aviso — alterações sensíveis
        if (in_array($verbo, [
                'alterar', 'aprovar', 'alterar_status', 'marcar_entrevista',
                'aprovar_inscricao', 'atender', 'verificar', 'desverificar',
                'reativar', 'importar', 'reabrir', 'colocar_em_analise',
                'colocar_em_andamento', 'editar_mensagem', 'eliminar_mensagem',
            ]) ||
            in_array($prefixo, ['admin', 'configuracao', 'perfil'])) {
            return 'aviso';
        }

        return 'info';
    }

    /**
     * Tenta adivinhar o tipo de alvo
     */
    private function guessAlvoTipo(string $prefixo): ?string
    {
        $mapa = [
            'egresso'      => 'Egresso',
            'empresa'      => 'Empresa',
            'admin'        => 'Admin',
            'oportunidade' => 'Oportunidade',
            'evento'       => 'Evento',
            'curso'        => 'Curso',
            'unidade'      => 'UnidadeOrganica',
            'servico'      => 'Servico',
            'feedback'     => 'Feedback',
            'pesquisa'     => 'Pesquisa',
            'mural'        => 'MuralNoticia',
            'validacao'    => 'Egresso',
            'candidatura'  => 'Candidatura',
            'inscricao'    => 'InscricaoEvento',
            'contacto'     => 'ContactoAlumni',
            'mensagem'     => 'Mensagem',
            'notificacao'  => 'Notificacao',
            'perfil'       => 'Admin',
            'video_call'   => 'VideoCall',
            'rede'         => 'Conexao',
            'mapa'         => 'Localizacao',
            'feed'         => 'Feed',
        ];

        return $mapa[$prefixo] ?? null;
    }

    /**
     * Tenta extrair o ID do alvo dos parâmetros da rota
     */
    private function guessAlvoId(Request $request): ?int
    {
        $params = $request->route()?->parameters() ?? [];

        foreach ([
            'id', 'egresso', 'empresa', 'admin', 'oportunidade', 'evento',
            'candidatura', 'inscricao', 'contacto', 'mensagem', 'feedback',
            'servico', 'pesquisa', 'mural', 'curso', 'unidade', 'conexao',
        ] as $key) {
            if (isset($params[$key])) {
                $value = $params[$key];

                if (is_object($value) && method_exists($value, 'getKey')) {
                    return $value->getKey();
                }

                if (is_numeric($value)) {
                    return (int) $value;
                }
            }
        }

        return null;
    }

    /**
     * Remove dados sensíveis dos inputs
     */
    private function sanitizeInput(array $input): array
    {
        $sensíveis = ['password', 'password_confirmation', '_token', '_method', 'admin_code'];

        foreach ($sensíveis as $key) {
            if (isset($input[$key])) {
                $input[$key] = '***';
            }
        }

        // Não guardar ficheiros
        foreach ($input as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $input[$key] = '[ficheiro: ' . $value->getClientOriginalName() . ']';
            }
        }

        return $input;
    }
}