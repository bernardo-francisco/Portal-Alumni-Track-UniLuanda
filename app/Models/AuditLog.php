<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'admin_id', 'admin_email', 'admin_nome',
        'acao', 'alvo_tipo', 'alvo_id', 'descricao',
        'admin_unidade_id', 'admin_unidade_sigla', 'admin_unidade_nome', 
        'dados_antes', 'dados_depois',
        'ip', 'user_agent', 'severidade',
    ];

    protected $casts = [
        'dados_antes'  => 'array',
        'dados_depois' => 'array',
        'created_at'   => 'datetime',
    ];

    // ============================================================
    // RELAÇÕES
    // ============================================================

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeCritico($q)
    {
        return $q->where('severidade', 'critico');
    }

    public function scopeDoAdmin($q, $adminId)
    {
        return $q->where('admin_id', $adminId);
    }

    public function scopeEntreDatas($q, $inicio, $fim)
    {
        return $q->whereBetween('created_at', [$inicio, $fim]);
    }
}