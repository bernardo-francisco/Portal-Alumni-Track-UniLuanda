<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oportunidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'unidade_id',
        'created_by',
        'titulo',
        'descricao',
        'tipo',
        'empresa',
        'localizacao',
        'salario',
        'requisitos',
        'data_limite',
        'is_active',
    ];

    protected $casts = [
        'data_limite' => 'date',
        'is_active' => 'boolean',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================
    public function unidade()
    {
        return $this->belongsTo(UnidadeOrganica::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================
    public function getTipoLabelAttribute(): string
    {
        $labels = [
            'emprego' => 'Emprego',
            'estagio' => 'Estágio',
            'bolsa' => 'Bolsa',
            'curso' => 'Curso',
            'evento' => 'Evento',
        ];
        return $labels[$this->tipo] ?? $this->tipo;
    }

    public function getTipoColorAttribute(): string
    {
        $colors = [
            'emprego' => 'primary',
            'estagio' => 'info',
            'bolsa' => 'success',
            'curso' => 'warning',
            'evento' => 'secondary',
        ];
        return $colors[$this->tipo] ?? 'secondary';
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactiva';
        }
        if ($this->data_limite && $this->data_limite->isPast()) {
            return 'Expirada';
        }
        return 'Activa';
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active) {
            return 'secondary';
        }
        if ($this->data_limite && $this->data_limite->isPast()) {
            return 'danger';
        }
        return 'success';
    }

    public function getTotalCandidaturasAttribute(): int
    {
        return $this->candidaturas()->count();
    }

    public function getDataLimiteFormatadaAttribute(): string
    {
        return $this->data_limite ? $this->data_limite->format('d/m/Y') : 'Sem prazo';
    }

        public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}