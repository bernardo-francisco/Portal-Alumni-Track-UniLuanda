<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nome', 200);
            $table->string('nif', 20)->nullable()->unique();
            $table->string('email', 180)->unique();
            $table->string('telefone', 25)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('sector', 100)->nullable();
            $table->text('descricao')->nullable();
            $table->string('localizacao', 200)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->enum('tipo', ['parceira', 'nao_parceira', 'potencial'])->default('potencial');
            $table->enum('status_validacao', ['pendente', 'aprovado', 'reprovado'])->default('pendente');
            $table->text('motivo_reprovacao')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('data_validacao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('sector');
            $table->index('tipo');
            $table->index('status_validacao');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};