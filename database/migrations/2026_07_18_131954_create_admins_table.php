<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            
            // ============================================
            // RELACIONAMENTO COM USER
            // ============================================
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // ============================================
            // DADOS PESSOAIS
            // ============================================
            $table->string('nome_completo');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('foto_url')->nullable();
            
            // ============================================
            // VINCULAÇÃO À UNIDADE (se nível = unidade)
            // ============================================
            $table->foreignId('unidade_id')
                  ->nullable()
                  ->constrained('unidades_organicas')
                  ->onDelete('set null');
            
            // ============================================
            // CARGOS E NÍVEIS
            // ============================================
            $table->string('cargo')->nullable();
            
            $table->enum('nivel', [
                'master',      // Super Admin - Acesso total
                'geral',       // Admin Geral - Gestão completa
                'unidade',     // Admin por Unidade - Gestão da unidade
                'suporte',     // Admin de Suporte - Apoio técnico
            ])->default('geral');
            
            // ============================================
            // PERMISSÕES ESPECÍFICAS
            // ============================================
            $table->boolean('pode_gerenciar_admins')->default(false);
            $table->boolean('pode_gerenciar_egressos')->default(true);
            $table->boolean('pode_gerenciar_oportunidades')->default(true);
            $table->boolean('pode_gerenciar_eventos')->default(true);
            $table->boolean('pode_gerenciar_configuracoes')->default(false);
            $table->boolean('pode_visualizar_relatorios')->default(true);
            
            // ============================================
            // CONTROLE DE ACESSO
            // ============================================
            $table->timestamp('ultimo_acesso')->nullable();
            $table->timestamp('data_verificacao')->nullable();
            $table->boolean('primeiro_acesso')->default(true);
            $table->timestamp('data_expiracao_senha')->nullable();
            
            // ============================================
            // STATUS
            // ============================================
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            
            // ============================================
            // ÍNDICES
            // ============================================
            $table->index('user_id');
            $table->index('email');
            $table->index('unidade_id');
            $table->index('nivel');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};