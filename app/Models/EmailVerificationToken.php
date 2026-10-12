<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $table = 'email_verification_tokens';

    protected $fillable = [
        'email',
        'token',
        'purpose',
        'payload',
        'expires_at',
        'used_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'payload'    => 'array',
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeValidos($query)
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public function scopeParaEmail($query, string $email, string $purpose = 'verificar_registo')
    {
        return $query
            ->where('email', $email)
            ->where('purpose', $purpose);
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    public static function gerarToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}