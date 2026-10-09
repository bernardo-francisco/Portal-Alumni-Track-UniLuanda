<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoAlumni extends Model
{
    use HasFactory;

    protected $table = 'contactos_alumni';

    protected $fillable = [
        'nome',
        'email',
        'assunto',
        'mensagem',
        'ip',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =============================================
    // ATTRIBUTES (ACCESSORS)
    // =============================================

    /**
     * Label do status (texto legível)
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'novo'       => 'Novo',
            'lido'       => 'Lido',
            'respondido' => 'Respondido',
            'arquivado'  => 'Arquivado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Cor do badge do status (Bootstrap)
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'novo'       => 'primary',
            'lido'       => 'info',
            'respondido' => 'success',
            'arquivado'  => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Iniciais do nome (para avatar)
     */
    public function getIniciaisAttribute()
    {
        $partes = explode(' ', trim($this->nome ?? ''));

        $iniciais = '';

        foreach (array_slice($partes, 0, 2) as $parte) {
            if (!empty($parte)) {
                $iniciais .= strtoupper(substr($parte, 0, 1));
            }
        }

        return $iniciais ?: '??';
    }

    /**
     * Data formatada (d/m/Y H:i)
     */
    public function getDataFormatadaAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y H:i')
            : '-';
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeNovos($query)
    {
        return $query->where('status', 'novo');
    }

    public function scopeLidos($query)
    {
        return $query->where('status', 'lido');
    }

    public function scopeRespondidos($query)
    {
        return $query->where('status', 'respondido');
    }

    public function scopeArquivados($query)
    {
        return $query->where('status', 'arquivado');
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    public function marcarComoLido()
    {
        if ($this->status === 'novo') {
            $this->update(['status' => 'lido']);
        }

        return $this;
    }

    public function marcarComoRespondido()
    {
        $this->update(['status' => 'respondido']);

        return $this;
    }

    public function marcarComoArquivado()
    {
        $this->update(['status' => 'arquivado']);

        return $this;
    }
}