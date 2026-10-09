<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    use HasFactory;

    protected $table = 'mensagens';

    protected $fillable = [
        'remetente_id',
        'destinatario_id',
        'mensagem',
        'lida',
        'editado',
        'editado_em',
        'tipo',
        'audio_url',
        'audio_duracao',
        'ficheiro_url',
        'ficheiro_nome',
        'ficheiro_tamanho',
        'ficheiro_tipo',
    ];

    protected $casts = [
        'lida'             => 'boolean',
        'editado'          => 'boolean',
        'editado_em'       => 'datetime',
        'audio_duracao'    => 'integer',
        'ficheiro_tamanho' => 'integer',
    ];

    public function remetente()
    {
        return $this->belongsTo(Egresso::class, 'remetente_id');
    }

    public function destinatario()
    {
        return $this->belongsTo(Egresso::class, 'destinatario_id');
    }

    public function isAudio(): bool
    {
        return $this->tipo === 'audio';
    }

    public function isTexto(): bool
    {
        return $this->tipo === 'texto' || $this->tipo === null;
    }

    public function isFicheiro(): bool
    {
        return $this->tipo === 'ficheiro';
    }

    /**
     * Devolve o ícone FontAwesome correspondente à extensão
     */
    public function iconeFicheiro(): string
    {
        $ext = strtolower(pathinfo($this->ficheiro_nome ?? '', PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'                                  => 'fa-file-pdf',
            'doc', 'docx'                          => 'fa-file-word',
            'xls', 'xlsx', 'csv'                   => 'fa-file-excel',
            'ppt', 'pptx'                          => 'fa-file-powerpoint',
            'zip', 'rar', '7z'                     => 'fa-file-zipper',
            'jpg', 'jpeg', 'png', 'gif', 'webp'    => 'fa-file-image',
            'mp3', 'wav', 'ogg'                    => 'fa-file-audio',
            'mp4', 'avi', 'mov', 'mkv'             => 'fa-file-video',
            'txt'                                  => 'fa-file-lines',
            default                                => 'fa-file',
        };
    }

    /**
     * Tamanho formatado (KB, MB)
     */
    public function tamanhoFormatado(): string
    {
        $bytes = $this->ficheiro_tamanho ?? 0;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
        return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    }
}