<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscricoes_eventos', function (Blueprint $table) {
            // ✅ Campos de verificação
            if (!Schema::hasColumn('inscricoes_eventos', 'codigo_comprovativo')) {
                $table->string('codigo_comprovativo', 20)->nullable()->unique();
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_codigo')) {
                $table->string('certificado_codigo', 20)->nullable()->unique();
            }

            // ✅ Campos de certificado
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_url')) {
                $table->string('certificado_url')->nullable();
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_emitido_em')) {
                $table->timestamp('certificado_emitido_em')->nullable();
            }

            // ✅ Campos de controlo
            if (!Schema::hasColumn('inscricoes_eventos', 'feedback_enviado')) {
                $table->boolean('feedback_enviado')->default(false);
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'lembrete_24h_enviado')) {
                $table->boolean('lembrete_24h_enviado')->default(false);
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'lembrete_1h_enviado')) {
                $table->boolean('lembrete_1h_enviado')->default(false);
            }

            // ✅ Campos de presença
            if (!Schema::hasColumn('inscricoes_eventos', 'presente')) {
                $table->boolean('presente')->default(false);
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'checkin_em')) {
                $table->timestamp('checkin_em')->nullable();
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_emitido')) {
                $table->boolean('certificado_emitido')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscricoes_eventos', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_comprovativo',
                'certificado_codigo',
                'certificado_url',
                'certificado_emitido_em',
                'feedback_enviado',
                'lembrete_24h_enviado',
                'lembrete_1h_enviado',
                'presente',
                'checkin_em',
                'certificado_emitido',
            ]);
        });
    }
};