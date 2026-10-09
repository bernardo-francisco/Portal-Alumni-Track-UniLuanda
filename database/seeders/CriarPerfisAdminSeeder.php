<?php

namespace Database\Seeders;

use App\Models\Egresso;
use App\Models\User;
use Illuminate\Database\Seeder;

class CriarPerfisAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::where(function ($q) {
            $q->where('role', 'admin')->orWhere('tipo', 'admin');
        })->get();

        foreach ($admins as $user) {
            if (!$user->egresso) {
                Egresso::create([
                    'user_id'          => $user->id,
                    'nome_completo'    => $user->name ?? 'Administrador',
                    'email'            => $user->email,
                    'numero_processo'  => 'ADMIN_' . $user->id,
                    'status'           => 'active',
                    'verificado'       => true,
                    'status_validacao' => 'aprovado',
                    'is_admin_profile' => true,
                    'data_verificacao' => now(),
                ]);
                $this->command->info("✅ Criado: {$user->name}");
            } else {
                $this->command->info("⏭️  Já existe: {$user->name}");
            }
        }
    }
}