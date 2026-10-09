<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oportunidade_id')->constrained('oportunidades')->onDelete('cascade');
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->text('mensagem')->nullable();
            $table->enum('status', ['pendente', 'aceite', 'rejeitado', 'em_analise'])->default('pendente');
            $table->timestamps();
            
            $table->unique(['oportunidade_id', 'egresso_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidaturas');
    }
};