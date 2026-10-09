<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->text('conteudo');
            $table->enum('tipo', ['texto', 'imagem', 'video', 'link'])->default('texto');
            $table->string('imagem_url', 255)->nullable();
            $table->string('link_url', 255)->nullable();
            $table->integer('curtidas')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicacoes');
    }
};