<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ FASE 1 — Rastreio de registo para auditoria e anti-spam
            $table->string('registration_ip', 45)
                  ->nullable()
                  ->after('is_active');

            $table->string('registration_user_agent', 500)
                  ->nullable()
                  ->after('registration_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'registration_ip',
                'registration_user_agent',
            ]);
        });
    }
};