<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alterar o enum para incluir 'contacto'
        DB::statement("ALTER TABLE notificacoes MODIFY COLUMN tipo ENUM(
            'curtida',
            'comentario',
            'conexao',
            'oportunidade',
            'mensagem',
            'sistema',
            'servico',
            'feedback',
            'contacto'
        ) DEFAULT 'sistema'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notificacoes MODIFY COLUMN tipo ENUM(
            'curtida',
            'comentario',
            'conexao',
            'oportunidade',
            'mensagem',
            'sistema',
            'servico',
            'feedback'
        ) DEFAULT 'sistema'");
    }
};