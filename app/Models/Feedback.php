<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // ✅ FORÇAR O NOME CORRETO DA TABELA (plural)
    protected $table = 'feedbacks';

    protected $fillable = [
        'egresso_id',
        'curso_id',
        'titulo',
        'mensagem',
        'nota',
        'status',
    ];

    protected $casts = [
        'nota' => 'integer',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pendente' => ['label' => 'Pendente', 'class' => 'warning'],
            'aprovado' => ['label' => 'Aprovado', 'class' => 'success'],
            'rejeitado' => ['label' => 'Rejeitado', 'class' => 'danger'],
        ];
        return $labels[$this->status] ?? ['label' => $this->status, 'class' => 'secondary'];
    }
}