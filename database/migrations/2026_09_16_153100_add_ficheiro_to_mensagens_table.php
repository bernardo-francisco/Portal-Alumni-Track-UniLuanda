<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            if (!Schema::hasColumn('mensagens', 'ficheiro_url')) {
                $table->string('ficheiro_url')->nullable()->after('audio_duracao');
            }
            if (!Schema::hasColumn('mensagens', 'ficheiro_nome')) {
                $table->string('ficheiro_nome')->nullable()->after('ficheiro_url');
            }
            if (!Schema::hasColumn('mensagens', 'ficheiro_tamanho')) {
                $table->integer('ficheiro_tamanho')->nullable()->after('ficheiro_nome');
            }
            if (!Schema::hasColumn('mensagens', 'ficheiro_tipo')) {
                $table->string('ficheiro_tipo')->nullable()->after('ficheiro_tamanho');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->dropColumn([
                'ficheiro_url',
                'ficheiro_nome',
                'ficheiro_tamanho',
                'ficheiro_tipo',
            ]);
        });
    }
};