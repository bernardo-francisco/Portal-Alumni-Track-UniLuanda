<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {

            // 1️⃣ Adicionar campos em falta (se não existirem)
            if (!Schema::hasColumn('candidaturas', 'data_entrevista')) {
                $table->timestamp('data_entrevista')->nullable()->after('status');
            }

            if (!Schema::hasColumn('candidaturas', 'local_entrevista')) {
                $table->string('local_entrevista')->nullable()->after('data_entrevista');
            }

            if (!Schema::hasColumn('candidaturas', 'motivo_rejeicao')) {
                $table->text('motivo_rejeicao')->nullable()->after('local_entrevista');
            }

            if (!Schema::hasColumn('candidaturas', 'avaliado_em')) {
                $table->timestamp('avaliado_em')->nullable()->after('motivo_rejeicao');
            }

            if (!Schema::hasColumn('candidaturas', 'cv_anexo')) {
                $table->string('cv_anexo')->nullable()->after('mensagem');
            }
        });

        // 2️⃣ Corrigir o ENUM do status (adicionar 'entrevista' e 'aprovado')
        DB::statement("
            ALTER TABLE candidaturas
            MODIFY COLUMN status ENUM('pendente', 'em_analise', 'entrevista', 'aprovado', 'aceite', 'rejeitado')
            NOT NULL DEFAULT 'pendente'
        ");

        // 3️⃣ Renomear 'mensagem' para 'mensagem_motivacional' (se aplicável)
        if (Schema::hasColumn('candidaturas', 'mensagem') && !Schema::hasColumn('candidaturas', 'mensagem_motivacional')) {
            Schema::table('candidaturas', function (Blueprint $table) {
                $table->renameColumn('mensagem', 'mensagem_motivacional');
            });
        }
    }

    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn(['data_entrevista', 'local_entrevista', 'motivo_rejeicao', 'avaliado_em', 'cv_anexo']);
        });

        DB::statement("
            ALTER TABLE candidaturas
            MODIFY COLUMN status ENUM('pendente', 'aceite', 'rejeitado', 'em_analise')
            NOT NULL DEFAULT 'pendente'
        ");
    }
};