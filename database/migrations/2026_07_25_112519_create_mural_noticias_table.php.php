<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mural_noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('titulo');
            $table->text('conteudo');
            $table->enum('tipo', ['noticia', 'evento', 'edital'])->default('noticia');
            $table->string('imagem_url')->nullable();
            $table->date('data_evento')->nullable();
            $table->string('local')->nullable();
            $table->boolean('destaque')->default(false);
            $table->boolean('publicado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mural_noticias');
    }
};