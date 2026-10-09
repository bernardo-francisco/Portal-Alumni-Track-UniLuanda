<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conexao extends Model
{
    use HasFactory;

    protected $table = 'conexoes';

    protected $fillable = [
        'solicitante_id',
        'destinatario_id',
        'status',
        'notificacao_lida',
        'data_notificacao',
    ];

    protected $casts = [
        'notificacao_lida' => 'boolean',
        'data_notificacao' => 'datetime',
    ];

    // =============================================
    // RELACIONAMENTOS (SIMPLES - SEM POLYMORPHIC)
    // =============================================

    /**
     * Quem enviou a solicitação (Egresso)
     */
    public function solicitante()
    {
        return $this->belongsTo(Egresso::class, 'solicitante_id');
    }

    /**
     * Quem recebeu a solicitação (Egresso)
     */
    public function destinatario()
    {
        return $this->belongsTo(Egresso::class, 'destinatario_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAceitas($query)
    {
        return $query->where('status', 'aceito');
    }

    public function scopeRecusadas($query)
    {
        return $query->where('status', 'recusado');
    }

    public function scopeAtivas($query)
    {
        return $query->where('status', 'aceito');
    }

    // =============================================
    // HELPERS
    // =============================================

    /**
     * Label amigável do status
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pendente'  => 'Pendente',
            'aceito'    => 'Aceito',
            'recusado'  => 'Recusado',
            'bloqueado' => 'Bloqueado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Verifica se a conexão está pendente
     */
    public function isPendente()
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se a conexão foi aceita
     */
    public function isAceita()
    {
        return $this->status === 'aceito';
    }

    /**
     * Aceitar a conexão
     */
    public function aceitar()
    {
        $this->update(['status' => 'aceito']);
    }

    /**
     * Recusar a conexão
     */
    public function recusar()
    {
        $this->update(['status' => 'recusado']);
    }

    /**
     * Bloquear a conexão
     */
    public function bloquear()
    {
        $this->update(['status' => 'bloqueado']);
    }
}