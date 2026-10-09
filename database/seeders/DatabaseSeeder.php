<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Iniciando Database Seeder...');
        $this->command->info('');

        // 1. Unidades Orgânicas
        $this->call(UnidadesOrganicasSeeder::class);
        $this->command->info('');

        // 2. Cursos
        $this->call(CursosSeeder::class);
        $this->command->info('');

        // 3. Usuários Admin
        $this->call(AdminUserSeeder::class);
        $this->command->info('');

        // 4. Migrar dados de admins existentes (se houver)
        $this->call(MigrateAdminDataSeeder::class);
        $this->command->info('');

        $this->command->info('🎉 Database Seeder concluído com sucesso!');
    }
}