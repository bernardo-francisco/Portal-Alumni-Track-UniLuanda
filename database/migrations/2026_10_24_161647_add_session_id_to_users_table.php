<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ID da sessão ativa (sessão única por utilizador)
            $table->string('session_id', 255)->nullable()->after('last_login');

            // Timestamp do último login (útil para auditoria / UI)
            $table->timestamp('last_login_at')->nullable()->after('session_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'last_login_at']);
        });
    }
};