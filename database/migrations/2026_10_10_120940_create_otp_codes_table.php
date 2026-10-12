<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            // Email destino do OTP
            $table->string('email')->index();

            // Código de 6 dígitos
            $table->string('code', 6);

            // Finalidade (ex: 'reclamar_conta')
            $table->string('purpose', 50)->default('reclamar_conta');

            // Dados temporários do registo (nome, numero_processo, etc.)
            $table->json('payload')->nullable();

            // Validade
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            // Auditoria
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            // Índice composto para lookup rápido
            $table->index(['email', 'code', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};