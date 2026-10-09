<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos_alumni', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('assunto');
            $table->text('mensagem');
            $table->string('ip')->nullable();
            $table->enum('status', ['novo', 'lido', 'respondido', 'arquivado'])->default('novo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos_alumni');
    }
};