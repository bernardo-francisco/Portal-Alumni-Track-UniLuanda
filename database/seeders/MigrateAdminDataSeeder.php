<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Egresso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAdminDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🔄 Verificando migração de administradores...');

        // Verificar se a tabela admins existe
        if (!Schema::hasTable('admins')) {
            $this->command->error('❌ Tabela admins não encontrada. Execute as migrations primeiro.');
            return;
        }

        DB::beginTransaction();

        try {
            // ============================================================
            // 1. BUSCAR USUÁRIOS COM ROLE ADMIN QUE NÃO TÊM PERFIL NA TABELA ADMINS
            // ============================================================
            $usersAdmin = User::where('role', 'admin')
                ->whereDoesntHave('admin')
                ->get();

            if ($usersAdmin->count() > 0) {
                $this->command->info("📊 Encontrados {$usersAdmin->count()} usuários com role 'admin' sem perfil.");

                foreach ($usersAdmin as $user) {
                    // Buscar se existe um egresso associado a este usuário
                    $egresso = Egresso::where('user_id', $user->id)->first();

                    Admin::create([
                        'user_id' => $user->id,
                        'nome_completo' => $egresso->nome_completo ?? $user->name,
                        'email' => $egresso->email ?? $user->email,
                        'telefone' => $egresso->telefone ?? $user->phone,
                        'foto_url' => $egresso->foto_url ?? $user->photo_url,
                        'unidade_id' => $egresso->curso->unidade_id ?? null,
                        'cargo' => 'Administrador do Sistema',
                        'ativo' => true,
                    ]);

                    // Garantir que o tipo do usuário está correto
                    $user->update(['tipo' => 'admin']);

                    // Se existir egresso, marcar como não admin
                    if ($egresso) {
                        $egresso->update(['is_admin' => false]);
                    }

                    $this->command->info("✅ Admin criado para: {$user->name} ({$user->email})");
                }
            } else {
                $this->command->info('✅ Nenhum usuário admin sem perfil encontrado.');
            }

            // ============================================================
            // 2. VERIFICAR EGRESSOS COM IS_ADMIN = TRUE
            // ============================================================
            $egressosAdmin = Egresso::where('is_admin', true)->get();

            if ($egressosAdmin->count() > 0) {
                $this->command->info("📊 Encontrados {$egressosAdmin->count()} egressos marcados como admin.");

                foreach ($egressosAdmin as $egresso) {
                    $user = User::find($egresso->user_id);

                    if (!$user) {
                        $this->command->warn("⚠️ Usuário não encontrado para egresso ID: {$egresso->id}");
                        continue;
                    }

                    // Verificar se já existe admin
                    $adminExistente = Admin::where('user_id', $user->id)->first();

                    if ($adminExistente) {
                        $this->command->info("ℹ️ Admin já existe para: {$user->email} - Pulando...");
                        $egresso->update(['is_admin' => false]);
                        continue;
                    }

                    Admin::create([
                        'user_id' => $user->id,
                        'nome_completo' => $egresso->nome_completo ?? $user->name,
                        'email' => $egresso->email ?? $user->email,
                        'telefone' => $egresso->telefone ?? $user->phone,
                        'foto_url' => $egresso->foto_url ?? $user->photo_url,
                        'unidade_id' => $egresso->curso->unidade_id ?? null,
                        'cargo' => 'Administrador do Sistema',
                        'ativo' => true,
                    ]);

                    $user->update([
                        'role' => 'admin',
                        'tipo' => 'admin',
                    ]);

                    $egresso->update(['is_admin' => false]);

                    $this->command->info("✅ Admin criado para: {$user->name} ({$user->email})");
                }
            } else {
                $this->command->info('✅ Nenhum egresso marcado como admin encontrado.');
            }

            DB::commit();
            $this->command->info('🎉 Migração de administradores concluída!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erro durante a migração: ' . $e->getMessage());
        }
    }
}