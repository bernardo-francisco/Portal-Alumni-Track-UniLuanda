<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'user_id',
        'nome',
        'nif',
        'email',
        'telefone',
        'website',
        'sector',
        'descricao',
        'localizacao',
        'provincia',
        'latitude',
        'longitude',
        'logo_url',
        'tipo',
        'status_validacao',
        'motivo_reprovacao',
        'validado_por',
        'data_validacao',
        'ativo',
    ];

    protected $casts = [
        'data_validacao' => 'datetime',
        'ativo' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ============================================================
    // RELAÇÕES
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validador()
    {
        return $this->belongsTo(Admin::class, 'validado_por');
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class);
    }

    public function candidaturas()
    {
        return $this->hasManyThrough(
            Candidatura::class,
            Oportunidade::class,
            'empresa_id',
            'oportunidade_id',
            'id',
            'id'
        );
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeAprovadas($query)
    {
        return $query->where('status_validacao', 'aprovado')->where('ativo', true);
    }

    public function scopePendentes($query)
    {
        return $query->where('status_validacao', 'pendente');
    }

    public function scopeParceiras($query)
    {
        return $query->where('tipo', 'parceira');
    }

    public function scopeReprovadas($query)
    {
        return $query->where('status_validacao', 'reprovado');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function isAprovada(): bool
    {
        return $this->status_validacao === 'aprovado' && $this->ativo;
    }

    public function getLogoUrlAttribute($value)
    {
        return $value ? asset($value) : asset('images/default-company.png');
    }

    /**
     * Cor do badge do estado de validação
     */
    public function getStatusCorAttribute(): string
    {
        return match($this->status_validacao) {
            'aprovado'  => 'success',
            'pendente'  => 'warning',
            'reprovado' => 'danger',
            default     => 'secondary',
        };
    }

    /**
     * Label do estado de validação
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status_validacao) {
            'aprovado'  => 'Aprovada',
            'pendente'  => 'Pendente',
            'reprovado' => 'Reprovada',
            default     => 'Desconhecido',
        };
    }
}