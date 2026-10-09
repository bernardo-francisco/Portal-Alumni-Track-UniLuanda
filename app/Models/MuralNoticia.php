<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuralNoticia extends Model
{
    use HasFactory;

    protected $table = 'mural_noticias';

    protected $fillable = [
        'admin_id',
        'egresso_id',  // ✅ NOVO
        'titulo',
        'conteudo',
        'tipo',
        'imagem_url',
        'data_evento',
        'local',
        'destaque',
        'publicado',
    ];

    protected $casts = [
        'destaque' => 'boolean',
        'publicado' => 'boolean',
        'data_evento' => 'date',
    ];

    // ✅ RELACIONAMENTO COM ADMIN (User)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // ✅ RELACIONAMENTO COM EGRESSO
    public function egresso()
    {
        return $this->belongsTo(Egresso::class, 'egresso_id');
    }

    // ✅ RELACIONAMENTOS COM CURTIDAS E COMENTÁRIOS
    public function muralCurtidas()
    {
        return $this->hasMany(MuralCurtida::class, 'mural_noticia_id');
    }

    public function muralComentarios()
    {
        return $this->hasMany(MuralComentario::class, 'mural_noticia_id');
    }

    // ✅ MÉTODO PARA OBTER O AUTOR CORRETAMENTE
    public function getAutorAttribute()
    {
        if ($this->admin_id) {
            return [
                'nome' => $this->admin?->name ?? 'Administrador',
                'tipo' => 'admin',
                'foto' => $this->admin?->photo_url ?? null,
            ];
        }

        if ($this->egresso_id) {
            return [
                'nome' => $this->egresso?->nome_completo ?? 'Egresso',
                'tipo' => 'egresso',
                'foto' => $this->egresso?->foto_url ?? null,
            ];
        }

        return [
            'nome' => 'Usuário Desconhecido',
            'tipo' => 'desconhecido',
            'foto' => null,
        ];
    }

    // ✅ MÉTODOS AUXILIARES
    public function getTipoLabelAttribute()
    {
        $labels = [
            'noticia' => '📰 Notícia',
            'evento' => '🎪 Evento',
            'edital' => '📢 Edital',
        ];
        return $labels[$this->tipo] ?? $this->tipo;
    }

    public function getDataFormatadaAttribute()
    {
        return $this->created_at->format('d/m/Y \à\s H:i');
    }

    public function getTempoDecorridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}