<?php

// app/Models/MuralComentario.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuralComentario extends Model
{
    use HasFactory;

    protected $table = 'mural_comentarios';

    protected $fillable = [
        'mural_noticia_id',
        'egresso_id',
        'conteudo',
        'editado',
        'editado_em',
    ];

    protected $casts = [
        'editado' => 'boolean',
        'editado_em' => 'datetime',
    ];

    public function muralNoticia()
    {
        return $this->belongsTo(MuralNoticia::class, 'mural_noticia_id');
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }
}