<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mural_noticias', function (Blueprint $table) {
            // ✅ Adicionar coluna egresso_id
            $table->foreignId('egresso_id')
                  ->nullable()
                  ->after('admin_id')
                  ->constrained('egressos')
                  ->onDelete('set null');

            // ✅ Adicionar índice para melhor performance
            $table->index('egresso_id');
        });
    }

    public function down(): void
    {
        Schema::table('mural_noticias', function (Blueprint $table) {
            $table->dropForeign(['egresso_id']);
            $table->dropColumn('egresso_id');
        });
    }
};