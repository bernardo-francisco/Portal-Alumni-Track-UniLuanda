<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profissional extends Model
{
    use HasFactory;

    // CORREÇÃO: Definir o nome correto da tabela
    protected $table = 'profissionais';

    protected $fillable = [
        'egresso_id',
        'empregador',
        'cargo',
        'sector',
        'tipo_emprego',
        'faixa_salarial',
        'data_inicio',
        'data_fim',
        'is_current',
        'linkedin_url',
        'observacoes',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function getTipoEmpregoLabelAttribute()
    {
        $labels = [
            'full_time' => 'Tempo Inteiro',
            'part_time' => 'Tempo Parcial',
            'freelance' => 'Freelance',
            'self_employed' => 'Autónomo',
            'unemployed' => 'Desempregado',
            'student' => 'A Estudar',
            'unknown' => 'Desconhecido',
        ];
        return $labels[$this->tipo_emprego] ?? $this->tipo_emprego;
    }
}