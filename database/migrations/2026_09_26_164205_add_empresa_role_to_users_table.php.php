<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','egresso','coordenador','empresa') NOT NULL DEFAULT 'egresso'");
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `tipo` ENUM('admin','egresso','empresa') NOT NULL DEFAULT 'egresso'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','egresso','coordenador') NOT NULL DEFAULT 'egresso'");
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `tipo` ENUM('admin','egresso') NOT NULL DEFAULT 'egresso'");
    }
};