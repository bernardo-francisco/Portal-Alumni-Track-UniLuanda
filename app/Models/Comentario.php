<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'publicacao_id',  // ✅ Deve ser publicacao_id
        'egresso_id',
        'conteudo',
    ];

    public function publicacao()
    {
        return $this->belongsTo(Publicacao::class, 'publicacao_id');
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }
}