<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'egresso_id',
        'admin_id',
        'empresa_id',   // ✅ NOVO
        'tipo',
        'titulo',
        'mensagem',
        'link',
        'lida',
    ];

    protected $casts = [
        'lida' => 'boolean',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // ✅ NOVO
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }

    public function scopeLidas($query)
    {
        return $query->where('lida', true);
    }

    public function scopeParaEgresso($query, $egressoId)
    {
        return $query->where('egresso_id', $egressoId);
    }

    public function scopeParaAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    // ✅ NOVO
    public function scopeParaEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    // =============================================
    // MÉTODOS
    // =============================================

    public function marcarComoLida()
    {
        $this->update(['lida' => true]);
        return $this;
    }

    // =============================================
    // ATTRIBUTES (mantém o que já tens)
    // =============================================

    public function getTipoLabelAttribute()
    {
        $labels = [
            'curtida'       => 'Curtida',
            'comentario'    => 'Comentário',
            'conexao'       => 'Conexão',
            'oportunidade'  => 'Oportunidade',
            'mensagem'      => 'Mensagem',
            'sistema'       => 'Sistema',
            'servico'       => 'Serviço',
            'feedback'      => 'Feedback',
            'contacto'      => 'Apoio ao Alumni',
            'candidatura'   => 'Candidatura',      // ✅ NOVO
            'nova_candidatura' => 'Nova Candidatura', // ✅ NOVO
        ];
        return $labels[$this->tipo] ?? $this->tipo;
    }

    public function getTipoIconeAttribute()
    {
        $icones = [
            'curtida'          => 'heart',
            'comentario'       => 'comment',
            'conexao'          => 'handshake',
            'oportunidade'     => 'briefcase',
            'mensagem'         => 'envelope',
            'sistema'          => 'bell',
            'servico'          => 'concierge-bell',
            'feedback'         => 'comment-dots',
            'contacto'         => 'envelope-open-text',
            'candidatura'      => 'file-signature',   // ✅ NOVO
            'nova_candidatura' => 'user-plus',        // ✅ NOVO
        ];
        return $icones[$this->tipo] ?? 'bell';
    }

    public function getTipoCorAttribute()
    {
        $cores = [
            'curtida'          => 'danger',
            'comentario'       => 'info',
            'conexao'          => 'success',
            'oportunidade'     => 'warning',
            'mensagem'         => 'primary',
            'sistema'          => 'secondary',
            'servico'          => 'info',
            'feedback'         => 'primary',
            'contacto'         => 'success',
            'candidatura'      => 'success',   
            'nova_candidatura' => 'info',      
        ];
        return $cores[$this->tipo] ?? 'secondary';
    }
}