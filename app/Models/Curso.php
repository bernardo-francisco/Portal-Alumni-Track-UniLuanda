<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'unidade_id',
        'nome',
        'codigo',
        'departamento',
        'duracao',
    ];

    /**
     * Relacionamento com UnidadeOrganica
     * Especificando a chave estrangeira 'unidade_id'
     */
    public function unidade()
    {
        return $this->belongsTo(UnidadeOrganica::class, 'unidade_id');
    }

    public function egressos()
    {
        return $this->hasMany(Egresso::class);
    }
}