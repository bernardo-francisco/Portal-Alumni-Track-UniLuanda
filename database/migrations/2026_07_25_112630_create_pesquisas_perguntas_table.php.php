<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesquisas_perguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesquisa_id')->constrained('pesquisas')->onDelete('cascade');
            $table->string('pergunta');
            $table->enum('tipo', ['texto', 'multipla_escolha', 'sim_nao', 'selecao_multipla']);
            $table->json('opcoes')->nullable();
            $table->boolean('obrigatoria')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesquisas_perguntas');
    }
};