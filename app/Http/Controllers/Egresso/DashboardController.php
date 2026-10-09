<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Oportunidade;
use App\Models\Evento;
use App\Models\Conexao;
use App\Models\Notificacao;
use App\Models\MuralNoticia;
use App\Models\MuralComentario;
use App\Models\MuralCurtida;
use App\Models\Mensagem;
use App\Models\Localizacao;
use App\Models\Servico;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $egresso = $user->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                            ->with('warning', 'Complete seu perfil para aceder ao dashboard.');
        }

        // ============================================
        // 1. ESTATÍSTICAS PRINCIPAIS (CORRIGIDO)
        // ============================================
        $stats = [
            'conexoes' => Conexao::where(function($q) use ($egresso) {
                                $q->where('solicitante_id', $egresso->id)
                                  ->orWhere('destinatario_id', $egresso->id);
                            })
                            ->where('status', 'aceito')
                            ->count(),
            'oportunidades' => Oportunidade::where('is_active', true)->count(),
            'eventos' => Evento::where('is_active', true)->count(), // ✅ CORRIGIDO: Conta TODOS os eventos ativos
            'notificacoes_nao_lidas' => Notificacao::where('egresso_id', $egresso->id)
                                                   ->where('lida', false)
                                                   ->count(),
            'total_egressos' => Egresso::where('status', 'active')
                ->whereDoesntHave('user', function($q) {
                    $q->where('role', 'admin')
                      ->orWhere('tipo', 'admin');
                })
                ->count(),
            'total_oportunidades' => Oportunidade::where('is_active', true)->count(),
            'total_eventos' => Evento::where('is_active', true)->count(),
            'conexoes_totais' => Conexao::where('status', 'aceito')->count(),
        ];

        // ============================================
        // 2. DADOS PARA A DISTRIBUIÇÃO DA COMUNIDADE
        // ============================================
        $paises = Localizacao::where('is_current', true)
                             ->whereNotNull('pais')
                             ->whereHas('egresso', function($q) {
                                 $q->where('status', 'active')
                                   ->whereDoesntHave('user', function($q2) {
                                       $q2->where('role', 'admin')
                                         ->orWhere('tipo', 'admin');
                                   });
                             })
                             ->select('pais', DB::raw('count(distinct egresso_id) as total'))
                             ->groupBy('pais')
                             ->orderBy('total', 'desc')
                             ->get();

        $bandeiras = [
            'Angola' => '🇦🇴',
            'Portugal' => '🇵🇹',
            'Brasil' => '🇧🇷',
            'Estados Unidos' => '🇺🇸',
            'EUA' => '🇺🇸',
            'Reino Unido' => '🇬🇧',
            'França' => '🇫🇷',
            'Alemanha' => '🇩🇪',
            'Espanha' => '🇪🇸',
            'Itália' => '🇮🇹',
            'Japão' => '🇯🇵',
            'China' => '🇨🇳',
            'Austrália' => '🇦🇺',
            'Canadá' => '🇨🇦',
            'Cabo Verde' => '🇨🇻',
            'Moçambique' => '🇲🇿',
            'África do Sul' => '🇿🇦',
        ];

        $totalPaises = $paises->count();

        // ============================================
        // 3. MENSAGENS NÃO LIDAS
        // ============================================
        try {
            $stats['mensagens_nao_lidas'] = Mensagem::where('destinatario_id', $egresso->id)
                                                    ->where('lida', false)
                                                    ->count() ?? 0;
        } catch (\Exception $e) {
            $stats['mensagens_nao_lidas'] = 0;
        }

        // ============================================
        // 4. OPORTUNIDADES RECENTES
        // ============================================
        $oportunidades_recentes = Oportunidade::with('unidade')
                                             ->where('is_active', true)
                                             ->latest()
                                             ->take(5)
                                             ->get();

        // ============================================
        // 5. EVENTOS (TODOS OS ATIVOS)
        // ============================================
        // ✅ CORRIGIDO: Mostra todos os eventos ativos
        $eventos_proximos = Evento::with('unidade')
                                  ->where('is_active', true)
                                  ->where('data_inicio', '>=', now())
                                  ->orderBy('data_inicio', 'asc')
                                  ->take(5)
                                  ->get();

        // ✅ CORRIGIDO: Buscar também eventos passados
        $eventos_passados = Evento::with('unidade')
                                  ->where('is_active', true)
                                  ->where('data_inicio', '<', now())
                                  ->orderBy('data_inicio', 'desc')
                                  ->take(5)
                                  ->get();

        // ✅ CORRIGIDO: Buscar todos os eventos ativos
        $todos_eventos_ativos = Evento::where('is_active', true)->count();

        // ============================================
        // 6. ATIVIDADES RECENTES
        // ============================================
        $atividades = $this->getAtividadesRecentes($egresso);

        // ============================================
        // 7. MURAL DE RECADOS
        // ============================================
        $mural = $this->getMuralRecados($egresso);

        // ============================================
        // 8. CONEXÕES RECENTES
        // ============================================
        try {
            $conexoes_recentes = Conexao::with(['solicitante' => function($q) {
                                            $q->select('id', 'nome_completo', 'foto_url');
                                        }, 'destinatario' => function($q) {
                                            $q->select('id', 'nome_completo', 'foto_url');
                                        }])
                                        ->where(function($q) use ($egresso) {
                                            $q->where('solicitante_id', $egresso->id)
                                              ->orWhere('destinatario_id', $egresso->id);
                                        })
                                        ->where('status', 'aceito')
                                        ->latest()
                                        ->take(6)
                                        ->get();
        } catch (\Exception $e) {
            $conexoes_recentes = collect();
        }

        // ============================================
        // 9. NOTIFICAÇÕES PARA O DROPDOWN
        // ============================================
        try {
            $notificacoes = Notificacao::where('egresso_id', $egresso->id)
                                       ->where('lida', false)
                                       ->latest()
                                       ->take(5)
                                       ->get()
                                       ->map(function($notif) {
                                           return [
                                               'icone' => 'bell',
                                               'cor' => 'primary',
                                               'titulo' => $notif->titulo,
                                               'descricao' => $notif->mensagem,
                                               'tempo' => $notif->created_at->diffForHumans(),
                                               'link' => $notif->link ?? '#',
                                           ];
                                       });
        } catch (\Exception $e) {
            $notificacoes = collect();
        }

        // ============================================
        // 10. MENSAGENS PARA O DROPDOWN
        // ============================================
        try {
            $mensagens = Mensagem::where('destinatario_id', $egresso->id)
                                 ->where('lida', false)
                                 ->with('remetente')
                                 ->latest()
                                 ->take(5)
                                 ->get()
                                 ->map(function($msg) {
                                     $remetente = $msg->remetente;
                                     return [
                                         'iniciais' => $remetente ? strtoupper(substr($remetente->name, 0, 2)) : '??',
                                         'cor' => 'linear-gradient(135deg, #1a56db, #3b82f6)',
                                         'nome' => $remetente->name ?? 'Usuário',
                                         'mensagem' => Str::limit($msg->conteudo, 50),
                                         'hora' => $msg->created_at->format('H:i'),
                                         'online' => false,
                                         'link' => route('egresso.mensagens.conversa', $remetente->id ?? 0),
                                     ];
                                 });
        } catch (\Exception $e) {
            $mensagens = collect();
        }

        return view('egresso.dashboard', compact(
            'egresso',
            'stats',
            'oportunidades_recentes',
            'eventos_proximos',
            'eventos_passados',
            'todos_eventos_ativos',
            'atividades',
            'mural',
            'conexoes_recentes',
            'notificacoes',
            'mensagens',
            'paises',
            'totalPaises',
            'bandeiras'
        ));
    }

    /**
     * Busca as atividades recentes do egresso (ÚLTIMOS 7 DIAS)
     * Registra TODAS as atividades do egresso
     */
    private function getAtividadesRecentes($egresso)
{
    $atividades = $this->buscarAtividades($egresso, now()->subDays(30));

    if (count($atividades) === 0) {
        $atividades = $this->buscarAtividades($egresso, null); // sem filtro
    }

    usort($atividades, function($a, $b) {
        $ta = $a['data_raw'] instanceof \Carbon\Carbon ? $a['data_raw']->timestamp : strtotime($a['data_raw']);
        $tb = $b['data_raw'] instanceof \Carbon\Carbon ? $b['data_raw']->timestamp : strtotime($b['data_raw']);
        return $tb - $ta;
    });

    return array_slice($atividades, 0, 20);
}

