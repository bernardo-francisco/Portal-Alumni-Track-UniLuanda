<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. Adicionar coluna motivo_rejeicao (se não existir)
        // ============================================================
        if (!Schema::hasColumn('inscricoes_eventos', 'motivo_rejeicao')) {
            Schema::table('inscricoes_eventos', function (Blueprint $table) {
                $table->string('motivo_rejeicao', 500)->nullable()->after('status');
            });
        }

        // ============================================================
        // 2. Adicionar 'rejeitada' ao enum status
        // ============================================================
        DB::statement("
            ALTER TABLE inscricoes_eventos
            MODIFY COLUMN status ENUM('pendente','confirmada','rejeitada','cancelada')
            NOT NULL DEFAULT 'pendente'
        ");

        // ============================================================
        // 3. Garantir colunas que o model já usa mas que podem faltar
        // ============================================================
        $colunasEmFalta = [
            'codigo_comprovativo'  => fn(Blueprint $t) => $t->string('codigo_comprovativo', 50)->nullable()->unique(),
            'presente'             => fn(Blueprint $t) => $t->boolean('presente')->default(false),
            'checkin_em'           => fn(Blueprint $t) => $t->timestamp('checkin_em')->nullable(),
            'certificado_emitido'  => fn(Blueprint $t) => $t->boolean('certificado_emitido')->default(false),
            'certificado_url'      => fn(Blueprint $t) => $t->string('certificado_url')->nullable(),
            'certificado_codigo'   => fn(Blueprint $t) => $t->string('certificado_codigo', 50)->nullable(),
            'certificado_emitido_em' => fn(Blueprint $t) => $t->timestamp('certificado_emitido_em')->nullable(),
            'feedback_enviado'     => fn(Blueprint $t) => $t->boolean('feedback_enviado')->default(false),
            'lembrete_24h_enviado' => fn(Blueprint $t) => $t->boolean('lembrete_24h_enviado')->default(false),
            'lembrete_1h_enviado'  => fn(Blueprint $t) => $t->boolean('lembrete_1h_enviado')->default(false),
        ];

        foreach ($colunasEmFalta as $nome => $definicao) {
            if (!Schema::hasColumn('inscricoes_eventos', $nome)) {
                Schema::table('inscricoes_eventos', function (Blueprint $table) use ($definicao) {
                    $definicao($table);
                });
            }
        }
    }

    public function down(): void
    {
        // Reverter apenas as colunas que adicionámos
        $colunas = [
            'motivo_rejeicao',
            'codigo_comprovativo',
            'presente',
            'checkin_em',
            'certificado_emitido',
            'certificado_url',
            'certificado_codigo',
            'certificado_emitido_em',
            'feedback_enviado',
            'lembrete_24h_enviado',
            'lembrete_1h_enviado',
        ];

        foreach ($colunas as $coluna) {
            if (Schema::hasColumn('inscricoes_eventos', $coluna)) {
                Schema::table('inscricoes_eventos', function (Blueprint $table) use ($coluna) {
                    $table->dropColumn($coluna);
                });
            }
        }

        // Voltar o default antigo
        DB::statement("
            ALTER TABLE inscricoes_eventos
            MODIFY COLUMN status ENUM('confirmada','cancelada','pendente')
            NOT NULL DEFAULT 'confirmada'
        ");
    }
};