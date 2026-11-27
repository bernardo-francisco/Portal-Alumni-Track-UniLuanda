<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('chamadas', 'ultimo_ping_em')) {
            Schema::table('chamadas', function (Blueprint $table) {
                $table->timestamp('ultimo_ping_em')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chamadas', 'ultimo_ping_em')) {
            Schema::table('chamadas', function (Blueprint $table) {
                $table->dropColumn('ultimo_ping_em');
            });
        }
    }
};