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
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

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

        // ✅ Mensagem ATUAL do código
        $response->assertJson([
            'success' => true,
            'reivindicavel' => true,
            'message' => '✅ Encontrámos o seu registo! Confirme os dados para continuar.',
        ]);

        // ✅ Paths corretos do código atual
        $response->assertJsonPath(
            'pre_preenchido.nome_mascarado',
            'João S**** T****'
        );

        $response->assertJsonPath(
            'pre_preenchido.curso',
            $curso->nome
        );
    }

    // ============================================================
    // TESTE 2: VERIFICAR PROCESSO COM CONTA EXISTENTE
    // ============================================================

    public function test_verificar_processo_com_conta_existente()
    {
        $user = User::factory()->create([
            'email' => 'usuario.existente@gmail.com',
            'tipo' => 'egresso',
        ]);

        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'numero_processo' => '987654321',
            'email' => 'usuario.existente@gmail.com',
            'claimed_at' => now(),
        ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '987654321',
        ]);

        $response->assertStatus(200);

        // ✅ Mensagem ATUAL
        $response->assertJson([
            'success' => false,
            'message' => 'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.',
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
    // ============================================================

    public function test_egresso_pode_registar_apos_verificar_processo()
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        // Email ORIGINAL do egresso (real, não descartável)
        $emailOriginal = 'maria.teste@gmail.com';

        $egresso = Egresso::factory()->semConta()->create([
            'curso_id' => $curso->id,
            'numero_processo' => '111222333',
            'nome_completo' => 'Maria Teste',
            'data_nascimento' => '1995-10-20',
            'email' => $emailOriginal,
            'telefone' => '923456789',
            'claimed_at' => null,
        ]);

        // 1. Verificar processo
        $response = $this->post('/verificar-processo', [
            'numero_processo' => '111222333',
        ]);

        $response->assertJson([
            'success' => true,
            'reivindicavel' => true,
        ]);

        // 2. Registar (Nível Ouro — email igual ao original)
        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Maria Teste',
            'numero_processo' => '111222333',
            'genero' => 'F',
            'data_nascimento' => '1995-10-20',
            'email' => $emailOriginal,
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.post'), $dadosRegistro);

        // 3. Deve redirecionar para OTP (Nível Ouro)
        $response->assertRedirect(
            route('register.verificar-otp', ['email' => $emailOriginal])
        );

        // 4. User deve ter sido criado
        $this->assertDatabaseHas('users', [
            'email' => $emailOriginal,
            'name' => 'Maria Teste',
            'tipo' => 'egresso',
        ]);

        $user = User::where('email', $emailOriginal)->first();
        $this->assertNotNull($user);

        // 5. Egresso deve estar associado ao user e ter claimed_at
        $this->assertDatabaseHas('egressos', [
            'id' => $egresso->id,
            'user_id' => $user->id,
            'numero_processo' => '111222333',
        ]);

        $egresso->refresh();
        $this->assertNotNull($egresso->claimed_at);
    }

    // ============================================================
    // TESTE 7: NÃO PERMITE REGISTAR COM PROCESSO JÁ ASSOCIADO
    // ============================================================

    public function test_nao_permite_registar_com_processo_ja_associado()
    {
        $user = User::factory()->create([
            'email' => 'usuario.associado@gmail.com',
            'tipo' => 'egresso',
        ]);

        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $egresso = Egresso::factory()->create([
            'user_id' => $user->id,
            'curso_id' => $curso->id,
            'numero_processo' => '999888777',
            'email' => $user->email,
            'claimed_at' => now(),
        ]);

        $response = $this->post('/verificar-processo', [
            'numero_processo' => '999888777',
        ]);

        // ✅ Mensagem ATUAL
        $response->assertJson([
            'success' => false,
            'message' => 'Este número de processo já tem uma conta ativa. Se é o titular, use "Recuperar password" no login.',
        ]);
    }

    // ============================================================
    // TESTE 8: REGISTRO COM PROCESSO INEXISTENTE (NÍVEL PRATA)
    // ============================================================

    public function test_registro_com_processo_inexistente_cria_usuario_pendente()
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Teste Inexistente',
            'numero_processo' => '000000000',
            'genero' => 'M',
            'data_nascimento' => '1990-01-01',
            'email' => 'inexistente.teste@gmail.com',   // ✅ email real
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.post'), $dadosRegistro);

        // ✅ User criado (Nível Prata)
        $this->assertDatabaseHas('users', [
            'email' => 'inexistente.teste@gmail.com',
            'name' => 'Teste Inexistente',
            'tipo' => 'egresso',
            'is_active' => false,
        ]);

        $user = User::where('email', 'inexistente.teste@gmail.com')->first();
        $this->assertNotNull($user);

        // ✅ Egresso criado como pendente
        $egresso = Egresso::where('user_id', $user->id)->first();
        $this->assertNotNull($egresso);

        $this->assertEquals('Teste Inexistente', $egresso->nome_completo);
        $this->assertStringStartsWith('PENDENTE_', $egresso->numero_processo);
        $this->assertEquals('pendente', $egresso->status_validacao);
        $this->assertEquals('inactive', $egresso->status);
        $this->assertFalse((bool) $egresso->verificado);

        // ✅ Redireciona para login (Nível Prata)
        $response->assertRedirect(route('login'));

        // ✅ Status da sessão
        $response->assertSessionHas('status', 'pending_email_verification');
    }

    // ============================================================
    // TESTE 9: REGISTRO SEM NÚMERO DE PROCESSO (NÍVEL PRATA)
    // ============================================================

    public function test_registro_sem_numero_processo_cria_usuario_pendente()
    {
        $unidade = UnidadeOrganica::factory()->create();
        $curso = Curso::factory()->create(['unidade_id' => $unidade->id]);

        $dadosRegistro = [
            'tipo' => 'egresso',
            'nome_completo' => 'Teste Sem Processo',
            'numero_processo' => null,
            'genero' => 'M',
            'data_nascimento' => '1990-01-01',
            'email' => 'semprocesso.teste@gmail.com',   // ✅ email real
            'telefone' => '923456789',
            'unidade_id' => $unidade->id,
            'curso_id' => $curso->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.post'), $dadosRegistro);

        // ✅ User criado
        $this->assertDatabaseHas('users', [
            'email' => 'semprocesso.teste@gmail.com',
            'name' => 'Teste Sem Processo',
            'tipo' => 'egresso',
            'is_active' => false,
        ]);

        $user = User::where('email', 'semprocesso.teste@gmail.com')->first();
        $this->assertNotNull($user);

        // ✅ Egresso pendente
        $this->assertDatabaseHas('egressos', [
            'user_id' => $user->id,
            'nome_completo' => 'Teste Sem Processo',
            'status_validacao' => 'pendente',
            'status' => 'inactive',
            'verificado' => false,
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'pending_email_verification');
    }
}