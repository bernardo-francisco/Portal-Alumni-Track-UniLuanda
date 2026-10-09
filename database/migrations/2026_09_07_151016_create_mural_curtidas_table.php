<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mural_curtidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mural_noticia_id')
                  ->constrained('mural_noticias')
                  ->onDelete('cascade');
            $table->foreignId('egresso_id')
                  ->constrained('egressos')
                  ->onDelete('cascade');
            $table->timestamps();

            // Evitar curtidas duplicadas
            $table->unique(['mural_noticia_id', 'egresso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mural_curtidas');
    }
};