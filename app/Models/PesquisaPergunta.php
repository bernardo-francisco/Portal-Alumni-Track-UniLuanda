<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesquisaPergunta extends Model
{
    use HasFactory;

    protected $table = 'pesquisas_perguntas';

    protected $fillable = [
        'pesquisa_id',
        'pergunta',
        'tipo',
        'opcoes',
        'obrigatoria',
        'ordem',
    ];

    protected $casts = [
        'opcoes' => 'array',
        'obrigatoria' => 'boolean',
    ];

    public function pesquisa()
    {
        return $this->belongsTo(Pesquisa::class);
    }

    public function respostas()
    {
        return $this->hasMany(PesquisaResposta::class, 'pergunta_id');
    }

    public function getTipoLabelAttribute()
    {
        $labels = [
            'texto' => '📝 Texto Livre',
            'multipla_escolha' => '🔘 Múltipla Escolha',
            'sim_nao' => '✅ Sim / Não',
            'selecao_multipla' => '☑️ Seleção Múltipla',
        ];
        return $labels[$this->tipo] ?? $this->tipo;
    }
}