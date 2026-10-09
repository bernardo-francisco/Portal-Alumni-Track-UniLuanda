<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamadas', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->unique();
            $table->foreignId('chamador_id')->constrained('egressos')->onDelete('cascade');
            $table->foreignId('recetor_id')->constrained('egressos')->onDelete('cascade');
            $table->enum('tipo', ['audio', 'video'])->default('video');
            $table->enum('status', ['pendente', 'aceite', 'recusada', 'terminada', 'perdida'])->default('pendente');
            $table->json('sinal_oferta')->nullable();
            $table->json('sinal_resposta')->nullable();
            $table->json('ice_candidates_chamador')->nullable();
            $table->json('ice_candidates_recetor')->nullable();
            $table->timestamp('aceite_em')->nullable();
            $table->timestamp('terminada_em')->nullable();
            $table->timestamps();

            $table->index(['recetor_id', 'status']);
            $table->index(['chamador_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamadas');
    }
};