private function buscarAtividades($egresso, $dataLimite = null)
{
    $atividades = [];

    $filtrarData = function($query) use ($dataLimite) {
        if ($dataLimite !== null) {
            $query->where('created_at', '>=', $dataLimite);
        }
    };

    // 1. CONEXÕES
    try {
        $q = \App\Models\Conexao::where(function($q) use ($egresso) {
                $q->where('solicitante_id', $egresso->id)
                  ->orWhere('destinatario_id', $egresso->id);
            })
            ->where('status', 'aceito')
            ->with(['solicitante', 'destinatario'])
            ->latest();
        $filtrarData($q);

        foreach ($q->get() as $c) {
            $contato = $c->solicitante_id == $egresso->id ? $c->destinatario : $c->solicitante;
            if ($contato) {
                $atividades[] = [
                    'tipo'      => 'success',
                    'icone'     => 'fa-user-plus',
                    'descricao' => 'Conectou-se com <strong>' . e($contato->nome_completo) . '</strong>',
                    'data'      => $c->created_at->diffForHumans(),
                    'data_raw'  => $c->created_at,
                ];
            }
        }
    } catch (\Exception $e) { Log::warning('Conexões: ' . $e->getMessage()); }

    // 2. CURTIDAS (usa tabela 'mural_curtidas')
    try {
        $q = \App\Models\MuralCurtida::where('egresso_id', $egresso->id)->with('muralNoticia')->latest();
        $filtrarData($q);
        foreach ($q->get() as $c) {
            if ($c->muralNoticia) {
                $atividades[] = [
                    'tipo'      => 'danger',
                    'icone'     => 'fa-heart',
                    'descricao' => 'Curtiu: <strong>' . e(Str::limit($c->muralNoticia->titulo, 40)) . '</strong>',
                    'data'      => $c->created_at->diffForHumans(),
                    'data_raw'  => $c->created_at,
                ];
            }
        }
    } catch (\Exception $e) { Log::warning('Curtidas: ' . $e->getMessage()); }

    // 3. COMENTÁRIOS (usa tabela 'mural_comentarios')
    try {
        $q = \App\Models\MuralComentario::where('egresso_id', $egresso->id)->with('muralNoticia')->latest();
        $filtrarData($q);
        foreach ($q->get() as $c) {
            if ($c->muralNoticia) {
                $atividades[] = [
                    'tipo'      => 'info',
                    'icone'     => 'fa-comment',
                    'descricao' => 'Comentou em: <strong>' . e(Str::limit($c->muralNoticia->titulo, 40)) . '</strong>',
                    'data'      => $c->created_at->diffForHumans(),
                    'data_raw'  => $c->created_at,
                ];
            }
        }
    } catch (\Exception $e) { Log::warning('Comentários: ' . $e->getMessage()); }

    // 4. SERVIÇOS
    try {
        $q = \App\Models\Servico::where('egresso_id', $egresso->id)->latest();
        $filtrarData($q);
        $labels = ['pendente' => 'Pendente', 'andamento' => 'Em Andamento', 'atendido' => 'Atendido', 'cancelado' => 'Cancelado'];
        foreach ($q->get() as $s) {
            $atividades[] = [
                'tipo'      => $s->status == 'pendente' ? 'warning' : ($s->status == 'atendido' ? 'success' : 'info'),
                'icone'     => 'fa-concierge-bell',
                'descricao' => 'Solicitou: <strong>' . e($s->servico) . '</strong> (' . ($labels[$s->status] ?? $s->status) . ')',
                'data'      => $s->created_at->diffForHumans(),
                'data_raw'  => $s->created_at,
            ];
        }
    } catch (\Exception $e) { Log::warning('Serviços: ' . $e->getMessage()); }

    // 5. FEEDBACKS
    try {
        $q = \App\Models\Feedback::where('egresso_id', $egresso->id)->latest();
        $filtrarData($q);
        $labels = ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'rejeitado' => 'Rejeitado'];
        foreach ($q->get() as $f) {
            $atividades[] = [
                'tipo'      => $f->status == 'pendente' ? 'warning' : ($f->status == 'aprovado' ? 'success' : 'danger'),
                'icone'     => 'fa-comment-dots',
                'descricao' => 'Enviou feedback: <strong>' . e($f->titulo) . '</strong> (' . ($labels[$f->status] ?? $f->status) . ')',
                'data'      => $f->created_at->diffForHumans(),
                'data_raw'  => $f->created_at,
            ];
        }
    } catch (\Exception $e) { Log::warning('Feedbacks: ' . $e->getMessage()); }

    // 6. PUBLICAÇÕES (tabela 'mural_noticias')
    try {
        $q = \App\Models\MuralNoticia::where('egresso_id', $egresso->id)->latest();
        $filtrarData($q);
        foreach ($q->get() as $p) {
            $atividades[] = [
                'tipo'      => 'primary',
                'icone'     => 'fa-newspaper',
                'descricao' => 'Publicou: <strong>' . e(Str::limit($p->titulo, 40)) . '</strong>',
                'data'      => $p->created_at->diffForHumans(),
                'data_raw'  => $p->created_at,
            ];
        }
    } catch (\Exception $e) { Log::warning('Publicações: ' . $e->getMessage()); }

    // 7. INSCRIÇÕES EM EVENTOS (tabela 'inscricoes_eventos')
    try {
        $q = \App\Models\InscricaoEvento::where('egresso_id', $egresso->id)
            ->with('evento')
            ->latest();
        $filtrarData($q);

        foreach ($q->get() as $i) {
            $titulo = $i->evento->titulo ?? 'Evento';
            $atividades[] = [
                'tipo'      => 'info',
                'icone'     => 'fa-calendar-check',
                'descricao' => 'Inscreveu-se no evento: <strong>' . e(Str::limit($titulo, 40)) . '</strong>',
                'data'      => $i->created_at->diffForHumans(),
                'data_raw'  => $i->created_at,
            ];
        }
    } catch (\Exception $e) { Log::warning('Inscrições: ' . $e->getMessage()); }

    // 8. CANDIDATURAS
    try {
        $q = DB::table('candidaturas')
            ->where('egresso_id', $egresso->id)
            ->join('oportunidades', 'candidaturas.oportunidade_id', '=', 'oportunidades.id')
            ->select('candidaturas.*', 'oportunidades.titulo')
            ->latest('candidaturas.created_at');

        if ($dataLimite !== null) {
            $q->where('candidaturas.created_at', '>=', $dataLimite);
        }

        $labels = ['pendente' => 'Pendente', 'analise' => 'Em Análise', 'aceito' => 'Aceito', 'recusado' => 'Recusado'];

        foreach ($q->get() as $c) {
            $dt = \Carbon\Carbon::parse($c->created_at);
            $atividades[] = [
                'tipo'      => $c->status == 'pendente' ? 'warning' : ($c->status == 'aceito' ? 'success' : 'danger'),
                'icone'     => 'fa-briefcase',
                'descricao' => 'Candidatou-se a: <strong>' . e(Str::limit($c->titulo, 40)) . '</strong> (' . ($labels[$c->status] ?? $c->status) . ')',
                'data'      => $dt->diffForHumans(),
                'data_raw'  => $dt,
            ];
        }
    } catch (\Exception $e) { Log::warning('Candidaturas: ' . $e->getMessage()); }

    // ============================================================
    // 9. RESPOSTAS A PESQUISAS (tabela 'pesquisas_respostas') ← A QUE FALTAVA
    // ============================================================
    try {
        $q = \App\Models\PesquisaResposta::where('egresso_id', $egresso->id)
            ->with('pesquisa')
            ->latest();
        $filtrarData($q);

        $agrupadas = $q->get()->groupBy('pesquisa_id');

        foreach ($agrupadas as $respostas) {
            $primeira   = $respostas->first();
            $total      = $respostas->count();
            $ultimaData = $respostas->max('created_at');

            if ($primeira && $primeira->pesquisa) {
                $dt = \Carbon\Carbon::parse($ultimaData);
                $atividades[] = [
                    'tipo'      => 'primary',
                    'icone'     => 'fa-poll',
                    'descricao' => 'Respondeu à pesquisa: <strong>'
                                    . e(Str::limit($primeira->pesquisa->titulo, 40))
                                    . "</strong> ({$total} " . ($total == 1 ? 'resposta' : 'respostas') . ')',
                    'data'      => $dt->diffForHumans(),
                    'data_raw'  => $dt,
                ];
            }
        }
    } catch (\Exception $e) { Log::warning('Pesquisas: ' . $e->getMessage()); }

    return $atividades;
}
    /**
     * Busca os recados do mural
     */
    /**
 * Busca os recados do mural
 */
