<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesquisaResposta extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_respostas';

    protected $fillable = [
        'pesquisa_id',
        'egresso_id',
        'pergunta_id',
        'resposta',
    ];

    public function pesquisa()
    {
        return $this->belongsTo(Pesquisa::class);
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function pergunta()
    {
        return $this->belongsTo(PesquisaPergunta::class, 'pergunta_id');
    }
}