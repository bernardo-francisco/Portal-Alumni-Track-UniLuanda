<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Egresso;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerificarProcessoTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // TESTE 1: VERIFICAR PROCESSO VÁLIDO
    // ============================================================

    public function test_verificar_processo_valido_sem_conta()
    {
        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $egresso = Egresso::factory()->semConta()->create([
            'curso_id' => $curso->id,
            'numero_processo' => '123456789',
            'nome_completo' => 'João Silva Teste',
            'data_nascimento' => '1990-05-15',
            'email' => null,
        ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '123456789',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true,
            'message' => '✅ Número de processo verificado! Cadastro automático liberado.',
        ]);

        $response->assertJsonPath(
            'egresso.nome',
            'João Silva Teste'
        );

        $response->assertJsonPath(
            'egresso.curso',
            $curso->nome
        );
    }

    // ============================================================
    // TESTE 2: VERIFICAR PROCESSO COM CONTA EXISTENTE
    // ============================================================

    public function test_verificar_processo_com_conta_existente()
    {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
            'tipo' => 'egresso',
        ]);

        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'numero_processo' => '987654321',
            'email' => 'usuario@teste.com',
        ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '987654321',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => false,
            'message' => 'Este número de processo já está associado a uma conta.',
        ]);
    }

    // ============================================================
    // TESTE 3: VERIFICAR PROCESSO INVÁLIDO
    // ============================================================

    public function test_verificar_processo_invalido()
    {
        $response = $this->post('/verificar-processo', [
            'numero_processo' => '99999999',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => false,
            'message' => 'Número de processo não encontrado. Seu cadastro ficará pendente para validação manual.',
            'sugerir_pendente' => true,
        ]);
    }

    // ============================================================
    // TESTE 4: VERIFICAR PROCESSO COM NÚMERO VAZIO
    // ============================================================

    public function test_verificar_processo_com_numero_vazio()
    {
        $response = $this->post('/verificar-processo', [
            'numero_processo' => '',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => false,
            'message' => 'Por favor, digite um número de processo.',
        ]);
    }

    // ============================================================
    // TESTE 5: VERIFICAR PROCESSO SEM CAMPO
    // ============================================================

    public function test_verificar_processo_sem_campo()
    {
        $response = $this->post('/verificar-processo', []);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => false,
            'message' => 'Por favor, digite um número de processo.',
        ]);
    }

    // ============================================================
    // TESTE 6: EGRESSO PODE REGISTAR APÓS VERIFICAR PROCESSO
    // NÍVEL OURO
    // ============================================================

    public function test_egresso_pode_registar_apos_verificar_processo()
    {
        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $egresso = Egresso::factory()->semConta()->create([
            'curso_id' => $curso->id,
            'numero_processo' => '111222333',
            'nome_completo' => 'Maria Teste',
            'data_nascimento' => '1995-10-20',
            'email' => null,
            'telefone' => '923456789',
        ]);

        // ========================================================
        // 1. VERIFICAR PROCESSO
        // ========================================================

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '111222333',
        ]);

        $response->assertJson([
            'success' => true,
        ]);

        // ========================================================
        // 2. REGISTRAR
        // ========================================================

        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Maria Teste',
            'numero_processo' => '111222333',
            'genero' => 'F',
            'data_nascimento' => '1995-10-20',
            'email' => 'maria.teste@email.com',
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(
            route('register.post'),
            $dadosRegistro
        );

        // ========================================================
        // 3. VERIFICAR REDIRECIONAMENTO
        // ========================================================

        $response->assertRedirect(
            route('login')
        );

        // ========================================================
        // 4. VERIFICAR MENSAGEM
        // ========================================================

        $response->assertSessionHas(
            'success',
            '✅ Cadastro aprovado! Faça login para aceder ao sistema.'
        );

        // ========================================================
        // 5. VERIFICAR USUÁRIO
        // ========================================================

        $this->assertDatabaseHas('users', [
            'email' => 'maria.teste@email.com',
            'name' => 'Maria Teste',
            'tipo' => 'egresso',
            'is_active' => true,
        ]);

        // Buscar usuário criado
        $user = User::where(
            'email',
            'maria.teste@email.com'
        )->first();

        $this->assertNotNull($user);

        // ========================================================
        // 6. VERIFICAR EGRESSO
        // ========================================================

        $this->assertDatabaseHas('egressos', [
            'id' => $egresso->id,
            'user_id' => $user->id,
            'email' => 'maria.teste@email.com',
            'status_validacao' => 'aprovado',
        ]);
    }

    // ============================================================
    // TESTE 7: NÃO PERMITE REGISTAR COM PROCESSO JÁ ASSOCIADO
    // ============================================================

    public function test_nao_permite_registar_com_processo_ja_associado()
    {
        $user = User::factory()->create([
            'email' => 'usuario@teste.com',
            'tipo' => 'egresso',
        ]);

        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'numero_processo' => '999888777',
            'email' => $user->email,
        ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '999888777',
        ]);

        $response->assertJson([
            'success' => false,
            'message' => 'Este número de processo já está associado a uma conta.',
        ]);
    }

    // ============================================================
    // TESTE 8: REGISTRO COM PROCESSO INEXISTENTE
    // NÍVEL PRATA
    // ============================================================

    public function test_registro_com_processo_inexistente_cria_usuario_pendente()
    {
        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Teste Inexistente',
            'numero_processo' => '000000000',
            'genero' => 'M',
            'data_nascimento' => '1990-01-01',
            'email' => 'inexistente@teste.com',
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // ========================================================
        // 1. FAZER O REGISTRO
        // ========================================================

        $response = $this->post(
            route('register.post'),
            $dadosRegistro
        );

        // ========================================================
        // 2. VERIFICAR USUÁRIO CRIADO
        // ========================================================

        $this->assertDatabaseHas('users', [
            'email' => 'inexistente@teste.com',
            'name' => 'Teste Inexistente',
            'tipo' => 'egresso',
            'is_active' => false,
        ]);

        // Buscar o usuário criado
        $user = User::where(
            'email',
            'inexistente@teste.com'
        )->first();

        // Garantir que o usuário existe
        $this->assertNotNull($user);

        // ========================================================
        // 3. BUSCAR EGRESSO CRIADO
        // ========================================================

        $egresso = Egresso::where(
            'user_id',
            $user->id
        )->first();

        // Garantir que o egresso existe
        $this->assertNotNull($egresso);

        // ========================================================
        // 4. VERIFICAR DADOS DO EGRESSO
        // ========================================================

        $this->assertEquals(
            'Teste Inexistente',
            $egresso->nome_completo
        );

        // ========================================================
        // 5. VERIFICAR NÚMERO DE PROCESSO PENDENTE
        // ========================================================

        // O número deve começar com "PENDENTE_"
        $this->assertStringStartsWith(
            'PENDENTE_',
            $egresso->numero_processo
        );

        // ========================================================
        // 6. VERIFICAR STATUS DE VALIDAÇÃO
        // ========================================================

        $this->assertEquals(
            'pendente',
            $egresso->status_validacao
        );

        // ========================================================
        // 7. VERIFICAR STATUS DO EGRESSO
        // ========================================================

        $this->assertEquals(
            'inactive',
            $egresso->status
        );

        // ========================================================
        // 8. VERIFICAR SE NÃO FOI VERIFICADO
        // ========================================================

        $this->assertFalse(
            (bool) $egresso->verificado
        );

        // ========================================================
        // 9. VERIFICAR REDIRECIONAMENTO
        // ========================================================

        $response->assertRedirect(
            route('login')
        );

        // ========================================================
        // 10. VERIFICAR MENSAGEM DE SUCESSO
        // ========================================================

        $response->assertSessionHas(
            'success',
            '⏳ Cadastro recebido! Aguarde a validação manual da equipe. Você receberá um email quando sua conta for aprovada.'
        );

        // ========================================================
        // 11. VERIFICAR STATUS DA SESSÃO
        // ========================================================

        $response->assertSessionHas(
            'status',
            'pending_validation'
        );
    }

    // ============================================================
    // TESTE 9: REGISTRO SEM NÚMERO DE PROCESSO
    // NÍVEL PRATA
    // ============================================================

    public function test_registro_sem_numero_processo_cria_usuario_pendente()
    {
        $unidade = UnidadeOrganica::factory()->create();

        $curso = Curso::factory()->create([
            'unidade_id' => $unidade->id,
        ]);

        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Teste Sem Processo',
            'numero_processo' => null,
            'genero' => 'M',
            'data_nascimento' => '1990-01-01',
            'email' => 'semprocesso@teste.com',
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // ========================================================
        // 1. FAZER O REGISTRO
        // ========================================================

        $response = $this->post(
            route('register.post'),
            $dadosRegistro
        );

        // ========================================================
        // 2. VERIFICAR USUÁRIO CRIADO
        // ========================================================

        $this->assertDatabaseHas('users', [
            'email' => 'semprocesso@teste.com',
            'name' => 'Teste Sem Processo',
            'tipo' => 'egresso',
            'is_active' => false,
        ]);

        // Buscar usuário
        $user = User::where(
            'email',
            'semprocesso@teste.com'
        )->first();

        $this->assertNotNull($user);

        // ========================================================
        // 3. VERIFICAR EGRESSO CRIADO COMO PENDENTE
        // ========================================================

        $this->assertDatabaseHas('egressos', [
            'user_id' => $user->id,
            'nome_completo' => 'Teste Sem Processo',
            'status_validacao' => 'pendente',
            'status' => 'inactive',
            'verificado' => false,
        ]);

        // ========================================================
        // 4. VERIFICAR REDIRECIONAMENTO
        // ========================================================

        $response->assertRedirect(
            route('login')
        );

        // ========================================================
        // 5. VERIFICAR STATUS DA SESSÃO
        // ========================================================

        $response->assertSessionHas(
            'status',
            'pending_validation'
        );
    }
}