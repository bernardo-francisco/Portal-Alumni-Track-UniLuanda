<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // CANDIDATURAS — adicionar colunas de gestão
        // ============================================================
        Schema::table('candidaturas', function (Blueprint $table) {
            if (!Schema::hasColumn('candidaturas', 'mensagem_motivacional')) {
                $table->text('mensagem_motivacional')->nullable()->after('status');
            }
            if (!Schema::hasColumn('candidaturas', 'cv_anexo')) {
                $table->string('cv_anexo')->nullable()->after('mensagem_motivacional');
            }
            if (!Schema::hasColumn('candidaturas', 'data_entrevista')) {
                $table->dateTime('data_entrevista')->nullable()->after('cv_anexo');
            }
            if (!Schema::hasColumn('candidaturas', 'local_entrevista')) {
                $table->string('local_entrevista')->nullable()->after('data_entrevista');
            }
            if (!Schema::hasColumn('candidaturas', 'motivo_rejeicao')) {
                $table->text('motivo_rejeicao')->nullable()->after('local_entrevista');
            }
            if (!Schema::hasColumn('candidaturas', 'observacoes_admin')) {
                $table->text('observacoes_admin')->nullable()->after('motivo_rejeicao');
            }
            if (!Schema::hasColumn('candidaturas', 'avaliado_em')) {
                $table->timestamp('avaliado_em')->nullable()->after('observacoes_admin');
            }
        });

        // ============================================================
        // INSCRIÇÕES EVENTOS — adicionar colunas de presença
        // ============================================================
        Schema::table('inscricoes_eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('inscricoes_eventos', 'presente')) {
                $table->boolean('presente')->default(false)->after('status');
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'checkin_em')) {
                $table->timestamp('checkin_em')->nullable()->after('presente');
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_emitido')) {
                $table->boolean('certificado_emitido')->default(false)->after('checkin_em');
            }
            if (!Schema::hasColumn('inscricoes_eventos', 'certificado_url')) {
                $table->string('certificado_url')->nullable()->after('certificado_emitido');
            }
        });

        // ============================================================
        // EVENTOS — adicionar vagas e limite
        // ============================================================
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'vagas_totais')) {
                $table->integer('vagas_totais')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('eventos', 'permite_cancelamento')) {
                $table->boolean('permite_cancelamento')->default(true)->after('vagas_totais');
            }
            if (!Schema::hasColumn('eventos', 'data_limite_inscricao')) {
                $table->dateTime('data_limite_inscricao')->nullable()->after('permite_cancelamento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidaturas', function (Blueprint $table) {
            $table->dropColumn([
                'mensagem_motivacional', 'cv_anexo', 'data_entrevista',
                'local_entrevista', 'motivo_rejeicao', 'observacoes_admin', 'avaliado_em',
            ]);
        });

        Schema::table('inscricoes_eventos', function (Blueprint $table) {
            $table->dropColumn(['presente', 'checkin_em', 'certificado_emitido', 'certificado_url']);
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['vagas_totais', 'permite_cancelamento', 'data_limite_inscricao']);
        });
    }
};