<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisas_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesquisa_id')->constrained('pesquisas')->onDelete('cascade');
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->foreignId('pergunta_id')->constrained('pesquisas_perguntas')->onDelete('cascade');
            $table->text('resposta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisas_respostas');
    }
};