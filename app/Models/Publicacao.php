<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publicacao extends Model
{
    use HasFactory;

    // 🔥 ADICIONE ESTA LINHA PARA DEFINIR O NOME CORRETO DA TABELA
    protected $table = 'publicacoes';

    protected $fillable = [
        'egresso_id',
        'conteudo',
        'tipo',
        'imagem_url',
        'link_url',
        'curtidas',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function curtidasRelacionadas()
    {
        return $this->hasMany(Curtida::class);
    }

    public function getTotalCurtidasAttribute()
    {
        return $this->curtidas;
    }

    public function getTotalComentariosAttribute()
    {
        return $this->comentarios()->count();
    }

    public function getDataFormatadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getTempoDecorridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}