<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curtidas', function (Blueprint $table) {
            // Remover a foreign key existente
            $table->dropForeign(['publicacao_id']);
            
            // Renomear a coluna
            $table->renameColumn('publicacao_id', 'mural_noticia_id');
            
            // Adicionar a nova foreign key
            $table->foreign('mural_noticia_id')->references('id')->on('mural_noticias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('curtidas', function (Blueprint $table) {
            $table->dropForeign(['mural_noticia_id']);
            $table->renameColumn('mural_noticia_id', 'publicacao_id');
            $table->foreign('publicacao_id')->references('id')->on('publicacoes')->onDelete('cascade');
        });
    }
};