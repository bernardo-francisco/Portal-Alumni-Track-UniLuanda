<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('atividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->string('tipo'); // conexao, curtida, comentario, servico, feedback, publicacao, evento, candidatura
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('icone')->nullable();
            $table->string('cor')->default('primary');
            $table->string('link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('atividades');
    }
};