<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricoes_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->enum('status', ['confirmada', 'cancelada', 'pendente'])->default('confirmada');
            $table->timestamps();
            
            $table->unique(['evento_id', 'egresso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricoes_eventos');
    }
};