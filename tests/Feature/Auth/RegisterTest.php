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
    // ============================================================
    // 1. PREPARAÇÃO: criar uma unidade orgânica existente
    // ============================================================
    $unidade = UnidadeOrganica::factory()->create();

    // ============================================================
    // 2. PREPARAÇÃO: criar um curso pertencente à unidade
    // ============================================================
    $curso = Curso::factory()->create([
        'unidade_id' => $unidade->id,
    ]);

    // ============================================================
    // 3. PREPARAÇÃO: criar previamente o egresso
    //    Este é o egresso que a instituição já cadastrou.
    //    Ele ainda NÃO possui uma conta.
    // ============================================================
    $numeroProcesso = fake()->unique()->numerify('#########');

    $egresso = Egresso::create([
        'user_id' => null,
        'curso_id' => $curso->id,
        'numero_processo' => $numeroProcesso,
        'nome_completo' => 'João Silva Teste',
        'genero' => 'M',
        'data_nascimento' => '2000-05-15',
        'email' => null,
        'telefone' => '923456789',
        'foto_url' => null,
        'ano_formatura' => 2024,
        'nota_final' => 15.00,
        'status' => 'active',
        'observacoes' => null,
        'data_verificacao' => null,
        'verificado' => false,
    ]);

    // Garantir que o egresso realmente não possui conta
    $this->assertNull($egresso->user_id);

    // ============================================================
    // 4. DADOS INFORMADOS PELO EGRESSO NO FORMULÁRIO
    // ============================================================
    $dados = [
        'tipo' => 'egresso',
        'nome_completo' => 'João Silva Teste',
        'numero_processo' => $numeroProcesso,
        'genero' => 'M',
        'data_nascimento' => '2000-05-15',
        'email' => 'joao.silva@teste.com',
        'telefone' => '923456789',
        'unidade_id' => $unidade->id,
        'curso_id' => $curso->id,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    // ============================================================
    // 5. REALIZAR O REGISTRO
    // ============================================================
    $response = $this->post(
        route('register.post'),
        $dados
    );

    // ============================================================
    // 6. DEVE REDIRECIONAR PARA LOGIN
    // ============================================================
    $response->assertRedirect(route('login'));

    // ============================================================
    // 7. O USER DEVE TER SIDO CRIADO
    // ============================================================
    $this->assertDatabaseHas('users', [
        'email' => 'joao.silva@teste.com',
        'name' => 'João Silva Teste',
        'tipo' => 'egresso',
        'role' => 'egresso',
    ]);

    // ============================================================
    // 8. O MESMO EGRESSO DEVE TER SIDO ASSOCIADO AO USER
    // ============================================================
    $user = User::where(
        'email',
        'joao.silva@teste.com'
    )->first();

    $this->assertNotNull($user);

    $this->assertDatabaseHas('egressos', [
        'id' => $egresso->id,
        'numero_processo' => $numeroProcesso,
        'user_id' => $user->id,
        'email' => 'joao.silva@teste.com',
    ]);
}

public function test_numero_processo_ja_associado_a_conta_retorna_erro()
{
    // ============================================================
    // 1. Criar um utilizador que já possui uma conta
    // ============================================================
    $user = User::factory()->create([
        'email' => 'utilizador.existente@teste.com',
    ]);

    // ============================================================
    // 2. Criar um egresso já associado a esse utilizador
    // ============================================================
    $egresso = Egresso::factory()->create([
        'numero_processo' => '987654321',
        'user_id' => $user->id,
    ]);

    // Garantir que realmente está associado
    $this->assertNotNull($egresso->user_id);

    // ============================================================
    // 3. Tentar verificar o número de processo
    // ============================================================
    $response = $this->post('/verificar-processo', [
        'numero_processo' => $egresso->numero_processo,
    ]);

    // ============================================================
    // 4. Deve retornar HTTP 200
    // ============================================================
    $response->assertStatus(200);

    // ============================================================
    // 5. Deve informar que já possui uma conta
    // ============================================================
    $response->assertJson([
        'success' => false,
        'message' => 'Este número de processo já está associado a uma conta.',
    ]);
}
    public function test_admin_pode_registar_com_codigo_valido()
{
    $unidade = UnidadeOrganica::factory()->create();
    
    $dados = [
        'tipo' => 'admin',
        'nome_completo' => 'Admin Teste',
        'email' => 'admin@teste.com',
        'telefone' => '923456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'admin_code' => 'UNILUNDA2024', // ✅ Código válido
        'unidade_id' => $unidade->id,
    ];

    $response = $this->post(route('register.post'), $dados);
    
    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('users', [
        'email' => 'admin@teste.com',
        'tipo' => 'admin',
        'role' => 'admin',
    ]);
}

    public function test_nao_permite_registar_com_email_duplicado()
    {
        User::factory()->create([
            'email' => 'existente@teste.com',
        ]);

        $unidade = UnidadeOrganica::inRandomOrder()->first();
        
        if (!$unidade) {
            $this->markTestSkipped('No unidades found in database.');
        }

        $dados = [
            'tipo' => 'egresso',
            'nome_completo' => 'Duplicado Teste',
            'email' => 'existente@teste.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'unidade_id' => $unidade->id,
            'numero_processo' => '20' . time(),
        ];

        $response = $this->post(route('register.post'), $dados);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_nao_permite_registar_com_senhas_diferentes()
    {
        $unidade = UnidadeOrganica::inRandomOrder()->first();
        
        if (!$unidade) {
            $this->markTestSkipped('No unidades found in database.');
        }

        $dados = [
            'tipo' => 'egresso',
            'nome_completo' => 'Senha Teste',
            'email' => 'senha@teste.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'unidade_id' => $unidade->id,
            'numero_processo' => '20' . time(),
        ];

        $response = $this->post(route('register.post'), $dados);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_campos_obrigatorios_sao_validados()
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors([
            'tipo',
            'nome_completo',
            'email',
            'password',
        ]);
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
    // Criar um egresso que ainda NÃO possui conta
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