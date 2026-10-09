<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egresso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContaAprovadaMail;
use App\Mail\ContaReprovadaMail;

class ValidacaoEgressosController extends Controller
{
    /**
     * ============================================================
     * VERIFICA SE UM EGRESSO É, NA VERDADE, UM ADMINISTRADOR
     * ============================================================
     *
     * Um perfil não será considerado egresso se:
     *
     * 1. O número de processo começar por ADMIN_
     * OU
     * 2. O utilizador associado tiver role = admin
     * OU
     * 3. O utilizador associado tiver tipo = admin
     */
    private function ehAdministrador(Egresso $egresso): bool
    {
        // Regra 1: número de processo reservado para administradores
        if (
            !empty($egresso->numero_processo) &&
            str_starts_with($egresso->numero_processo, 'ADMIN_')
        ) {
            return true;
        }

        // Regra 2 e 3: verificar utilizador associado
        if ($egresso->user) {
            return (
                $egresso->user->role === 'admin' ||
                $egresso->user->tipo === 'admin'
            );
        }

        return false;
    }


    /**
     * ============================================================
     * CONDIÇÃO BASE PARA EGRESSOS REAIS
     * ============================================================
     *
     * Exclui:
     * - perfis ADMIN_
     * - utilizadores com role = admin
     * - utilizadores com tipo = admin
     *
     * Perfis sem user continuam permitidos, porque podem ser
     * egressos "fantasma" que precisam de ser corrigidos.
     */
    private function queryEgressosReais()
    {
        return Egresso::query()
            ->where(function ($query) {

                // Excluir qualquer número de processo reservado ao admin
                $query->whereNull('numero_processo')
                    ->orWhere('numero_processo', 'NOT LIKE', 'ADMIN_%');
            })
            ->where(function ($query) {

                /*
                 * Permitir:
                 *
                 * - egresso sem utilizador associado
                 * OU
                 * - egresso cujo utilizador NÃO seja admin
                 */
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($userQuery) {

                        $userQuery
                            ->where(function ($q) {
                                $q->whereNull('role')
                                  ->orWhere('role', '!=', 'admin');
                            })
                            ->where(function ($q) {
                                $q->whereNull('tipo')
                                  ->orWhere('tipo', '!=', 'admin');
                            });
                    });
            });
    }


    /**
     * ============================================================
     * LISTA OS EGRESSOS PENDENTES DE VALIDAÇÃO
     * ============================================================
     */
    public function index()
    {
        /*
         * IMPORTANTE:
         *
         * Antes:
         * Egresso::where('status_validacao', 'pendente')
         *
         * Isso permitia que o ADMIN aparecesse.
         *
         * Agora usamos queryEgressosReais().
         */
        $pendentes = $this->queryEgressosReais()
            ->with(['curso', 'unidade', 'user'])
            ->where('status_validacao', 'pendente')
            ->orderBy('created_at', 'asc')
            ->paginate(20);


        /**
         * ========================================================
         * ESTATÍSTICAS
         * ========================================================
         *
         * Todas as estatísticas agora excluem administradores.
         */
        $pendentesCount = $this->queryEgressosReais()
            ->where('status_validacao', 'pendente')
            ->count();

        $aprovadosCount = $this->queryEgressosReais()
            ->where('status_validacao', 'aprovado')
            ->count();

        $reprovadosCount = $this->queryEgressosReais()
            ->where('status_validacao', 'reprovado')
            ->count();

        $totalCount = $this->queryEgressosReais()
            ->count();


        $stats = [
            'pendentes'  => $pendentesCount,
            'aprovados'  => $aprovadosCount,
            'reprovados' => $reprovadosCount,
            'total'      => $totalCount,
        ];


        return view(
            'admin.validacao.index',
            compact('pendentes', 'stats')
        );
    }


    /**
     * ============================================================
     * EXIBE OS DETALHES DE UM EGRESSO
     * ============================================================
     */
    public function show($id)
    {
        $egresso = Egresso::with(['curso', 'unidade', 'user'])
            ->findOrFail($id);


        /*
         * SEGURANÇA:
         *
         * Mesmo que alguém tente acessar diretamente:
         *
         * /admin/validacao/ID_DO_ADMIN
         *
         * o administrador não será tratado como egresso.
         */
        if ($this->ehAdministrador($egresso)) {

            return redirect()
                ->route('admin.validacao.index')
                ->with(
                    'warning',
                    'O perfil do administrador não pode ser tratado como egresso.'
                );
        }


        return view(
            'admin.validacao.show',
            compact('egresso')
        );
    }


    /**
     * ============================================================
     * APROVAR EGRESSO
     * ============================================================
     */
    public function aprovar($id, Request $request)
    {
        try {

            DB::beginTransaction();


            /*
             * Procuramos o egresso normalmente.
             */
            $egresso = Egresso::with('user')
                ->findOrFail($id);


            /*
             * SEGURANÇA:
             * Nunca permitir aprovação do perfil do administrador.
             */
            if ($this->ehAdministrador($egresso)) {

                DB::rollBack();

                return back()->with(
                    'error',
                    'O perfil do administrador não pode ser aprovado como egresso.'
                );
            }


            /*
             * Verificar se ainda está pendente.
             */
            if ($egresso->status_validacao !== 'pendente') {

                DB::rollBack();

                return back()->with(
                    'warning',
                    'Este egresso já foi validado.'
                );
            }


            /*
             * Aprovar egresso.
             */
            $egresso->update([
                'status_validacao'       => 'aprovado',
                'verificado'             => true,
                'data_validacao'         => now(),
                'validado_por'           => Auth::id(),
                'status'                 => 'active',
                'observacoes_validacao' => $request->observacoes
                    ?? 'Aprovado pela administração.',
            ]);


            /*
             * Ativar conta do egresso.
             *
             * O administrador nunca chegará aqui devido à
             * validação acima.
             */
            if ($egresso->user) {

                $egresso->user->update([
                    'is_active' => true,
                ]);
            }


            DB::commit();


            /**
             * ====================================================
             * EMAIL DE APROVAÇÃO
             * ====================================================
             */
            try {

                if (!empty($egresso->email)) {

                    Mail::to($egresso->email)
                        ->send(new ContaAprovadaMail($egresso));

                    Log::info(
                        'Email de aprovação enviado para: '
                        . $egresso->email
                    );
                }

            } catch (\Throwable $e) {

                Log::error(
                    'Erro ao enviar email de aprovação para '
                    . $egresso->email
                    . ': '
                    . $e->getMessage()
                );
            }


            return redirect()
                ->route('admin.validacao.index')
                ->with(
                    'success',
                    "Egresso '{$egresso->nome_completo}' aprovado com sucesso!"
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Erro ao aprovar egresso.',
                [
                    'egresso_id' => $id,
                    'admin_id'   => Auth::id(),
                    'message'    => $e->getMessage(),
                ]
            );


            return back()->with(
                'error',
                'Erro ao aprovar o egresso: ' . $e->getMessage()
            );
        }
    }


    /**
     * ============================================================
     * REPROVAR EGRESSO
     * ============================================================
     */
    public function reprovar($id, Request $request)
    {
        /*
         * Validar motivo.
         */
        $request->validate([
            'motivo_reprovacao' => 'required|string|min:10|max:500',
        ]);


        try {

            DB::beginTransaction();


            $egresso = Egresso::with('user')
                ->findOrFail($id);


            /*
             * SEGURANÇA:
             *
             * Nunca permitir reprovar o administrador
             * como se fosse egresso.
             */
            if ($this->ehAdministrador($egresso)) {

                DB::rollBack();

                return back()->with(
                    'error',
                    'O perfil do administrador não pode ser reprovado como egresso.'
                );
            }


            /*
             * Verificar se ainda está pendente.
             */
            if ($egresso->status_validacao !== 'pendente') {

                DB::rollBack();

                return back()->with(
                    'warning',
                    'Este egresso já foi validado.'
                );
            }


            /*
             * Reprovar egresso.
             */
            $egresso->update([
                'status_validacao'       => 'reprovado',
                'motivo_reprovacao'      => $request->motivo_reprovacao,
                'data_validacao'         => now(),
                'validado_por'           => Auth::id(),
                'status'                 => 'blocked',
                'observacoes_validacao' => $request->observacoes ?? null,
            ]);


            /*
             * Desativar conta do egresso.
             */
            if ($egresso->user) {

                $egresso->user->update([
                    'is_active' => false,
                ]);
            }


            DB::commit();


            /**
             * ====================================================
             * EMAIL DE REPROVAÇÃO
             * ====================================================
             */
            try {

                if (!empty($egresso->email)) {

                    Mail::to($egresso->email)
                        ->send(
                            new ContaReprovadaMail(
                                $egresso,
                                $request->motivo_reprovacao
                            )
                        );

                    Log::info(
                        'Email de reprovação enviado para: '
                        . $egresso->email
                    );
                }

            } catch (\Throwable $e) {

                Log::error(
                    'Erro ao enviar email de reprovação para '
                    . $egresso->email
                    . ': '
                    . $e->getMessage()
                );
            }


            return redirect()
                ->route('admin.validacao.index')
                ->with(
                    'warning',
                    "Egresso '{$egresso->nome_completo}' reprovado."
                );


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Erro ao reprovar egresso.',
                [
                    'egresso_id' => $id,
                    'admin_id'   => Auth::id(),
                    'message'    => $e->getMessage(),
                ]
            );


            return back()->with(
                'error',
                'Erro ao reprovar o egresso: ' . $e->getMessage()
            );
        }
    }
}