<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conexoes', function (Blueprint $table) {

            $table->string('solicitante_type')
                  ->after('solicitante_id');

            $table->string('destinatario_type')
                  ->after('destinatario_id');

        });
    }

    public function down(): void
    {
        Schema::table('conexoes', function (Blueprint $table) {

            $table->dropColumn([
                'solicitante_type',
                'destinatario_type',
            ]);

        });
    }
};