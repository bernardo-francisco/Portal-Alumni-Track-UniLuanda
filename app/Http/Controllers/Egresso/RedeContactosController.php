<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Conexao;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RedeContactosController extends Controller
{
    /**
     * ============================================================
     * PÁGINA PRINCIPAL DA REDE DE CONTACTOS
     * ============================================================
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Utilizador não autenticado.');
        }

        $egresso = $user->egresso;

        if (!$egresso) {
            abort(
                403,
                'Este utilizador não possui um perfil de egresso.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CONEXÕES ACEITAS
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        | Incluímos:
        | - user_id
        | - user
        |
        | Isto permite à View usar a foto do User quando o Egresso
        | não tiver uma foto própria.
        |
        |--------------------------------------------------------------------------
        */

        $conexoes = Conexao::where('status', 'aceito')
            ->where(function ($q) use ($egresso) {
                $q->where('solicitante_id', $egresso->id)
                  ->orWhere('destinatario_id', $egresso->id);
            })
            ->with([
                'solicitante' => function ($q) {

                    $q->select(
                        'id',
                        'user_id',
                        'nome_completo',
                        'foto_url',
                        'curso_id'
                    )->with([
                        'curso:id,nome',
                        'user:id,name,email,photo_url,role,tipo,is_active'
                    ]);
                },

                'destinatario' => function ($q) {

                    $q->select(
                        'id',
                        'user_id',
                        'nome_completo',
                        'foto_url',
                        'curso_id'
                    )->with([
                        'curso:id,nome',
                        'user:id,name,email,photo_url,role,tipo,is_active'
                    ]);
                }
            ])
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SOLICITAÇÕES PENDENTES RECEBIDAS
        |--------------------------------------------------------------------------
        */

        $solicitacoes = Conexao::where('status', 'pendente')
            ->where('destinatario_id', $egresso->id)
            ->with([
                'solicitante' => function ($q) {

                    $q->select(
                        'id',
                        'user_id',
                        'nome_completo',
                        'foto_url',
                        'curso_id'
                    )->with([
                        'curso:id,nome',
                        'user:id,name,email,photo_url,role,tipo,is_active'
                    ]);
                }
            ])
            ->latest()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SUGESTÕES DE CONEXÃO
        |--------------------------------------------------------------------------
        */

        $idsComConexao = Conexao::where(function ($q) use ($egresso) {

                $q->where('solicitante_id', $egresso->id)
                  ->orWhere('destinatario_id', $egresso->id);

            })
            ->get([
                'solicitante_id',
                'destinatario_id'
            ])
            ->flatMap(function ($conexao) {

                return [
                    $conexao->solicitante_id,
                    $conexao->destinatario_id
                ];

            })
            ->unique()
            ->values()
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | SUGESTÕES
        |--------------------------------------------------------------------------
        */

        $sugestoes = Egresso::where('id', '!=', $egresso->id)
            ->where('status', 'active')
            ->whereNotIn('id', $idsComConexao)
            ->with([
                'curso:id,nome',
                'user:id,name,email,photo_url,role,tipo,is_active'
            ])
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'egresso.rede.index',
            compact(
                'conexoes',
                'solicitacoes',
                'sugestoes'
            )
        );
    }


    /**
     * ============================================================
     * ENVIAR SOLICITAÇÃO DE CONEXÃO
     * ============================================================
     */
    public function conectar($id)
    {
        $egressoLogado = Auth::user()->egresso;

        if (!$egressoLogado) {
            return redirect()
                ->route('egresso.rede')
                ->with(
                    'error',
                    'Perfil de egresso não encontrado.'
                );
        }

        $egresso = Egresso::find($id);

        if (!$egresso) {
            return redirect()
                ->route('egresso.rede')
                ->with(
                    'error',
                    'Egresso não encontrado.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | NÃO PODE CONECTAR CONSIGO MESMO
        |--------------------------------------------------------------------------
        */

        if ((int) $egresso->id === (int) $egressoLogado->id) {
            return redirect()
                ->route('egresso.rede')
                ->with(
                    'error',
                    'Não pode conectar-se a si mesmo.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR SE JÁ EXISTE CONEXÃO
        |--------------------------------------------------------------------------
        */

        $existe = Conexao::where(function ($q) use (
            $egressoLogado,
            $egresso
        ) {

            $q->where(
                'solicitante_id',
                $egressoLogado->id
            )->where(
                'destinatario_id',
                $egresso->id
            );

        })->orWhere(function ($q) use (
            $egressoLogado,
            $egresso
        ) {

            $q->where(
                'solicitante_id',
                $egresso->id
            )->where(
                'destinatario_id',
                $egressoLogado->id
            );

        })->exists();


        if ($existe) {

            return redirect()
                ->route('egresso.rede')
                ->with(
                    'error',
                    'Já existe uma solicitação ou conexão entre estes egressos.'
                );
        }


        try {

            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | CRIAR CONEXÃO
            |--------------------------------------------------------------------------
            */

            Conexao::create([
                'solicitante_id'  => $egressoLogado->id,
                'destinatario_id' => $egresso->id,
                'status'          => 'pendente',
            ]);


            /*
            |--------------------------------------------------------------------------
            | NOTIFICAÇÃO
            |--------------------------------------------------------------------------
            */

            Notificacao::create([
                'egresso_id' => $egresso->id,
                'tipo'       => 'conexao',
                'titulo'     => '📩 Nova solicitação de conexão',
                'mensagem'   =>
                    $egressoLogado->nome_completo .
                    ' deseja conectar-se a você.',
                'link'       => route('egresso.rede'),
                'lida'       => false,
            ]);


            DB::commit();


            return redirect()
                ->route('egresso.rede')
                ->with(
                    'success',
                    'Solicitação de conexão enviada com sucesso.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->route('egresso.rede')
                ->with(
                    'error',
                    'Erro ao enviar solicitação: ' .
                    $e->getMessage()
                );
        }
    }


    /**
     * ============================================================
     * ACEITAR CONEXÃO
     * ============================================================
     */
    public function aceitar($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return back()->with(
                'error',
                'Perfil de egresso não encontrado.'
            );
        }

        $conexao = Conexao::with([
            'solicitante',
            'destinatario'
        ])->find($id);

        if (!$conexao) {
            return back()->with(
                'error',
                'Solicitação não encontrada.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GARANTIR QUE O DESTINATÁRIO É O UTILIZADOR ATUAL
        |--------------------------------------------------------------------------
        */

        if ((int) $conexao->destinatario_id !== (int) $egresso->id) {

            return back()->with(
                'error',
                'Não autorizado.'
            );
        }


        if ($conexao->status !== 'pendente') {

            return back()->with(
                'error',
                'Esta solicitação já foi processada.'
            );
        }


        try {

            DB::beginTransaction();


            $conexao->update([
                'status' => 'aceito'
            ]);


            /*
            |--------------------------------------------------------------------------
            | NOTIFICAR SOLICITANTE
            |--------------------------------------------------------------------------
            */

            Notificacao::create([
                'egresso_id' => $conexao->solicitante_id,
                'tipo'       => 'conexao',
                'titulo'     => '✅ Conexão aceita',
                'mensagem'   =>
                    $egresso->nome_completo .
                    ' aceitou sua solicitação de conexão.',
                'link'       =>
                    route(
                        'egresso.mensagens.conversa',
                        $egresso->id
                    ),
                'lida'       => false,
            ]);


            DB::commit();


            return redirect()
                ->route('egresso.rede')
                ->with(
                    'success',
                    'Conexão aceita com sucesso.'
                );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Erro ao aceitar conexão: ' .
                $e->getMessage()
            );
        }
    }


    /**
     * ============================================================
     * RECUSAR CONEXÃO
     * ============================================================
     */
    public function recusar($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return back()->with(
                'error',
                'Perfil de egresso não encontrado.'
            );
        }


        $conexao = Conexao::with([
            'solicitante',
            'destinatario'
        ])->find($id);


        if (!$conexao) {

            return back()->with(
                'error',
                'Solicitação não encontrada.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GARANTIR DESTINATÁRIO
        |--------------------------------------------------------------------------
        */

        if ((int) $conexao->destinatario_id !== (int) $egresso->id) {

            return back()->with(
                'error',
                'Não autorizado.'
            );
        }


        if ($conexao->status !== 'pendente') {

            return back()->with(
                'error',
                'Esta solicitação já foi processada.'
            );
        }


        try {

            DB::beginTransaction();


            $conexao->update([
                'status' => 'recusado'
            ]);


            /*
            |--------------------------------------------------------------------------
            | NOTIFICAR SOLICITANTE
            |--------------------------------------------------------------------------
            */

            Notificacao::create([
                'egresso_id' => $conexao->solicitante_id,
                'tipo'       => 'conexao',
                'titulo'     => '❌ Conexão recusada',
                'mensagem'   =>
                    $egresso->nome_completo .
                    ' recusou sua solicitação de conexão.',
                'link'       => route('egresso.rede'),
                'lida'       => false,
            ]);


            DB::commit();


            return back()->with(
                'success',
                'Solicitação recusada.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Erro ao recusar conexão: ' .
                $e->getMessage()
            );
        }
    }
}