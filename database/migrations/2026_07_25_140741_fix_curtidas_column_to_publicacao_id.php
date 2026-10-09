<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curtidas', function (Blueprint $table) {
            // Verificar se a coluna mural_noticia_id existe
            if (Schema::hasColumn('curtidas', 'mural_noticia_id')) {
                // Remover a foreign key existente
                try {
                    $table->dropForeign(['mural_noticia_id']);
                } catch (\Exception $e) {
                    // Se não existir, continua
                }
                // Renomear a coluna
                $table->renameColumn('mural_noticia_id', 'publicacao_id');
                // Adicionar a nova foreign key
                $table->foreign('publicacao_id')->references('id')->on('publicacoes')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('curtidas', function (Blueprint $table) {
            if (Schema::hasColumn('curtidas', 'publicacao_id')) {
                try {
                    $table->dropForeign(['publicacao_id']);
                } catch (\Exception $e) {
                    // Se não existir, continua
                }
                $table->renameColumn('publicacao_id', 'mural_noticia_id');
                $table->foreign('mural_noticia_id')->references('id')->on('mural_noticias')->onDelete('cascade');
            }
        });
    }
};