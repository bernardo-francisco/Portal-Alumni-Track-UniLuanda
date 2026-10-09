<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\NotifiableTrait;

class Egresso extends Model
{
    use HasFactory, NotifiableTrait;

    protected $table = 'egressos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'curso_id',
        'numero_processo',
        'nome_completo',
        'genero',
        'data_nascimento',
        'email',
        'telefone',
        'foto_url',
        'ano_formatura',
        'nota_final',
        // Validação
        'status_validacao',
        'motivo_reprovacao',
        'observacoes_validacao',
        'validado_por',
        'data_validacao',
        // Campos existentes
        'status',
        'observacoes',
        'data_verificacao',
        'verificado',
    ];

    protected $casts = [
        'data_nascimento'  => 'date',
        'ano_formatura'    => 'integer',
        'nota_final'       => 'decimal:2',
        'verificado'       => 'boolean',
        'data_validacao'   => 'datetime',
        'data_verificacao' => 'datetime',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function unidade()
    {
        return $this->hasOneThrough(
            UnidadeOrganica::class,
            Curso::class,
            'id',
            'id',
            'curso_id',
            'unidade_id'
        );
    }

    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    // =============================================
    // LOCALIZAÇÕES
    // =============================================

    public function localizacoes()
    {
        return $this->hasMany(Localizacao::class, 'egresso_id');
    }

    /**
     * ✅ Localização atual — usa ordem explícita para garantir
     */
    public function localizacaoAtual()
    {
        return $this->hasOne(Localizacao::class, 'egresso_id')
            ->where('is_current', true)
            ->orderByDesc('data_desde')
            ->orderByDesc('id');
    }

    // =============================================
    // PROFISSIONAIS
    // =============================================

    public function profissionais()
    {
        return $this->hasMany(Profissional::class, 'egresso_id');
    }

    /**
     * ✅ Profissional atual — usa ordem explícita
     */
    public function profissionalAtual()
    {
        return $this->hasOne(Profissional::class, 'egresso_id')
            ->where('is_current', true)
            ->orderByDesc('data_inicio')
            ->orderByDesc('id');
    }

    // =============================================
    // CONEXÕES
    // =============================================

    public function conexoesEnviadas()
    {
        return $this->hasMany(Conexao::class, 'solicitante_id');
    }

    public function conexoesRecebidas()
    {
        return $this->hasMany(Conexao::class, 'destinatario_id');
    }

    public function todasConexoes()
    {
        return Conexao::where('solicitante_id', $this->id)
                      ->orWhere('destinatario_id', $this->id);
    }

    public function conexoesAtivas()
    {
        return $this->todasConexoes()->where('status', 'aceito');
    }

    public function solicitacoesPendentes()
    {
        return $this->conexoesRecebidas()->where('status', 'pendente');
    }

    // =============================================
    // OUTROS RELACIONAMENTOS
    // =============================================

    public function mensagensEnviadas()
    {
        return $this->hasMany(Mensagem::class, 'remetente_id');
    }

    public function mensagensRecebidas()
    {
        return $this->hasMany(Mensagem::class, 'destinatario_id');
    }

    public function publicacoes()
    {
        return $this->hasMany(Publicacao::class);
    }

    public function inscricoesEventos()
    {
        return $this->hasMany(InscricaoEvento::class);
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopePendente($query)
    {
        return $query->where('status_validacao', 'pendente');
    }

    public function scopeAprovado($query)
    {
        return $query->where('status_validacao', 'aprovado');
    }

    public function scopeReprovado($query)
    {
        return $query->where('status_validacao', 'reprovado');
    }

    public function scopeAtivo($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNaoValidados($query)
    {
        return $query->where('status_validacao', '!=', 'aprovado');
    }

    // =============================================
    // MÉTODOS AUXILIARES — VALIDAÇÃO
    // =============================================

    public function isPendente(): bool
    {
        return $this->status_validacao === 'pendente';
    }

    public function isAprovado(): bool
    {
        return $this->status_validacao === 'aprovado';
    }

    public function isReprovado(): bool
    {
        return $this->status_validacao === 'reprovado';
    }

    public function isAtivo(): bool
    {
        return $this->status === 'active';
    }

    public function podeAcessar(): bool
    {
        return $this->isAprovado() && $this->isAtivo();
    }

    public function getStatusValidacaoLabelAttribute()
    {
        $labels = [
            'pendente'  => '⏳ Pendente',
            'aprovado'  => '✅ Aprovado',
            'reprovado' => '❌ Reprovado',
        ];
        return $labels[$this->status_validacao] ?? $this->status_validacao;
    }

    public function getStatusValidacaoBadgeAttribute()
    {
        $badges = [
            'pendente'  => 'warning',
            'aprovado'  => 'success',
            'reprovado' => 'danger',
        ];
        return $badges[$this->status_validacao] ?? 'secondary';
    }

    // =============================================
    // ACCESSORS
    // =============================================

    public function getFotoUrlAttribute($value)
    {
        // 1. Foto do egresso
        if (!empty($value)) {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }

            $caminho = public_path($value);
            if (file_exists($caminho)) {
                return asset($value);
            }

            $caminhoStorage = storage_path('app/public/' . $value);
            if (file_exists($caminhoStorage)) {
                return asset('storage/' . $value);
            }
        }

        // 2. Foto do user
        if ($this->user && !empty($this->user->photo_url)) {
            $photoUser = $this->user->photo_url;

            if (filter_var($photoUser, FILTER_VALIDATE_URL)) {
                return $photoUser;
            }

            $caminho = public_path($photoUser);
            if (file_exists($caminho)) {
                return asset($photoUser);
            }

            $caminhoStorage = storage_path('app/public/' . $photoUser);
            if (file_exists($caminhoStorage)) {
                return asset('storage/' . $photoUser);
            }
        }

        // 3. Avatar padrão
        return asset('images/default-avatar.png');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'active'       => 'Activo',
            'inactive'     => 'Inactivo',
            'blocked'      => 'Bloqueado',
            'lost_contact' => 'Sem Contacto',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getGeneroLabelAttribute()
    {
        $labels = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro',
        ];
        return $labels[$this->genero] ?? $this->genero;
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', trim($this->nome_completo));
        $initials = '';

        foreach (array_slice($names, 0, 2) as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }

        return $initials;
    }

    // =============================================
    // HELPERS DE CONEXÃO
    // =============================================

    public function estaConectadoCom($egressoId)
    {
        return Conexao::where(function ($query) use ($egressoId) {
            $query->where('solicitante_id', $this->id)
                  ->where('destinatario_id', $egressoId);
        })->orWhere(function ($query) use ($egressoId) {
            $query->where('solicitante_id', $egressoId)
                  ->where('destinatario_id', $this->id);
        })->where('status', 'aceito')
          ->exists();
    }

    public function temSolicitacaoPendenteCom($egressoId)
    {
        return Conexao::where(function ($query) use ($egressoId) {
            $query->where('solicitante_id', $this->id)
                  ->where('destinatario_id', $egressoId);
        })->orWhere(function ($query) use ($egressoId) {
            $query->where('solicitante_id', $egressoId)
                  ->where('destinatario_id', $this->id);
        })->where('status', 'pendente')
          ->exists();
    }
}