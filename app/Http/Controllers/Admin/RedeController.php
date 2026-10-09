<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Egresso;
use App\Models\Conexao;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RedeController extends Controller
{
    private function obterOuCriarPerfilAdminEgresso()
    {
        $user = Auth::user();
        $adminEgresso = $user->egresso;

        if (!$adminEgresso) {
            $adminEgresso = Egresso::create([
                'user_id'           => $user->id,
                'nome_completo'     => $user->name ?? 'Administrador do Sistema',
                'email'             => $user->email,
                'status'            => 'active',
                'verificado'        => true,
                'data_verificacao'  => now(),
                'numero_processo'   => 'ADMIN_' . time(),
            ]);
        }

        return $adminEgresso;
    }

public function index()
{
    $egresso = Auth::user()->egresso;

    if (!$egresso) {
        abort(403, 'Este utilizador não possui um perfil de egresso.');
    }

    /*
    |--------------------------------------------------------------------------
    | CONEXÕES ACEITAS
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
                    'user:id,name,photo_url,role,tipo'
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
                    'user:id,name,photo_url,role,tipo'
                ]);
            }
        ])
        ->latest()
        ->get();

    /*
    |--------------------------------------------------------------------------
    | SOLICITAÇÕES PENDENTES
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
                    'user:id,name,photo_url,role,tipo'
                ]);
            }
        ])
        ->latest()
        ->get();

    /*
    |--------------------------------------------------------------------------
    | IDs QUE JÁ POSSUEM CONEXÃO
    |--------------------------------------------------------------------------
    */
    $idsComConexao = Conexao::where('solicitante_id', $egresso->id)
        ->orWhere('destinatario_id', $egresso->id)
        ->pluck('solicitante_id')
        ->merge(
            Conexao::where('solicitante_id', $egresso->id)
                ->orWhere('destinatario_id', $egresso->id)
                ->pluck('destinatario_id')
        )
        ->unique()
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
            'user:id,name,photo_url,role,tipo'
        ])
        ->limit(10)
        ->get();

    return view(
        'egresso.rede.index',
        compact(
            'conexoes',
            'solicitacoes',
            'sugestoes'
        )
    );
}

    public function show(Egresso $egresso)
    {
        $egresso->load(['curso.unidade', 'localizacoes', 'profissionais']);
        return view('admin.rede.show', compact('egresso'));
    }

    /**
     * Enviar solicitação de conexão (Admin → Egresso)
     * Método: GET
     */
    public function conectar(Request $request, Egresso $egresso)
    {
        try {
            // 1. Obtém perfil do admin ou cria automaticamente
            $admin = $this->obterOuCriarPerfilAdminEgresso();

            // 2. Valida se não é ele mesmo
            if ($admin->id == $egresso->id) {
                return redirect()->route('admin.rede.index')->with('error', 'Não pode conectar-se a si mesmo.');
            }

            // 3. Verifica se já existe conexão
            $existe = Conexao::where(function($q) use ($admin, $egresso) {
                $q->where('solicitante_id', $admin->id)
                  ->where('destinatario_id', $egresso->id);
            })->orWhere(function($q) use ($admin, $egresso) {
                $q->where('solicitante_id', $egresso->id)
                  ->where('destinatario_id', $admin->id);
            })->exists();

            if ($existe) {
                return redirect()->route('admin.rede.index')->with('warning', 'Já existe uma solicitação de conexão com este egresso.');
            }

            DB::beginTransaction();

            // 4. Cria a solicitação pendente
            Conexao::create([
                'solicitante_id'  => $admin->id,
                'destinatario_id' => $egresso->id,
                'status'          => 'pendente',
            ]);

            // 5. 🔔 CRIA NOTIFICAÇÃO PARA O EGRESSO
            Notificacao::create([
                'egresso_id' => $egresso->id,
                'tipo'       => 'conexao',
                'titulo'     => '📩 Nova solicitação de conexão',
                'mensagem'   => 'O administrador ' . $admin->nome_completo . ' deseja conectar-se a você.',
                'link'       => route('egresso.rede'),
                'lida'       => false,
            ]);

            DB::commit();

            return redirect()->route('admin.rede.index')->with('success', 'Solicitação de conexão enviada para ' . $egresso->nome_completo . '!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar solicitação: ' . $e->getMessage());
            return redirect()->route('admin.rede.index')->with('error', 'Erro ao enviar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Aceitar solicitação de conexão (Admin recebeu)
     * Método: POST
     */
    public function aceitar(Request $request, $id)
    {
        $admin = $this->obterOuCriarPerfilAdminEgresso();

        $conexao = Conexao::find($id);

        if (!$conexao) {
            return back()->with('error', 'Solicitação não encontrada.');
        }

        if ($conexao->destinatario_id !== $admin->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            DB::beginTransaction();

            $conexao->update(['status' => 'aceito']);

            // 🔔 NOTIFICAR o solicitante
            Notificacao::create([
                'egresso_id' => $conexao->solicitante_id,
                'tipo'       => 'conexao',
                'titulo'     => '✅ Conexão aceita',
                'mensagem'   => $admin->nome_completo . ' aceitou sua solicitação de conexão.',
                'link'       => route('admin.rede.index'),
                'lida'       => false,
            ]);

            DB::commit();

            return back()->with('success', 'Conexão aceita com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aceitar conexão: ' . $e->getMessage());
        }
    }

    /**
     * Recusar solicitação de conexão (Admin recebeu)
     * Método: POST
     */
    public function recusar(Request $request, $id)
    {
        $admin = $this->obterOuCriarPerfilAdminEgresso();

        $conexao = Conexao::find($id);

        if (!$conexao) {
            return back()->with('error', 'Solicitação não encontrada.');
        }

        if ($conexao->destinatario_id !== $admin->id) {
            return back()->with('error', 'Não autorizado.');
        }

        try {
            $conexao->update(['status' => 'recusado']);

            // 🔔 NOTIFICAR o solicitante
            Notificacao::create([
                'egresso_id' => $conexao->solicitante_id,
                'tipo'       => 'conexao',
                'titulo'     => '❌ Conexão recusada',
                'mensagem'   => $admin->nome_completo . ' recusou sua solicitação de conexão.',
                'link'       => route('admin.rede.index'),
                'lida'       => false,
            ]);

            return back()->with('success', 'Solicitação recusada.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao recusar conexão: ' . $e->getMessage());
        }
    }
}