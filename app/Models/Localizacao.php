<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localizacao extends Model
{
    use HasFactory;

    // CORREÇÃO: Definir o nome correto da tabela
    protected $table = 'localizacoes';

    protected $fillable = [
        'egresso_id',
        'pais',
        'provincia',
        'cidade',
        'endereco',
        'latitude',
        'longitude',
        'is_current',
        'data_desde',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'data_desde' => 'date',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function getLocalizacaoCompletaAttribute()
    {
        $parts = array_filter([
            $this->cidade,
            $this->provincia,
            $this->pais,
        ]);
        return implode(', ', $parts);
    }

    public function getCoordenadasAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ', ' . $this->longitude;
        }
        return null;
    }
}