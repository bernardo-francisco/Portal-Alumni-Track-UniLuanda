<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_tokens', function (Blueprint $table) {
            $table->id();

            // Email a verificar
            $table->string('email')->index();

            // Token único (64 caracteres aleatórios)
            $table->string('token', 64)->unique();

            // Finalidade (ex: 'verificar_registo')
            $table->string('purpose', 50)->default('verificar_registo');

            // Dados do registo pendente
            $table->json('payload')->nullable();

            // Validade (24 horas)
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            // Auditoria
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            // Índice composto para lookup rápido
            $table->index(['email', 'purpose', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_tokens');
    }
};