<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profissionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egresso_id')->constrained('egressos')->onDelete('cascade');
            $table->string('empregador', 200)->nullable();
            $table->string('cargo', 150)->nullable();
            $table->string('sector', 100)->nullable();
            $table->enum('tipo_emprego', ['full_time', 'part_time', 'freelance', 'self_employed', 'unemployed', 'student', 'unknown'])->default('unknown');
            $table->enum('faixa_salarial', ['sem_rendimento', '<50k', '50k-100k', '100k-200k', '>200k', 'nao_informado'])->default('nao_informado');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('is_current')->default(true);
            $table->string('linkedin_url', 255)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('is_current');
            $table->index('tipo_emprego');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profissionais');
    }
};