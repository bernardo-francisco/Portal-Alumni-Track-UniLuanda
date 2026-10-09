<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatbot extends Model
{
    use HasFactory;

    protected $table = 'chatbots';

    protected $fillable = [
        'egresso_id',
        'admin_id',
        'mensagem',
        'tipo',
        'origem',
        'lida',
    ];

    protected $casts = [
        'lida' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function egresso()
    {
        return $this->belongsTo(Egresso::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getTempoDecorridoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getDataFormatadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getHoraAttribute()
    {
        return $this->created_at->format('H:i');
    }
}