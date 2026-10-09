<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        // ✅ ADICIONAR PHPDoc para ajudar o IDE/analisador
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'egresso',
            'tipo' => 'egresso',
        ]);

        Egresso::factory()->aprovado()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'email' => 'test@example.com',
            'status_validacao' => 'aprovado',
            'status' => 'active',
            'verificado' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('egresso.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}