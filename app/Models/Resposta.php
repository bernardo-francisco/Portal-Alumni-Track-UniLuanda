<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resposta extends Model
{
    use HasFactory;

    // Definir o nome da tabela (corrigido)
    protected $table = 'pesquisas_respostas';

    protected $fillable = [
        'pesquisa_id',
        'pergunta_id',
        'egresso_id',
        'resposta',
        'resposta_extra',
    ];

    protected $casts = [
        'resposta' => 'array',
    ];

    // =============================================
    // RELACIONAMENTOS (CORRIGIDOS)
    // =============================================

    public function pesquisa()
    {
        return $this->belongsTo(Pesquisa::class);
    }

    // CORRIGIDO: Usar PesquisaPergunta em vez de Pergunta
    public function pergunta()
    {
        return $this->belongsTo(PesquisaPergunta::class, 'pergunta_id');
    }

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }
}