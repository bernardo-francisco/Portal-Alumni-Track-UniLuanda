<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'egresso_id',
        'servico',
        'descricao',
        'status',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pendente' => ['label' => 'Pendente', 'class' => 'warning'],
            'andamento' => ['label' => 'Em Andamento', 'class' => 'info'],
            'atendido' => ['label' => 'Atendido', 'class' => 'success'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
        ];
        return $labels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
    }
}