<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_completo',
        'email',
        'telefone',
        'foto_url',
        'unidade_id',
        'cargo',
        'nivel',
        'pode_gerenciar_admins',
        'pode_gerenciar_egressos',
        'pode_gerenciar_oportunidades',
        'pode_gerenciar_eventos',
        'pode_gerenciar_configuracoes',
        'pode_visualizar_relatorios',
        'ultimo_acesso',
        'data_verificacao',
        'primeiro_acesso',
        'data_expiracao_senha',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'primeiro_acesso' => 'boolean',
        'pode_gerenciar_admins' => 'boolean',
        'pode_gerenciar_egressos' => 'boolean',
        'pode_gerenciar_oportunidades' => 'boolean',
        'pode_gerenciar_eventos' => 'boolean',
        'pode_gerenciar_configuracoes' => 'boolean',
        'pode_visualizar_relatorios' => 'boolean',
        'ultimo_acesso' => 'datetime',
        'data_verificacao' => 'datetime',
        'data_expiracao_senha' => 'datetime',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unidade()
    {
        return $this->belongsTo(UnidadeOrganica::class, 'unidade_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeMaster($query)
    {
        return $query->where('nivel', 'master');
    }

    public function scopeGeral($query)
    {
        return $query->where('nivel', 'geral');
    }

    public function scopeUnidade($query)
    {
        return $query->where('nivel', 'unidade');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    public function getFotoUrlAttribute($value)
    {
        if ($value && file_exists(public_path($value))) {
            return asset($value);
        }
        return asset('images/default-avatar.png');
    }

    public function getNivelLabelAttribute()
    {
        $labels = [
            'master' => '👑 Master',
            'geral' => '📋 Geral',
            'unidade' => '🏛️ Unidade',
            'suporte' => '🔧 Suporte',
        ];
        return $labels[$this->nivel] ?? $this->nivel;
    }

    public function getStatusLabelAttribute()
    {
        return $this->ativo ? '✅ Ativo' : '❌ Inativo';
    }

    public function isMaster()
    {
        return $this->nivel === 'master';
    }

    public function isAdminGeral()
    {
        return $this->nivel === 'geral';
    }

    public function isAdminUnidade()
    {
        return $this->nivel === 'unidade';
    }

    public function isAtivo()
    {
        return $this->ativo;
    }

    public function pode($permissao)
    {
        // Master pode tudo
        if ($this->isMaster()) {
            return true;
        }

        // Verificar permissão específica
        $campo = 'pode_' . $permissao;
        if (in_array($campo, $this->fillable)) {
            return $this->$campo;
        }

        return false;
    }

    public function registrarAcesso()
    {
        $this->update(['ultimo_acesso' => now()]);
    }

    public function verificar()
    {
        $this->update(['data_verificacao' => now()]);
    }
}