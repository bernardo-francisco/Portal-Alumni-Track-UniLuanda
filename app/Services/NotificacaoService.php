<?php

namespace App\Services;

use App\Events\NovaNotificacao;
use App\Events\StartVideoCall;
use App\Mail\ConfirmacaoCandidaturaMail;
use App\Mail\ContaAprovadaMail;
use App\Mail\StatusCandidaturaMail;
use App\Mail\LembreteEventoMail;
use App\Mail\InscricaoConfirmadaMail;
use App\Mail\PresencaConfirmadaEntrevistaMail;
use App\Mail\PresencaEventoMail;
use App\Mail\ContaReprovadaMail;
use App\Mail\EmpresaValidadaMail;
use App\Mail\InscricaoAprovada;
use App\Mail\InscricaoRejeitada;
use App\Mail\NovaCandidaturaMail;
use App\Mail\NovaMensagemMail;
use App\Mail\NovoPendenteMail;
use App\Mail\RespostaContactoMail;
use App\Models\Admin;
use App\Models\Candidatura;
use App\Models\Egresso;
use App\Models\Feedback;
use App\Models\InscricaoEvento;
use App\Models\Mensagem;
use App\Models\Notificacao;
use App\Models\Servico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificacaoService
{
    // ============================================================
    // 📋 LISTAGEM PARA O ADMIN
    // ============================================================
    public static function getNotificacoesAdmin(): array
    {
        $user = Auth::user();

        if (!$user) {
            return ['total' => 0, 'items' => collect()];
        }

        $admin = Admin::where('user_id', $user->id)->first();

        if (!$admin) {
            return ['total' => 0, 'items' => collect()];
        }

        $notificacoes = collect();

        $notificacoesSistema = Notificacao::where('admin_id', $admin->id)
            ->where('lida', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($notif) {
                return [
                    'id'       => 'notif_' . $notif->id,
                    'tipo'     => $notif->tipo ?? 'sistema',
                    'titulo'   => $notif->titulo,
                    'mensagem' => $notif->mensagem,
                    'link'     => $notif->link ?? '#',
                    'icone'    => self::getIconePorTipo($notif->tipo),
                    'cor'      => self::getCorPorTipo($notif->tipo),
                    'data'     => $notif->created_at,
                    'lida'     => $notif->lida,
                ];
            });

        $notificacoes = $notificacoes->merge($notificacoesSistema);

        $pendentesValidacao = Egresso::where('status_validacao', 'pendente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($egresso) {
                return [
                    'id'       => 'validacao_' . $egresso->id,
                    'tipo'     => 'validacao',
                    'titulo'   => 'Novo cadastro pendente',
                    'mensagem' => $egresso->nome_completo . ' aguarda validação.',
                    'link'     => route('admin.validacao.show', $egresso->id),
                    'icone'    => 'user-check',
                    'cor'      => 'warning',
                    'data'     => $egresso->created_at,
                    'lida'     => false,
                ];
            });

        $notificacoes = $notificacoes->merge($pendentesValidacao);

        $feedbacksPendentes = Feedback::where('status', 'pendente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id'       => 'feedback_' . $feedback->id,
                    'tipo'     => 'feedback',
                    'titulo'   => 'Novo feedback recebido',
                    'mensagem' => $feedback->titulo ?? 'Feedback aguarda aprovação.',
                    'link'     => route('admin.feedbacks.show', $feedback->id),
                    'icone'    => 'comment-dots',
                    'cor'      => 'info',
                    'data'     => $feedback->created_at,
                    'lida'     => false,
                ];
            });

        $notificacoes = $notificacoes->merge($feedbacksPendentes);

        $servicosPendentes = Servico::where('status', 'pendente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($servico) {
                return [
                    'id'       => 'servico_' . $servico->id,
                    'tipo'     => 'servico',
                    'titulo'   => 'Novo pedido de serviço',
                    'mensagem' => $servico->servico ?? 'Pedido aguarda atendimento.',
                    'link'     => route('admin.servicos.show', $servico->id),
                    'icone'    => 'concierge-bell',
                    'cor'      => 'danger',
                    'data'     => $servico->created_at,
                    'lida'     => false,
                ];
            });

        $notificacoes = $notificacoes->merge($servicosPendentes);

        $notificacoes = $notificacoes->sortByDesc('data')->values();

        return [
            'total' => $notificacoes->count(),
            'items' => $notificacoes->take(15),
        ];
    }

    // ============================================================
    // 📋 MENSAGENS PARA O ADMIN
    // ============================================================
    public static function getMensagensAdmin(): array
    {
        $user = Auth::user();

        if (!$user) {
            return ['total' => 0, 'items' => collect()];
        }

        $adminEgresso = Egresso::where('user_id', $user->id)->first();

        if (!$adminEgresso) {
            return ['total' => 0, 'items' => collect()];
        }

        $mensagensNaoLidas = Mensagem::with(['remetente'])
            ->where('destinatario_id', $adminEgresso->id)
            ->where('lida', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $items = $mensagensNaoLidas->map(function ($msg) {
            $remetente = $msg->remetente;

            return [
                'id'                 => $msg->id,
                'remetente_id'       => $msg->remetente_id,
                'remetente_nome'     => $remetente->nome_completo ?? 'Utilizador',
                'remetente_foto'     => $remetente->foto_url ?? null,
                'remetente_iniciais' => self::getIniciais($remetente->nome_completo ?? 'U'),
                'mensagem'           => Str::limit($msg->mensagem, 50),
                'data'               => $msg->created_at,
                'link'               => route('admin.mensagens.conversa', $msg->remetente_id),
            ];
        });

        return [
            'total' => Mensagem::where('destinatario_id', $adminEgresso->id)
                ->where('lida', false)
                ->count(),
            'items' => $items,
        ];
    }

    // ============================================================
    // 🎯 CRIAR NOTIFICAÇÃO (BASE)
    // ============================================================
    public static function criarParaEgresso(
        int $egressoId,
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ): ?Notificacao {
        return self::criarNotificacao($egressoId, null, $titulo, $mensagem, $tipo, $link);
    }

    public static function criarParaAdmin(
        int $adminId,
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ): ?Notificacao {
        return self::criarNotificacao(null, $adminId, $titulo, $mensagem, $tipo, $link);
    }

    public static function criar(
        int $destinatarioId,
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null,
        string $destinatarioTipo = 'egresso'
    ): ?Notificacao {
        if ($destinatarioTipo === 'admin') {
            return self::criarParaAdmin($destinatarioId, $titulo, $mensagem, $tipo, $link);
        }
        return self::criarParaEgresso($destinatarioId, $titulo, $mensagem, $tipo, $link);
    }

    private static function criarNotificacao(
        ?int $egressoId,
        ?int $adminId,
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ): ?Notificacao {
        try {
            $notif = Notificacao::create([
                'egresso_id' => $egressoId,
                'admin_id'   => $adminId,
                'titulo'     => mb_substr($titulo, 0, 100),
                'mensagem'   => mb_substr($mensagem, 0, 500),
                'tipo'       => mb_substr($tipo, 0, 50),
                'link'       => $link ? mb_substr($link, 0, 255) : null,
                'lida'       => false,
            ]);

            try {
                broadcast(new NovaNotificacao($notif));
            } catch (\Exception $e) {
                Log::warning('Broadcast NovaNotificacao falhou: ' . $e->getMessage());
            }

            return $notif;

        } catch (\Exception $e) {
            Log::error('Erro ao criar notificação: ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================
    // 🔧 DETECTAR ADMIN
    // ============================================================
    public static function ehAdmin(int $egressoId): bool
{
    // 1. Vai buscar o egresso + user associado
    $egresso = Egresso::with('user')->find($egressoId);

    if (!$egresso || !$egresso->user) {
        return false;
    }

    // 2. Compara pelo user_id (não pelo id do egresso!)
    return Admin::where('user_id', $egresso->user_id)->exists()
        || ($egresso->user->role ?? null) === 'admin'
        || ($egresso->user->tipo ?? null) === 'admin';
}
    // ============================================================
    // 🎯 MENSAGENS
    // ============================================================
    public static function novaMensagem($remetente, $destinatarioId, string $texto, $ignorarLink = null): void
    {
        $ehAdmin = self::ehAdmin($destinatarioId);

        $link = $ehAdmin
            ? "/admin/mensagens/{$remetente->id}"
            : "/egresso/mensagens/{$remetente->id}";

        self::criar(
            $destinatarioId,
            "💬 Nova mensagem de {$remetente->nome_completo}",
            mb_substr($texto, 0, 100),
            'mensagem',
            $link,
            $ehAdmin ? 'admin' : 'egresso'
        );

        try {
            $destinatario = $ehAdmin
                ? Admin::with('user')->find($destinatarioId)
                : Egresso::with('user')->find($destinatarioId);

            $emailDestinatario = $destinatario->email
                ?? $destinatario->user->email
                ?? null;

            if ($emailDestinatario) {
                Mail::to($emailDestinatario)->send(
                    new NovaMensagemMail($remetente, $destinatario, 'texto', $texto)
                );

                Log::info("✅ Email nova mensagem (texto) enviado para: {$emailDestinatario}");
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email nova mensagem: ' . $e->getMessage());
        }
    }

    public static function novaMensagemAudio($remetente, $destinatarioId, $ignorarLink = null): void
    {
        $ehAdmin = self::ehAdmin($destinatarioId);

        $link = $ehAdmin
            ? "/admin/mensagens/{$remetente->id}"
            : "/egresso/mensagens/{$remetente->id}";

        self::criar(
            $destinatarioId,
            "🎤 Mensagem de voz de {$remetente->nome_completo}",
            'Enviou-lhe uma mensagem de voz.',
            'audio',
            $link,
            $ehAdmin ? 'admin' : 'egresso'
        );

        try {
            $destinatario = $ehAdmin
                ? Admin::with('user')->find($destinatarioId)
                : Egresso::with('user')->find($destinatarioId);

            $emailDestinatario = $destinatario->email
                ?? $destinatario->user->email
                ?? null;

            if ($emailDestinatario) {
                Mail::to($emailDestinatario)->send(
                    new NovaMensagemMail($remetente, $destinatario, 'audio', 'Mensagem de voz')
                );

                Log::info("✅ Email nova mensagem (áudio) enviado para: {$emailDestinatario}");
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email nova mensagem (áudio): ' . $e->getMessage());
        }
    }

    public static function novoFicheiro($remetente, $destinatarioId, string $nomeFicheiro, $ignorarLink = null): void
    {
        $ehAdmin = self::ehAdmin($destinatarioId);

        $link = $ehAdmin
            ? "/admin/mensagens/{$remetente->id}"
            : "/egresso/mensagens/{$remetente->id}";

        self::criar(
            $destinatarioId,
            "📎 Ficheiro de {$remetente->nome_completo}",
            "Enviou: {$nomeFicheiro}",
            'ficheiro',
            $link,
            $ehAdmin ? 'admin' : 'egresso'
        );

        try {
            $destinatario = $ehAdmin
                ? Admin::with('user')->find($destinatarioId)
                : Egresso::with('user')->find($destinatarioId);

            $emailDestinatario = $destinatario->email
                ?? $destinatario->user->email
                ?? null;

            if ($emailDestinatario) {
                Mail::to($emailDestinatario)->send(
                    new NovaMensagemMail($remetente, $destinatario, 'ficheiro', $nomeFicheiro)
                );

                Log::info("✅ Email novo ficheiro enviado para: {$emailDestinatario}");
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email novo ficheiro: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🎯 CHAMADA RECEBIDA (notificação + broadcast do modal)
    // ============================================================
public static function chamadaRecebida($chamador, $destinatarioId, string $tipo, string $roomId): void
{
    $label = $tipo === 'video' ? 'vídeo' : 'voz';
    $emoji = $tipo === 'video' ? '📹' : '📞';

    $ehAdmin = self::ehAdmin($destinatarioId);

    $link = $ehAdmin
        ? "/admin/video-call/{$roomId}?target_user={$chamador->id}&type={$tipo}"
        : "/egresso/video-call/{$roomId}?target_user={$chamador->id}&type={$tipo}";

    self::criar(
        $destinatarioId,
        "{$emoji} Chamada de {$label} de {$chamador->nome_completo}",
        'Está a ligar-lhe agora.',
        $tipo === 'video' ? 'chamada_video' : 'chamada_voz',
        $link,
        $ehAdmin ? 'admin' : 'egresso'
    );

    try {
        broadcast(new StartVideoCall(
            $roomId,
            (int) $chamador->id,
            $chamador->nome_completo ?? 'Utilizador',
            (int) $destinatarioId,
            $tipo
        ));

        Log::info('🚀 StartVideoCall enviado', [
            'room_id'         => $roomId,
            'chamador_id'     => $chamador->id,
            'destinatario_id' => $destinatarioId,
            'tipo'            => $tipo,
        ]);
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar StartVideoCall: ' . $e->getMessage());
    }
}

    // ============================================================
    // 🎯 CANDIDATURAS
    // ============================================================
    public static function novaCandidatura($egresso, $oportunidade, $candidaturaId): void
    {
        $candidatura = Candidatura::with([
            'egresso.curso.unidade',
            'oportunidade.empresa',
        ])->find($candidaturaId);

        try {
            $admins = Admin::all();
            foreach ($admins as $admin) {
                self::criarParaAdmin(
                    $admin->id,
                    "📋 Nova candidatura recebida",
                    "{$egresso->nome_completo} candidatou-se a '{$oportunidade->titulo}'",
                    'oportunidade',
                    "/admin/candidaturas/{$candidaturaId}"
                );
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar admins (novaCandidatura): ' . $e->getMessage());
        }

        try {
            self::novaCandidaturaEmpresa($egresso, $oportunidade, $candidaturaId);
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar empresa (novaCandidatura): ' . $e->getMessage());
        }

        try {
            self::criarParaEgresso(
                $egresso->id,
                "✅ Candidatura enviada",
                "A tua candidatura a '{$oportunidade->titulo}' foi recebida com sucesso.",
                'oportunidade',
                "/egresso/minhas-candidaturas"
            );
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar egresso (novaCandidatura): ' . $e->getMessage());
        }

        try {
            $emailEgresso = $egresso->email ?? null;

            if ($emailEgresso && $candidatura) {
                Mail::to($emailEgresso)
                    ->send(new ConfirmacaoCandidaturaMail($candidatura));

                Log::info('✅ Email confirmação candidatura enviado para egresso: ' . $emailEgresso);
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email confirmação candidatura (egresso): ' . $e->getMessage());
        }
    }

   public static function candidaturaAprovada($candidatura, ?int $egressoIdForcar = null): void
{
    $egressoId = $egressoIdForcar ?? $candidatura->egresso_id;

    self::criarParaEgresso(
        $egressoId,
        "🎉 Candidatura aprovada!",
        "A tua candidatura a '{$candidatura->oportunidade->titulo}' foi aprovada!",
        'oportunidade',
        "/egresso/minhas-candidaturas"
    );

    try {
        $egresso = $candidatura->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new StatusCandidaturaMail($candidatura, 'aprovado'));
            Log::info('✅ Email StatusCandidatura (aprovado) enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email candidatura aprovada: ' . $e->getMessage());
    }
}

public static function candidaturaRejeitada($candidatura, ?int $egressoIdForcar = null): void
{
    $egressoId = $egressoIdForcar ?? $candidatura->egresso_id;

    self::criarParaEgresso(
        $egressoId,
        "❌ Candidatura não aceite",
        "A tua candidatura a '{$candidatura->oportunidade->titulo}' não foi aceite.",
        'oportunidade',
        "/egresso/minhas-candidaturas"
    );

    try {
        $egresso = $candidatura->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new StatusCandidaturaMail($candidatura, 'rejeitado'));
            Log::info('✅ Email StatusCandidatura (rejeitado) enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email candidatura rejeitada: ' . $e->getMessage());
    }
}

 public static function entrevistaMarcada($candidatura, ?int $egressoIdForcar = null): void
{
    $egressoId = $egressoIdForcar ?? $candidatura->egresso_id;

    $data = $candidatura->data_entrevista
        ? $candidatura->data_entrevista->format('d/m/Y \à\s H:i')
        : 'a confirmar';

    self::criarParaEgresso(
        $egressoId,
        "📅 Entrevista marcada!",
        "'{$candidatura->oportunidade->titulo}' — Entrevista em {$data}",
        'oportunidade',
        "/egresso/minhas-candidaturas"
    );

    try {
        $egresso = $candidatura->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new StatusCandidaturaMail($candidatura, 'entrevista'));
            Log::info('✅ Email StatusCandidatura (entrevista) enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email entrevista marcada: ' . $e->getMessage());
    }
}

    // ============================================================
    // ✅ PRESENÇA CONFIRMADA
    // ============================================================
 public static function presencaConfirmada($candidatura): void
{
    try {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            self::criarParaAdmin(
                $admin->id,
                "✅ Presença confirmada",
                "{$candidatura->egresso->nome_completo} confirmou presença na entrevista de '{$candidatura->oportunidade->titulo}'",
                'oportunidade',
                "/admin/candidaturas/{$candidatura->id}"
            );
        }
    } catch (\Exception $e) {
        Log::warning('Erro ao notificar admins sobre presença: ' . $e->getMessage());
    }

    try {
        self::criarParaEgresso(
            $candidatura->egresso_id,
            "✅ Presença confirmada",
            "Confirmaste a tua presença na entrevista de '{$candidatura->oportunidade->titulo}'.",
            'oportunidade',
            "/egresso/candidaturas/{$candidatura->id}"
        );
    } catch (\Exception $e) {
        Log::warning('Erro ao notificar egresso sobre presença: ' . $e->getMessage());
    }

    // 📧 Email
    try {
        $egresso = $candidatura->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new PresencaConfirmadaEntrevistaMail($candidatura));
            Log::info('✅ Email PresencaConfirmadaEntrevista enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email presença confirmada: ' . $e->getMessage());
    }
}

    // ============================================================
    // 🎯 EVENTOS
    // ============================================================
    public static function novaInscricaoEvento($egresso, $evento, $inscricaoId): void
{
    try {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            self::criarParaAdmin(
                $admin->id,
                "🎪 Nova inscrição em evento",
                "{$egresso->nome_completo} inscreveu-se em '{$evento->titulo}'",
                'evento',
                "/admin/eventos/{$evento->id}/inscritos"
            );
        }
    } catch (\Exception $e) {
        Log::warning('Erro ao notificar admins (novaInscricaoEvento): ' . $e->getMessage());
    }

    try {
        self::criarParaEgresso(
            $egresso->id,
            "✅ Inscrição confirmada",
            "A tua inscrição em '{$evento->titulo}' foi confirmada.",
            'evento',
            "/egresso/minhas-inscricoes"
        );
    } catch (\Exception $e) {
        Log::warning('Erro ao notificar egresso (novaInscricaoEvento): ' . $e->getMessage());
    }

    // 📧 Email
    try {
        $inscricao = InscricaoEvento::with(['egresso', 'evento'])->find($inscricaoId);
        if ($inscricao && $inscricao->egresso && $inscricao->egresso->email) {
            Mail::to($inscricao->egresso->email)->send(new InscricaoConfirmadaMail($inscricao));
            Log::info('✅ Email InscricaoConfirmada enviado para: ' . $inscricao->egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email inscrição confirmada: ' . $e->getMessage());
    }
}

    public static function inscricaoCancelada($inscricao): void
    {
        $admins = Admin::all();

        foreach ($admins as $admin) {
            self::criarParaAdmin(
                $admin->id,
                "⚠️ Inscrição cancelada",
                "{$inscricao->egresso->nome_completo} cancelou a inscrição em '{$inscricao->evento->titulo}'",
                'evento',
                "/admin/eventos/{$inscricao->evento_id}/inscritos"
            );
        }
    }
public static function presencaMarcada($inscricao, bool $presente): void
{
    try {
        self::criarParaEgresso(
            $inscricao->egresso_id,
            $presente ? "✅ Presença confirmada" : "⚠️ Falta registada",
            "No evento '{$inscricao->evento->titulo}'",
            'evento',
            "/egresso/minhas-inscricoes"
        );
    } catch (\Exception $e) {
        Log::warning('Erro ao criar notificação presença: ' . $e->getMessage());
    }

    // 📧 Email
    try {
        $egresso = $inscricao->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new PresencaEventoMail($inscricao, $presente));
            Log::info('✅ Email PresencaEvento enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email presença evento: ' . $e->getMessage());
    }
}
    public static function lembreteEvento($inscricao): void
{
    $evento = $inscricao->evento;
    $hora   = $evento->data_inicio ? $evento->data_inicio->format('H:i') : '';

    try {
        self::criarParaEgresso(
            $inscricao->egresso_id,
            "⏰ Lembrete: evento amanhã",
            "'{$evento->titulo}' acontece amanhã às {$hora}",
            'evento',
            "/egresso/minhas-inscricoes"
        );
    } catch (\Exception $e) {
        Log::warning('Erro ao criar notificação lembrete: ' . $e->getMessage());
    }

    // 📧 Email
    try {
        $egresso = $inscricao->egresso;
        if ($egresso && $egresso->email) {
            Mail::to($egresso->email)->send(new LembreteEventoMail($inscricao, 'lembrete_24h'));
            Log::info('✅ Email LembreteEvento enviado para: ' . $egresso->email);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Erro ao enviar email lembrete evento: ' . $e->getMessage());
    }
}

    // ============================================================
    // 🔧 HELPERS
    // ============================================================
    private static function getIconePorTipo(?string $tipo): string
    {
        return match ($tipo) {
            'validacao'     => 'user-check',
            'feedback'      => 'comment-dots',
            'servico'       => 'concierge-bell',
            'conexao'       => 'handshake',
            'oportunidade'  => 'briefcase',
            'evento'        => 'calendar',
            'mensagem'      => 'envelope',
            'chamada_video' => 'video',
            'chamada_voz'   => 'phone',
            'audio'         => 'microphone',
            'ficheiro'      => 'paperclip',
            default         => 'bell',
        };
    }

    private static function getCorPorTipo(?string $tipo): string
    {
        return match ($tipo) {
            'validacao'     => 'warning',
            'feedback'      => 'info',
            'servico'       => 'danger',
            'conexao'       => 'success',
            'oportunidade'  => 'primary',
            'evento'        => 'info',
            'mensagem'      => 'primary',
            'chamada_video' => 'success',
            'chamada_voz'   => 'primary',
            'audio'         => 'info',
            'ficheiro'      => 'warning',
            default         => 'secondary',
        };
    }

    private static function getIniciais(string $nome): string
    {
        $partes = explode(' ', trim($nome));

        if (count($partes) >= 2) {
            return strtoupper(substr($partes[0], 0, 1) . substr($partes[count($partes) - 1], 0, 1));
        }

        return strtoupper(substr($nome, 0, 2));
    }

    // ============================================================
    // ✅ INSCRIÇÃO APROVADA
    // ============================================================
    public static function inscricaoAprovada(InscricaoEvento $inscricao): void
{
    Log::info('🔔 inscricaoAprovada chamado', [
        'inscricao_id' => $inscricao->id,
    ]);

    try {
        // Garantir que as relações estão carregadas
        if (!$inscricao->relationLoaded('egresso')) {
            $inscricao->load('egresso');
        }
        if (!$inscricao->relationLoaded('evento')) {
            $inscricao->load('evento');
        }

        $egresso = $inscricao->egresso;
        $evento  = $inscricao->evento;

        Log::info('🔔 inscricaoAprovada — dados', [
            'inscricao_id'  => $inscricao->id,
            'egresso_id'    => $inscricao->egresso_id,
            'egresso_email' => $egresso->email ?? 'SEM EMAIL',
            'evento_id'     => $inscricao->evento_id,
            'evento_titulo' => $evento->titulo ?? 'SEM EVENTO',
        ]);

        if (!$egresso) {
            Log::warning('🔔 inscricaoAprovada — egresso não encontrado', [
                'inscricao_id' => $inscricao->id,
                'egresso_id'   => $inscricao->egresso_id,
            ]);
            return;
        }

        if (!$evento) {
            Log::warning('🔔 inscricaoAprovada — evento não encontrado', [
                'inscricao_id' => $inscricao->id,
                'evento_id'    => $inscricao->evento_id,
            ]);
            return;
        }

        if (!$egresso->email) {
            Log::warning('🔔 inscricaoAprovada — egresso SEM email', [
                'egresso_id' => $egresso->id,
            ]);
            return;
        }

        // 📧 Enviar email
        try {
            Mail::to($egresso->email)->send(new InscricaoAprovada($inscricao));
            Log::info('✅ Email InscricaoAprovada enviado para: ' . $egresso->email);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email InscricaoAprovada: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        // 🔔 Notificação no sino
        try {
            self::criarParaEgresso(
                $egresso->id,
                '✅ Inscrição Aprovada',
                "A tua inscrição no evento «{$evento->titulo}» foi aprovada. Já podes descarregar o comprovativo.",
                'evento',
                route('egresso.minhas.inscricoes')
            );
        } catch (\Throwable $e) {
            Log::warning('inscricaoAprovada (notificacao): ' . $e->getMessage());
        }

    } catch (\Throwable $e) {
        Log::error('❌ inscricaoAprovada EXCEÇÃO: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

    // ============================================================
    // ❌ INSCRIÇÃO REJEITADA
    // ============================================================
    public static function inscricaoRejeitada(InscricaoEvento $inscricao): void
    {
        try {
            $egresso = $inscricao->egresso;
            $evento  = $inscricao->evento;

            if (!$egresso || !$evento) {
                return;
            }

            if ($egresso->email) {
                try {
                    Mail::to($egresso->email)->send(new InscricaoRejeitada($inscricao));
                } catch (\Throwable $e) {
                    Log::error('Erro ao enviar email de inscricao rejeitada: ' . $e->getMessage());
                }
            }

            try {
                $motivo = $inscricao->motivo_rejeicao
                    ? " Motivo: {$inscricao->motivo_rejeicao}"
                    : '';

                self::criarParaEgresso(
                    $egresso->id,
                    '❌ Inscrição Rejeitada',
                    "A tua inscrição no evento «{$evento->titulo}» foi rejeitada.{$motivo}",
                    'evento',
                    route('egresso.minhas.inscricoes')
                );
            } catch (\Throwable $e) {
                Log::warning('inscricaoRejeitada (notificacao): ' . $e->getMessage());
            }

        } catch (\Throwable $e) {
            Log::warning('inscricaoRejeitada: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🏢 EMPRESA — CRIAR NOTIFICAÇÃO
    // ============================================================
    public static function criarParaEmpresa(
        int $empresaId,
        string $titulo,
        string $mensagem,
        string $tipo = 'sistema',
        ?string $link = null
    ): ?Notificacao {
        try {
            $notif = Notificacao::create([
                'empresa_id' => $empresaId,
                'titulo'     => mb_substr($titulo, 0, 100),
                'mensagem'   => $mensagem,
                'tipo'       => $tipo,
                'link'       => $link,
                'lida'       => false,
            ]);

            try {
                broadcast(new NovaNotificacao($notif));
            } catch (\Exception $e) {
                Log::warning('Broadcast NovaNotificacao (empresa) falhou: ' . $e->getMessage());
            }

            return $notif;

        } catch (\Exception $e) {
            Log::error('Erro ao criar notificação para empresa: ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================
    // 🎯 NOVA CANDIDATURA NA EMPRESA
    // ============================================================
    public static function novaCandidaturaEmpresa($egresso, $oportunidade, $candidaturaId): void
    {
        if (!$oportunidade->empresa_id) {
            Log::warning('novaCandidaturaEmpresa: oportunidade sem empresa_id', [
                'oportunidade_id' => $oportunidade->id ?? null,
            ]);
            return;
        }

        self::criarParaEmpresa(
            $oportunidade->empresa_id,
            '📋 Nova candidatura recebida',
            "{$egresso->nome_completo} candidatou-se a '{$oportunidade->titulo}'",
            'oportunidade',
            "/empresa/candidaturas/{$candidaturaId}"
        );

        try {
            $empresa = $oportunidade->empresa ?? null;

            if (!$empresa || !$empresa->email) {
                Log::warning('novaCandidaturaEmpresa: empresa sem email', [
                    'empresa_id' => $oportunidade->empresa_id,
                ]);
                return;
            }

            $candidatura = Candidatura::with([
                'egresso.curso.unidade',
                'oportunidade.empresa',
            ])->find($candidaturaId);

            if ($candidatura) {
                Mail::to($empresa->email)
                    ->send(new NovaCandidaturaMail($candidatura));

                Log::info('✅ Email nova candidatura enviado para empresa: ' . $empresa->email);
            }
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao enviar email nova candidatura (empresa): ' . $e->getMessage());
        }
    }

    // ============================================================
    // ✅ CANDIDATURA APROVADA PELA EMPRESA
    // ============================================================
    public static function candidaturaStatusEmpresa($candidatura, string $novoStatus): void
    {
        if (!$candidatura->oportunidade || !$candidatura->oportunidade->empresa_id) {
            return;
        }

        $label = match($novoStatus) {
            'em_analise' => 'em análise',
            'entrevista' => 'em fase de entrevista',
            'aprovado'   => 'aprovada',
            'rejeitado'  => 'rejeitada',
            default      => $novoStatus,
        };

        self::criarParaEmpresa(
            $candidatura->oportunidade->empresa_id,
            "📊 Candidatura {$label}",
            "A candidatura de {$candidatura->egresso->nome_completo} foi marcada como {$label}.",
            'oportunidade',
            "/empresa/candidaturas/{$candidatura->id}"
        );
    }
}