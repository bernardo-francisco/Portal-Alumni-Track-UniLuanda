<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egressos', function (Blueprint $table) {
            $table->id();
            
            // ============================================
            // RELACIONAMENTOS
            // ============================================
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('curso_id')->nullable();
            
            // ============================================
            // DADOS DO EGRESSO
            // ============================================
            $table->string('numero_processo', 20)->unique();
            $table->string('nome_completo', 200);
            $table->enum('genero', ['M', 'F', 'O'])->default('M');
            $table->date('data_nascimento')->nullable();
            $table->string('email', 180)->nullable()->unique();
            $table->string('telefone', 25)->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->year('ano_formatura')->nullable();
            $table->decimal('nota_final', 4, 2)->nullable();
            
            // ============================================
            // 🔥 NOVOS CAMPOS PARA VALIDAÇÃO (NÍVEL PRATA)
            // ============================================
            $table->enum('status_validacao', ['aprovado', 'pendente', 'reprovado'])->default('pendente');
            $table->text('motivo_reprovacao')->nullable();
            $table->text('observacoes_validacao')->nullable();
            $table->unsignedBigInteger('validado_por')->nullable();
            $table->timestamp('data_validacao')->nullable();
            
            // ============================================
            // STATUS DO EGRESSO
            // ============================================
            $table->enum('status', ['active', 'inactive', 'blocked', 'lost_contact'])->default('inactive');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_verificacao')->nullable();
            $table->boolean('verificado')->default(false);
            $table->timestamps();
            
            // ============================================
            // ÍNDICES
            // ============================================
            $table->index('numero_processo');
            $table->index('status');
            $table->index('status_validacao');
            $table->index('user_id');
            $table->index('curso_id');
            $table->index('validado_por');
        });

        // ============================================
        // ADICIONAR FOREIGN KEYS DEPOIS DE CRIAR A TABELA
        // ============================================
        Schema::table('egressos', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('curso_id')
                  ->references('id')
                  ->on('cursos')
                  ->onDelete('set null');
                  
            $table->foreign('validado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egressos');
    }
};