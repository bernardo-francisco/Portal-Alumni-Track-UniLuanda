<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->string('pais', 100);
            $table->string('provincia', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('data_desde')->nullable();
            $table->timestamps();
            
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localizacoes');
    }
};