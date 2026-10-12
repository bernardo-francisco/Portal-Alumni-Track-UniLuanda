<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            // Quando foi reivindicado pelo egresso real (null = ainda não)
            $table->timestamp('claimed_at')
                  ->nullable()
                  ->after('data_verificacao');

            // IP e user agent de quem reivindicou (auditoria)
            $table->string('claimed_ip', 45)
                  ->nullable()
                  ->after('claimed_at');

            $table->string('claimed_user_agent', 500)
                  ->nullable()
                  ->after('claimed_ip');
        });
    }

    public function down(): void
    {
        Schema::table('egressos', function (Blueprint $table) {
            $table->dropColumn([
                'claimed_at',
                'claimed_ip',
                'claimed_user_agent',
            ]);
        });
    }
};