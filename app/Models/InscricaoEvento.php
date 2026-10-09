<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoEvento extends Model
{
    use HasFactory;

    protected $table = 'inscricoes_eventos';

    protected $fillable = [
        'egresso_id',
        'evento_id',
        'status',
        'motivo_rejeicao',        // ✅ NOVO
        'presente',
        'checkin_em',
        'certificado_emitido',
        'certificado_url',
        'certificado_codigo',
        'certificado_emitido_em',
        'codigo_comprovativo',
        'feedback_enviado',
        'lembrete_24h_enviado',
        'lembrete_1h_enviado',
    ];

    protected $casts = [
        'presente'                => 'boolean',
        'certificado_emitido'     => 'boolean',
        'feedback_enviado'        => 'boolean',
        'lembrete_24h_enviado'    => 'boolean',
        'lembrete_1h_enviado'     => 'boolean',
        'checkin_em'              => 'datetime',
        'certificado_emitido_em'  => 'datetime',
    ];

    // ============================================================
    // RELAÇÕES
    // ============================================================
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    // ============================================================
    // HELPERS
    // ============================================================
    public static function gerarCodigoComprovativo(): string
    {
        do {
            $codigo = strtoupper(substr(bin2hex(random_bytes(8)), 0, 12));
        } while (self::where('codigo_comprovativo', $codigo)->exists());

        return $codigo;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pendente'   => 'Pendente',
            'confirmada' => 'Confirmada',
            'rejeitada'  => 'Rejeitada',
            'cancelada'  => 'Cancelada',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'confirmada' => 'success',
            'pendente'   => 'warning',
            'rejeitada'  => 'danger',
            'cancelada'  => 'secondary',
            default      => 'secondary',
        };
    }

    // ============================================================
    // SCOPES
    // ============================================================
    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('status', 'confirmada');
    }

    public function scopeRejeitadas($query)
    {
        return $query->where('status', 'rejeitada');
    }

    public function scopeAtivas($query)
    {
        return $query->whereIn('status', ['pendente', 'confirmada']);
    }
}