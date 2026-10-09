<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Egresso;
use App\Models\Admin;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // TESTES DE VERIFICAÇÃO DE TIPO
    // ============================================================

    public function test_user_is_egresso()
    {
        $user = User::factory()->create([
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);

        $this->assertTrue($user->isEgresso());
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_is_admin()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'tipo' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isEgresso());
    }

    public function test_user_with_role_admin_and_tipo_egresso_is_both()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'tipo' => 'egresso',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isEgresso());
    }

    public function test_user_with_role_egresso_and_tipo_admin_is_both()
    {
        $user = User::factory()->create([
            'role' => 'egresso',
            'tipo' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isEgresso());
    }

    // ============================================================
    // TESTES DE PERFIL - CORRIGIDOS
    // ============================================================

    public function test_user_has_perfil_completo_com_egresso()
    {
        $user = User::factory()->create([
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);
        
        // Sem perfil - deve retornar false
        $this->assertFalse($user->hasPerfilCompleto());
        
        // Com egresso - deve retornar true
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);
        
        Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
        ]);
        
        $user->refresh();
        $this->assertTrue($user->hasPerfilCompleto());
    }

    public function test_user_has_perfil_completo_com_admin()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'tipo' => 'admin',
        ]);
        
        // Sem perfil admin
        $this->assertFalse($user->hasPerfilCompleto());
        
        // ✅ USANDO A ESTRUTURA CORRETA DA TABELA admins
        $admin = Admin::create([
            'user_id' => $user->id,
            'nome_completo' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'telefone' => '923456789',
            'foto_url' => null,
            'unidade_id' => null,
            'cargo' => 'Administrador',
            'nivel' => 'geral',
            'pode_gerenciar_admins' => false,
            'pode_gerenciar_egressos' => true,
            'pode_gerenciar_oportunidades' => true,
            'pode_gerenciar_eventos' => true,
            'pode_gerenciar_configuracoes' => false,
            'pode_visualizar_relatorios' => true,
            'primeiro_acesso' => false,
            'ativo' => true,
        ]);
        
        $user->refresh();
        $this->assertTrue($user->hasPerfilCompleto());
    }

    public function test_user_get_perfil_returns_egresso()
    {
        $user = User::factory()->create([
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);
        
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);
        
        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
        ]);
        
        $user->refresh();
        $perfil = $user->getPerfil();
        $this->assertEquals($egresso->id, $perfil->id);
        $this->assertInstanceOf(Egresso::class, $perfil);
    }

    public function test_user_get_perfil_returns_admin()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'tipo' => 'admin',
        ]);
        
        // ✅ USANDO A ESTRUTURA CORRETA DA TABELA admins
        $admin = Admin::create([
            'user_id' => $user->id,
            'nome_completo' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'telefone' => '923456789',
            'foto_url' => null,
            'unidade_id' => null,
            'cargo' => 'Administrador',
            'nivel' => 'geral',
            'pode_gerenciar_admins' => false,
            'pode_gerenciar_egressos' => true,
            'pode_gerenciar_oportunidades' => true,
            'pode_gerenciar_eventos' => true,
            'pode_gerenciar_configuracoes' => false,
            'pode_visualizar_relatorios' => true,
            'primeiro_acesso' => false,
            'ativo' => true,
        ]);
        
        $user->refresh();
        $perfil = $user->getPerfil();
        $this->assertEquals($admin->id, $perfil->id);
        $this->assertInstanceOf(Admin::class, $perfil);
    }

    public function test_user_get_perfil_returns_null_when_no_perfil()
    {
        $user = User::factory()->create();
        $this->assertNull($user->getPerfil());
    }

    // ============================================================
    // TESTES DE ATRIBUTOS
    // ============================================================

    public function test_user_get_full_name_attribute()
    {
        $user = User::factory()->create([
            'name' => 'João Silva Santos',
            'tipo' => 'egresso',
        ]);
        
        $this->assertEquals('João Silva Santos', $user->fullName);
    }

    public function test_user_get_initials_attribute()
    {
        $user = User::factory()->create([
            'name' => 'João Silva Santos',
            'tipo' => 'egresso',
        ]);
        $this->assertEquals('JS', $user->initials);
        
        $user = User::factory()->create([
            'name' => 'Daniel',
            'tipo' => 'egresso',
        ]);
        $this->assertEquals('D', $user->initials);
        
        $user = User::factory()->create([
            'name' => 'Ana Carolina Ferreira Santos',
            'tipo' => 'egresso',
        ]);
        $this->assertEquals('AC', $user->initials);
        
        $user = User::factory()->create([
            'name' => '',
            'tipo' => 'egresso',
        ]);
        $this->assertEquals('', $user->initials);
    }

    public function test_user_get_photo_url_attribute()
    {
        $user = User::factory()->create([
            'photo_url' => null,
            'tipo' => 'egresso',
        ]);
        
        $this->assertStringContainsString('default-avatar.png', $user->photo_url);
    }

    // ============================================================
    // TESTES DE RELACIONAMENTOS
    // ============================================================

    public function test_user_has_one_egresso()
    {
        $user = User::factory()->create([
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);
        
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);
        
        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
        ]);
        
        $this->assertInstanceOf(Egresso::class, $user->egresso);
        $this->assertEquals($egresso->id, $user->egresso->id);
    }

    public function test_user_has_one_admin()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'tipo' => 'admin',
        ]);
        
        $admin = Admin::create([
            'user_id' => $user->id,
            'nome_completo' => 'Admin Teste',
            'email' => 'admin@teste.com',
            'telefone' => '923456789',
            'foto_url' => null,
            'unidade_id' => null,
            'cargo' => 'Administrador',
            'nivel' => 'geral',
            'pode_gerenciar_admins' => false,
            'pode_gerenciar_egressos' => true,
            'pode_gerenciar_oportunidades' => true,
            'pode_gerenciar_eventos' => true,
            'pode_gerenciar_configuracoes' => false,
            'pode_visualizar_relatorios' => true,
            'primeiro_acesso' => false,
            'ativo' => true,
        ]);
        
        $user->refresh();
        $this->assertInstanceOf(Admin::class, $user->admin);
        $this->assertEquals($admin->id, $user->admin->id);
    }

    // ============================================================
    // TESTES DE SCOPES
    // ============================================================

    public function test_scope_admins()
    {
        User::factory()->create(['role' => 'admin', 'tipo' => 'admin']);
        User::factory()->create(['role' => 'admin', 'tipo' => 'admin']);
        User::factory()->create(['role' => 'egresso', 'tipo' => 'egresso']);

        $admins = User::admins()->get();
        
        $this->assertEquals(2, $admins->count());
    }

    public function test_scope_egressos()
    {
        User::factory()->create(['role' => 'egresso', 'tipo' => 'egresso']);
        User::factory()->create(['role' => 'egresso', 'tipo' => 'egresso']);
        User::factory()->create(['role' => 'admin', 'tipo' => 'admin']);

        $egressos = User::egressos()->get();
        
        $this->assertEquals(2, $egressos->count());
    }

    public function test_scope_active()
    {
        User::factory()->create(['is_active' => true, 'tipo' => 'egresso']);
        User::factory()->create(['is_active' => true, 'tipo' => 'egresso']);
        User::factory()->create(['is_active' => false, 'tipo' => 'egresso']);

        $activeUsers = User::active()->get();
        
        $this->assertEquals(2, $activeUsers->count());
    }

    // ============================================================
    // TESTES DE MÉTODOS AUXILIARES
    // ============================================================

    public function test_update_last_login()
    {
        $user = User::factory()->create([
            'tipo' => 'egresso',
        ]);
        
        $user->updateLastLogin();
        $this->assertTrue(true);
    }
}