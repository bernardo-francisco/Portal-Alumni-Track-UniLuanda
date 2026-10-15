<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_unidade_id')->nullable()->after('admin_nome');
            $table->string('admin_unidade_sigla', 20)->nullable()->after('admin_unidade_id');
            $table->string('admin_unidade_nome')->nullable()->after('admin_unidade_sigla');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn([
                'admin_unidade_id',
                'admin_unidade_sigla',
                'admin_unidade_nome',
            ]);
        });
    }
};