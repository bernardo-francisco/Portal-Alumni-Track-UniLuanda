<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades_organicas')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['presencial', 'online', 'hibrido'])->default('presencial');
            $table->enum('categoria', ['workshop', 'palestra', 'networking', 'job_fair', 'curso', 'outro'])->default('outro');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->string('local', 200)->nullable();
            $table->string('link_reuniao', 255)->nullable();
            $table->integer('max_participantes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('data_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};