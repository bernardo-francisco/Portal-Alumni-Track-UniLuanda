<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {

            if (!Schema::hasColumn('candidaturas', 'confirmado_pelo_egresso')) {
                $table->boolean('confirmado_pelo_egresso')
                      ->default(false)
                      ->after('local_entrevista');
            }

            if (!Schema::hasColumn('candidaturas', 'confirmado_em')) {
                $table->timestamp('confirmado_em')
                      ->nullable()
                      ->after('confirmado_pelo_egresso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn(['confirmado_pelo_egresso', 'confirmado_em']);
        });
    }
};