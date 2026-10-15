<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('notificacoes', 'empresa_id')) {
            Schema::table('notificacoes', function (Blueprint $table) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('admin_id');
                $table->index('empresa_id');
            });
        }

        if (Schema::hasTable('empresas')) {
            try {
                Schema::table('notificacoes', function (Blueprint $table) {
                    $table->foreign('empresa_id')
                        ->references('id')
                        ->on('empresas')
                        ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // FK já existe
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notificacoes', 'empresa_id')) {
            try {
                Schema::table('notificacoes', function (Blueprint $table) {
                    $table->dropForeign(['empresa_id']);
                });
            } catch (\Exception $e) {}

            Schema::table('notificacoes', function (Blueprint $table) {
                $table->dropColumn('empresa_id');
            });
        }
    }
};