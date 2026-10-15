<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ============================================================
     * Remover UNIQUE de `egressos.email`
     * ============================================================
     *
     * Motivo:
     *   A unicidade do email já é garantida em `users.email`.
     *   Manter UNIQUE em `egressos.email` causava colisões quando:
     *     - O admin editava o email de um egresso
     *     - O egresso real reivindicava a conta com o mesmo email
     *
     * Após esta migration:
     *   - `users.email` continua ÚNICO (1 email = 1 conta)
     *   - `egressos.email` pode ter duplicados (histórico)
     * ============================================================
     */
    public function up(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            // Remover o índice UNIQUE (mantém o índice simples se existir)
            $table->dropUnique('egressos_email_unique');
        });
    }

    /**
     * Reverter: recriar o UNIQUE
     */
    public function down(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};