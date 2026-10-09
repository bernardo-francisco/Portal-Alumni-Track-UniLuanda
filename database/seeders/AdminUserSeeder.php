<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        // SEM ADMIN PADRÃO - Criar via tela de cadastro
        // ============================================================
        $this->command->info('👤 Sistema de administradores pronto!');
        $this->command->info('📌 O primeiro administrador deve ser criado via tela de cadastro.');
        $this->command->info('   Acesse: /register');
        $this->command->info('   e selecione "Administrador" no tipo de conta.');
    }
}