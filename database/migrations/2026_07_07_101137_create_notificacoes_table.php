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
            $table->foreignId('egresso_id')->nullable()->constrained('egressos')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->enum('tipo', ['curtida', 'comentario', 'conexao', 'oportunidade', 'mensagem', 'sistema', 'servico', 'feedback'])->default('sistema');
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