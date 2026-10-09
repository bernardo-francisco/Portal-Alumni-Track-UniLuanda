<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidade_id',
        'created_by',
        'titulo',
        'descricao',
        'tipo',
        'categoria',
        'data_inicio',
        'data_fim',
        'local',
        'link_reuniao',
        'max_participantes',
        'is_active',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'is_active' => 'boolean',
    ];

    // =============================================
    // RELACIONAMENTOS
    // =============================================
    public function unidade()
    {
        return $this->belongsTo(UnidadeOrganica::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inscricoes()
    {
        return $this->hasMany(InscricaoEvento::class);
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================
    public function getTotalInscritosAttribute(): int
    {
        return $this->inscricoes()->count();
    }

    public function getVagasDisponiveisAttribute(): ?int
    {
        if (!$this->max_participantes) {
            return null;
        }
        $disponivel = $this->max_participantes - $this->total_inscritos;
        return max(0, $disponivel);
    }

    public function getStatusAttribute(): string
    {
        $now = now();
        if ($this->data_inicio > $now) {
            return 'Próximo';
        }
        if ($this->data_fim && $this->data_fim < $now) {
            return 'Finalizado';
        }
        return 'Em Andamento';
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'Próximo' => 'primary',
            'Em Andamento' => 'success',
            'Finalizado' => 'secondary',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    public function getDataInicioFormatadaAttribute(): string
    {
        return $this->data_inicio ? $this->data_inicio->format('d/m/Y H:i') : '-';
    }

    public function getDataFimFormatadaAttribute(): string
    {
        return $this->data_fim ? $this->data_fim->format('d/m/Y H:i') : '-';
    }

    public function getTipoLabelAttribute(): string
    {
        $labels = [
            'presencial' => 'Presencial',
            'online' => 'Online',
            'hibrido' => 'Híbrido',
        ];
        return $labels[$this->tipo] ?? $this->tipo;
    }

    public function getCategoriaLabelAttribute(): string
    {
        $labels = [
            'workshop' => 'Workshop',
            'palestra' => 'Palestra',
            'networking' => 'Networking',
            'job_fair' => 'Job Fair',
            'curso' => 'Curso',
            'outro' => 'Outro',
        ];
        return $labels[$this->categoria] ?? $this->categoria;
    }
}