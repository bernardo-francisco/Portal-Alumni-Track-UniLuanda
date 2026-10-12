<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    /**
     * Nome da tabela associada.
     */
    protected $table = 'otp_codes';

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'email',
        'code',
        'purpose',
        'payload',
        'expires_at',
        'used_at',
        'ip',
        'user_agent',
    ];

    /**
     * Casts de tipos.
     */
    protected $casts = [
        'payload'    => 'array',
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ============================================================
    // SCOPES
    // ============================================================

    /**
     * Apenas OTPs válidos (não usados e não expirados).
     */
    public function scopeValidos($query)
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Filtrar por email e propósito.
     */
    public function scopeParaEmail($query, string $email, string $purpose = 'reclamar_conta')
    {
        return $query
            ->where('email', $email)
            ->where('purpose', $purpose);
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Verifica se este OTP está expirado.
     */
    public function expirou(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica se este OTP já foi usado.
     */
    public function foiUsado(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Marca este OTP como usado.
     */
    public function marcarComoUsado(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Gera um código numérico de 6 dígitos.
     */
    public static function gerarCodigo(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}