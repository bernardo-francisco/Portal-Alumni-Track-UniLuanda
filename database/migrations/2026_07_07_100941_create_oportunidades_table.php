<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oportunidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades_organicas')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('titulo', 200);
            $table->text('descricao');
            $table->enum('tipo', ['emprego', 'estagio', 'bolsa', 'curso', 'evento'])->default('emprego');
            $table->string('empresa', 200)->nullable();
            $table->string('localizacao', 200)->nullable();
            $table->string('salario', 100)->nullable();
            $table->text('requisitos')->nullable();
            $table->date('data_limite')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('tipo');
            $table->index('data_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oportunidades');
    }
};