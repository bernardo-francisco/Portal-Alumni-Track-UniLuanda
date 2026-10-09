<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conexoes', function (Blueprint $table) {
            $table->id();
            
            // ============================================
            // RELACIONAMENTOS SIMPLES (apenas Egresso)
            // ============================================
            $table->foreignId('solicitante_id')
                  ->constrained('egressos')
                  ->onDelete('cascade');
                  
            $table->foreignId('destinatario_id')
                  ->constrained('egressos')
                  ->onDelete('cascade');
            
            // ============================================
            // STATUS
            // ============================================
            $table->enum('status', [
                'pendente',
                'aceito',
                'recusado',
                'bloqueado'
            ])->default('pendente');
            
            // ============================================
            // NOTIFICAÇÃO
            // ============================================
            $table->boolean('notificacao_lida')->default(false);
            $table->timestamp('data_notificacao')->nullable();
            
            $table->timestamps();
            
            // ============================================
            // ÍNDICES
            // ============================================
            $table->index('solicitante_id');
            $table->index('destinatario_id');
            $table->index('status');
            
            // Índice único para evitar duplicidade
            $table->unique(['solicitante_id', 'destinatario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conexoes');
    }
};