<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();

            // ✅ SEM foreign key — a tabela `admins` ainda não existe nesta altura
            // A FK é adicionada em 2026_09_03_164334_add_admin_id_to_notificacoes_table
            $table->unsignedBigInteger('egresso_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->enum('tipo', [
                'curtida', 'comentario', 'conexao', 'oportunidade',
                'mensagem', 'sistema', 'servico', 'feedback'
            ])->default('sistema');

            $table->string('titulo', 100);
            $table->text('mensagem');
            $table->string('link', 255)->nullable();
            $table->boolean('lida')->default(false);
            $table->timestamps();

            $table->index('egresso_id');
            $table->index('admin_id');
            $table->index('lida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};