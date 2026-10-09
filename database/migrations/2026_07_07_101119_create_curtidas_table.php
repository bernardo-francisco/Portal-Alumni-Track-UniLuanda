<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curtidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacao_id')->constrained('publicacoes')->onDelete('cascade');
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['publicacao_id', 'egresso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curtidas');
    }
};