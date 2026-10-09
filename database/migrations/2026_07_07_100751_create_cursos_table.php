<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades_organicas')->onDelete('cascade');
            $table->string('nome', 150);
            $table->string('codigo', 20)->unique();
            $table->string('departamento', 100)->nullable();
            $table->tinyInteger('duracao')->unsigned()->nullable()->comment('Duração em anos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};