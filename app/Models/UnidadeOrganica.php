<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeOrganica extends Model
{
    use HasFactory;

    protected $table = 'unidades_organicas';

    protected $fillable = [
        'nome',
        'sigla',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'unidade_id'); // ajusta 'unidade_id' ao nome real
    }
    /**
     * Relacionamento com Oportunidades
     * A chave estrangeira é 'unidade_id' (NÃO unidade_organica_id)
     */
    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class, 'unidade_id');
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    public function getNomeCompletoAttribute()
    {
        return $this->sigla . ' - ' . $this->nome;
    }
}