private function getMuralRecados($egresso)
{
    $mural = [];

    try {
        $publicacoes = MuralNoticia::where('publicado', true)
                                   ->with([
                                       'admin' => function($q) {
                                           $q->select('id', 'name', 'photo_url');
                                       },
                                       'egresso' => function($q) {
                                           $q->select('id', 'nome_completo', 'foto_url');
                                       }
                                   ])
                                   ->orderBy('destaque', 'desc')
                                   ->orderBy('created_at', 'desc')
                                   ->take(6)
                                   ->get();

        foreach ($publicacoes as $pub) {
            $autor = $pub->autor;

            $curtiu = MuralCurtida::where('mural_noticia_id', $pub->id)
                                  ->where('egresso_id', $egresso->id)
                                  ->exists();

            $totalCurtidas = MuralCurtida::where('mural_noticia_id', $pub->id)->count();
            $totalComentarios = MuralComentario::where('mural_noticia_id', $pub->id)->count();

            $mural[] = [
                'id' => $pub->id,
                'titulo' => $pub->titulo,
                'texto' => Str::limit($pub->conteudo, 150),
                'tipo' => $pub->tipo,
                'tipo_label' => $pub->getTipoLabelAttribute(),
                'imagem_url' => $pub->imagem_url,
                'data_evento' => $pub->data_evento,
                'local' => $pub->local,
                'destaque' => $pub->destaque,
                'autor' => $autor['nome'],
                'autor_tipo' => $autor['tipo'],
                'autor_foto' => $autor['foto'],           // ✅ já passa a foto
                'autor_id' => $pub->admin_id ?? $pub->egresso_id,
                'data' => $pub->created_at->diffForHumans(),
                'data_raw' => $pub->created_at,
                'data_formatada' => $pub->created_at->format('d/m/Y H:i'),
                'curtiu' => $curtiu,
                'total_curtidas' => $totalCurtidas,
                'total_comentarios' => $totalComentarios,
            ];
        }
    } catch (\Exception $e) {
        Log::error('Erro ao buscar mural:', ['error' => $e->getMessage()]);
    }

    return $mural;
}

    /**
     * Realiza uma pesquisa global no sistema
     */
    public function pesquisar(Request $request)
    {
        $termo = $request->get('q', '');
        
        if (empty($termo) || strlen($termo) < 2) {
            return back()->with('info', 'Digite pelo menos 2 caracteres para pesquisar.');
        }

        // Buscar Egressos (exclui admins)
        $egressos = Egresso::where('status', 'active')
            ->whereDoesntHave('user', function($q) {
                $q->where('role', 'admin')
                  ->orWhere('tipo', 'admin');
            })
            ->where(function($q) use ($termo) {
                $q->where('nome_completo', 'LIKE', "%{$termo}%")
                  ->orWhere('numero_processo', 'LIKE', "%{$termo}%")
                  ->orWhere('email', 'LIKE', "%{$termo}%");
            })
            ->with(['curso', 'curso.unidade'])
            ->limit(5)
            ->get();

        // Buscar Oportunidades
        $oportunidades = Oportunidade::where('is_active', true)
            ->where(function($q) use ($termo) {
                $q->where('titulo', 'LIKE', "%{$termo}%")
                  ->orWhere('empresa', 'LIKE', "%{$termo}%")
                  ->orWhere('descricao', 'LIKE', "%{$termo}%");
            })
            ->with('unidade')
            ->limit(5)
            ->get();

        // Buscar Eventos
        $eventos = Evento::where('is_active', true)
            ->where(function($q) use ($termo) {
                $q->where('titulo', 'LIKE', "%{$termo}%")
                  ->orWhere('local', 'LIKE', "%{$termo}%")
                  ->orWhere('descricao', 'LIKE', "%{$termo}%");
            })
            ->with('unidade')
            ->limit(5)
            ->get();

        // Buscar Publicações do Mural
        $publicacoes = MuralNoticia::where('publicado', true)
            ->where(function($q) use ($termo) {
                $q->where('titulo', 'LIKE', "%{$termo}%")
                  ->orWhere('conteudo', 'LIKE', "%{$termo}%");
            })
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalResultados = $egressos->count() + $oportunidades->count() + 
                           $eventos->count() + $publicacoes->count();

        return view('egresso.pesquisar.resultados', compact(
            'termo',
            'egressos',
            'oportunidades',
            'eventos',
            'publicacoes',
            'totalResultados'
        ));
    }
}