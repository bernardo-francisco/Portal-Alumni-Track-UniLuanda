<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * O RegisterController gera identificadores no formato
     * 'PENDENTE_' . Str::random(8) . '_' . time(), que chega a
     * ~29 caracteres. A coluna precisa comportar isso com folga.
     */
    public function up(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            $table->string('numero_processo', 60)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            $table->string('numero_processo', 20)->change();
        });
    }
};