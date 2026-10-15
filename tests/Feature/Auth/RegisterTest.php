<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Egresso;
use App\Models\UnidadeOrganica;
use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_egresso_pode_registar_com_dados_validos()
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $numeroProcesso = fake()->unique()->numerify('#########');

        $egresso = Egresso::create([
            'user_id' => null,
            'curso_id' => $curso->id,
            'numero_processo' => $numeroProcesso,
            'nome_completo' => 'João Silva Teste',
            'genero' => 'M',
            'data_nascimento' => '2000-05-15',
            'email' => 'joao.silva.original@gmail.com',   // ✅ email real
            'telefone' => '923456789',
            'foto_url' => null,
            'ano_formatura' => 2024,
            'nota_final' => 15.00,
            'status' => 'active',
            'observacoes' => null,
            'data_verificacao' => null,
            'verificado' => false,
            'claimed_at' => null,
        ]);

        $this->assertNull($egresso->user_id);

        $dados = [
            'tipo' => 'egresso',
            'nome_completo' => 'João Silva Teste',
            'numero_processo' => $numeroProcesso,
            'genero' => 'M',
            'data_nascimento' => '2000-05-15',
            'email' => 'joao.silva.original@gmail.com',   // ✅ igual ao original
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.post'), $dados);

        // ✅ Reivindicação redireciona para OTP, não para login
        $response->assertRedirect(
            route('register.verificar-otp', ['email' => 'joao.silva.original@gmail.com'])
        );

        $this->assertDatabaseHas('users', [
            'email' => 'joao.silva.original@gmail.com',
            'name' => 'João Silva Teste',
            'tipo' => 'egresso',
            'role' => 'egresso',
        ]);

        $user = User::where('email', 'joao.silva.original@gmail.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('egressos', [
            'id' => $egresso->id,
            'numero_processo' => $numeroProcesso,
            'user_id' => $user->id,
            'email' => 'joao.silva.original@gmail.com',
        ]);
    }

    public function test_numero_processo_ja_associado_a_conta_retorna_erro()
    {
        $user = User::factory()->create([
            'email' => 'utilizador.existente@gmail.com',
        ]);

        $egresso = Egresso::factory()->create([
            'numero_processo' => '987654321',
            'user_id' => $user->id,
            'claimed_at' => now(),
        ]);

        $this->assertNotNull($egresso->user_id);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => $egresso->numero_processo,
        ]);

        $response->assertStatus(200);

        // ✅ Mensagem ATUAL do teu código
        $response->assertJson([
            'success' => false,
            'message' => 'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.',
        ]);
    }

    public function test_admin_pode_registar_com_codigo_valido()
    {
        $unidade = UnidadeOrganica::factory()->create();

        $dados = [
            'tipo' => 'admin',
            'nome_completo' => 'Admin Teste',
            'email' => 'admin.teste@gmail.com',   // ✅ email real
            'telefone' => '923456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admin_code' => 'UNILUNDA2024',
            'unidade_id' => $unidade->id,
        ];

        $response = $this->post(route('register.post'), $dados);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin.teste@gmail.com',
            'tipo' => 'admin',
            'role' => 'admin',
        ]);
    }

    public function test_nao_permite_registar_com_email_duplicado()
    {
        User::factory()->create([
            'email' => 'existente@gmail.com',
        ]);

        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $dados = [
            'tipo' => 'egresso',
            'nome_completo' => 'Duplicado Teste',
            'email' => 'existente@gmail.com',       // ✅ duplicado
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'numero_processo' => '20' . time(),
        ];

        $response = $this->post(route('register.post'), $dados);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_nao_permite_registar_com_senhas_diferentes()
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $dados = [
            'tipo' => 'egresso',
            'nome_completo' => 'Senha Teste',
            'email' => 'senha.diferente@gmail.com',   // ✅ email real
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'numero_processo' => '20' . time(),
        ];

        $response = $this->post(route('register.post'), $dados);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_campos_obrigatorios_sao_validados()
    {
        $response = $this->post(route('register'), []);

        // ✅ Só os campos validados SEMPRE (antes do tipo)
        $response->assertSessionHasErrors([
            'tipo',
            'email',
            'password',
        ]);

        // ⚠️ 'nome_completo' só é validado se tipo=egresso/admin
        // Por isso não está aqui.
    }

    public function test_numero_processo_invalido_retorna_erro()
    {
        $response = $this->post('/verificar-processo', [
            'numero_processo' => '99999999'
        ]);

        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_numero_processo_valido_retorna_dados()
    {
        $egresso = Egresso::factory()
            ->semConta()
            ->create([
                'numero_processo' => '123456789',
                'nome_completo' => 'Referência Teste',
            ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => $egresso->numero_processo,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}