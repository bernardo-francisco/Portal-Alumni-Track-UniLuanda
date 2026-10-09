<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notificacoes MODIFY COLUMN tipo ENUM(
            'curtida', 'comentario', 'conexao', 'oportunidade', 'mensagem',
            'sistema', 'servico', 'feedback',
            'chamada_video', 'chamada_voz', 'audio', 'ficheiro'
        ) DEFAULT 'sistema'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notificacoes MODIFY COLUMN tipo ENUM(
            'curtida', 'comentario', 'conexao', 'oportunidade', 'mensagem',
            'sistema', 'servico', 'feedback'
        ) DEFAULT 'sistema'");
    }
};