<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model
{
    use HasFactory;

    protected $fillable = [
        'oportunidade_id',
        'egresso_id',
        'mensagem_motivacional',
        'cv_anexo',
        'status',
        'data_entrevista',
        'local_entrevista',
        'motivo_rejeicao',
        'avaliado_em',
        'confirmado_pelo_egresso',
        'confirmado_em',
    ];

    protected $casts = [
        'data_entrevista'         => 'datetime',
        'avaliado_em'             => 'datetime',
        'confirmado_em'           => 'datetime',
        'confirmado_pelo_egresso' => 'boolean',
    ];

    public function oportunidade()
    {
        return $this->belongsTo(Oportunidade::class);
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pendente'   => 'Pendente',
            'em_analise' => 'Em Análise',
            'entrevista' => 'Entrevista',
            'aprovado'   => 'Aprovado',
            'aceite'     => 'Aceite',
            'rejeitado'  => 'Rejeitado',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pendente'   => 'warning',
            'em_analise' => 'info',
            'entrevista' => 'primary',
            'aprovado'   => 'success',
            'aceite'     => 'success',
            'rejeitado'  => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}