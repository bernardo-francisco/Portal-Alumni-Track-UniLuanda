<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamadas', function (Blueprint $table) {
            $table->string('peer_id_chamador', 100)->nullable()->after('room_id');
            $table->string('peer_id_recetor', 100)->nullable()->after('peer_id_chamador');
        });
    }

    public function down(): void
    {
        Schema::table('chamadas', function (Blueprint $table) {
            $table->dropColumn(['peer_id_chamador', 'peer_id_recetor']);
        });
    }
};