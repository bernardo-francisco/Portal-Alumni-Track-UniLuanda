<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alterar o ENUM para incluir todos os status
        DB::statement("
            ALTER TABLE candidaturas
            MODIFY COLUMN status ENUM(
                'pendente',
                'em_analise',
                'entrevista',
                'aprovado',
                'rejeitado'
            ) NOT NULL DEFAULT 'pendente'
        ");

        // Verificar se 'avaliado_em' tem o tipo correto
        if (!\Illuminate\Support\Facades\Schema::hasColumn('candidaturas', 'avaliado_em')) {
            \Illuminate\Support\Facades\Schema::table('candidaturas', function ($table) {
                $table->timestamp('avaliado_em')->nullable();
            });
        }
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE candidaturas
            MODIFY COLUMN status ENUM('pendente','aprovado','rejeitado')
            NOT NULL DEFAULT 'pendente'
        ");
    }
};