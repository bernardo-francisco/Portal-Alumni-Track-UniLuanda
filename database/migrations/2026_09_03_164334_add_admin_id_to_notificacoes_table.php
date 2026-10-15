<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('notificacoes', 'admin_id')) {

            $fkExists = collect(DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'notificacoes' 
                  AND CONSTRAINT_NAME = 'notificacoes_admin_id_foreign'
            "))->isNotEmpty();

            if (!$fkExists) {
                Schema::table('notificacoes', function (Blueprint $table) {
                    $table->foreign('admin_id')
                        ->references('id')
                        ->on('admins')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notificacoes', 'admin_id')) {
            try {
                Schema::table('notificacoes', function (Blueprint $table) {
                    $table->dropForeign(['admin_id']);
                });
            } catch (\Exception $e) {}
        }
    }
};