<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilizador_pode_fazer_login_com_credenciais_validas()
    {
        // 1. Criar unidade e curso
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        // 2. Criar usuário
        $user = User::factory()->create([
            'email' => 'login@teste.com',
            'password' => bcrypt('password123'),
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);

        // 3. Criar egresso aprovado
        Egresso::factory()->aprovado()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'email' => 'login@teste.com',
        ]);

        // 4. Tentar login
        $dados = [
            'email' => 'login@teste.com',
            'password' => 'password123',
            'remember' => false,
        ];

        $response = $this->post(route('login'), $dados);

        $response->assertRedirect(route('egresso.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_com_lembrar_me_ativo()
    {
        // 1. Criar unidade e curso
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        // 2. Criar usuário
        $user = User::factory()->create([
            'email' => 'lembrar@teste.com',
            'password' => bcrypt('password123'),
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);

        // 3. Criar egresso aprovado
        Egresso::factory()->aprovado()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'email' => 'lembrar@teste.com',
        ]);

        $dados = [
            'email' => 'lembrar@teste.com',
            'password' => 'password123',
            'remember' => true,
        ];

        $response = $this->post(route('login'), $dados);

        $response->assertRedirect(route('egresso.dashboard'));
        $this->assertAuthenticated();
    }

    // ... outros testes
}