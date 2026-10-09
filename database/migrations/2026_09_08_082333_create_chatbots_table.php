<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egresso_id')->nullable()->constrained('egressos')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->text('mensagem');
            $table->enum('tipo', ['pergunta', 'resposta'])->default('pergunta');
            $table->enum('origem', ['egresso', 'admin'])->default('egresso');
            $table->boolean('lida')->default(false);
            $table->timestamps();

            // Índices para performance
            $table->index('egresso_id');
            $table->index('admin_id');
            $table->index('origem');
            $table->index('lida');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};