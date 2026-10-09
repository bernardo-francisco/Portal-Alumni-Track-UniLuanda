<?php

// app/Models/MuralCurtida.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuralCurtida extends Model
{
    use HasFactory;

    protected $table = 'mural_curtidas';

    protected $fillable = [
        'mural_noticia_id',
        'egresso_id',
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