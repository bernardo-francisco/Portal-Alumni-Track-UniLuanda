<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Quem
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('admin_email')->nullable();
            $table->string('admin_nome')->nullable();

            // O quê
            $table->string('acao', 100);                     // ex: 'egresso.apagar'
            $table->string('alvo_tipo', 100)->nullable();    // ex: 'Egresso'
            $table->unsignedBigInteger('alvo_id')->nullable();
            $table->string('descricao', 500)->nullable();

            // Dados
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();

            // Contexto
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Nível
            $table->enum('severidade', ['info', 'aviso', 'critico'])->default('info');

            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index('severidade');
            $table->index('acao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};