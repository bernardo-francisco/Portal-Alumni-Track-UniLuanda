<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Registar uma acção de auditoria
     */
    public static function auditar(
        string $acao,
        ?string $alvoTipo = null,
        ?int $alvoId = null,
        ?string $descricao = null,
        array $dadosAntes = [],
        array $dadosDepois = [],
        string $severidade = 'info'
    ): void {
        $admin = Auth::user()?->admin;

        AuditLog::create([
            'admin_id'     => $admin?->id,
            'admin_email'  => Auth::user()?->email,
            'admin_nome'   => $admin?->nome_completo,

            'acao'         => $acao,
            'alvo_tipo'    => $alvoTipo,
            'alvo_id'      => $alvoId,
            'descricao'    => $descricao,

            'dados_antes'  => $dadosAntes,
            'dados_depois' => $dadosDepois,

            'ip'           => request()->ip(),
            'user_agent'   => substr((string) request()->userAgent(), 0, 500),

            'severidade'   => $severidade,
        ]);
    }
}