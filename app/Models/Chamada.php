<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chamada extends Model
{
    use HasFactory;

    protected $table = 'chamadas';

    protected $fillable = [
        'room_id',
        'chamador_id',
        'recetor_id',
        'tipo',
        'status',
        'aceite_em',
        'terminada_em',
        'peer_id_chamador',
        'peer_id_recetor',
        'sinal_oferta',
        'sinal_resposta',
        'ice_candidates_chamador',
        'ice_candidates_recetor',
    ];

    protected $casts = [
        'aceite_em'               => 'datetime',
        'terminada_em'            => 'datetime',
        'sinal_oferta'            => 'array',
        'sinal_resposta'          => 'array',
        'ice_candidates_chamador' => 'array',
        'ice_candidates_recetor'  => 'array',
    ];

    public function chamador()
    {
        return $this->belongsTo(Egresso::class, 'chamador_id');
    }

    public function recetor()
    {
        return $this->belongsTo(Egresso::class, 'recetor_id');
    }
}