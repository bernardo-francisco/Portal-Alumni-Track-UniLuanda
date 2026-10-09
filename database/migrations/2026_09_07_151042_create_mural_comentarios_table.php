<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mural_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mural_noticia_id')
                  ->constrained('mural_noticias')
                  ->onDelete('cascade');
            $table->foreignId('egresso_id')
                  ->constrained('egressos')
                  ->onDelete('cascade');
            $table->text('conteudo');
            $table->boolean('editado')->default(false);
            $table->timestamp('editado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mural_comentarios');
    }
};