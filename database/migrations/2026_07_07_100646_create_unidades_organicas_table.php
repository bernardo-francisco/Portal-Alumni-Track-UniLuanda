<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADICIONADO

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_organicas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('sigla', 20)->unique();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
        
        // Inserir unidades padrão usando DB facade
        DB::table('unidades_organicas')->insert([
            ['nome' => 'Instituto Politécnico de Gestão e Tecnologias', 'sigla' => 'IPGEST', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Instituto Superior Técnico de Ciências', 'sigla' => 'INSTIC', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Faculdade de Ciências Sociais', 'sigla' => 'FSS', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Faculdade de Administração e Auditoria', 'sigla' => 'FAA', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_organicas');
    }
};