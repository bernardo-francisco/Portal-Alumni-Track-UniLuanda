<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            if (!Schema::hasColumn('mensagens', 'tipo')) {
                $table->string('tipo')->default('texto')->after('mensagem');
            }
            if (!Schema::hasColumn('mensagens', 'audio_url')) {
                $table->string('audio_url')->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('mensagens', 'audio_duracao')) {
                $table->integer('audio_duracao')->nullable()->after('audio_url');
            }
        });

        // Tornar "mensagem" nullable (para áudios não terem texto)
        Schema::table('mensagens', function (Blueprint $table) {
            $table->text('mensagem')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mensagens', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'audio_url', 'audio_duracao']);
            $table->text('mensagem')->nullable(false)->change();
        });
    }
};