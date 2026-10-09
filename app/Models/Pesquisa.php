<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesquisa extends Model
{
    use HasFactory;

    protected $table = 'pesquisas';

    protected $fillable = [
        'admin_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'ativa',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativa' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // CORRIGIDO: O nome da tabela é 'pesquisas_perguntas'
    public function perguntas()
    {
        return $this->hasMany(PesquisaPergunta::class, 'pesquisa_id');
    }

    // CORRIGIDO: O nome da tabela é 'pesquisas_respostas'
    public function respostas()
    {
        return $this->hasMany(PesquisaResposta::class, 'pesquisa_id');
    }

    public function getEstaAtivaAttribute()
    {
        $hoje = now()->format('Y-m-d');
        return $this->data_inicio <= $hoje && $this->data_fim >= $hoje;
    }

    public function getStatusLabelAttribute()
    {
        if ($this->esta_ativa) {
            return ['label' => 'Ativa', 'class' => 'success'];
        } elseif ($this->data_fim < now()->format('Y-m-d')) {
            return ['label' => 'Encerrada', 'class' => 'secondary'];
        } else {
            return ['label' => 'Agendada', 'class' => 'warning'];
        }
    }
}