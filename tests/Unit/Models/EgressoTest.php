<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Egresso;

/**
 * Testes unitários da classe Egresso (técnica de caixa branca).
 *
 * A numeração [N x.y] nos comentários corresponde aos nós do
 * Grafo de Fluxo de Controle (Figura 24): x = número do teste, y = nó.
 */
class EgressoTest extends TestCase
{
    /**
     * Teste 1: valida o accessor `initials` (iniciais do nome completo).
     */
    public function test_get_initials_attribute()
    {
        // [N1.1] Instancia o modelo sem persistir na base de dados
        $egresso = new Egresso();

        // [N1.2] Nome completo com três palavras
        $egresso->nome_completo = 'Bernardo Vasco Lima';
        $this->assertEquals('BV', $egresso->initials);

        // [N1.3] Nome de uma única palavra
        $egresso->nome_completo = 'Daniel';
        $this->assertEquals('D', $egresso->initials);

        // [N1.4] Nome com quatro palavras
        $egresso->nome_completo = 'Ana Carolina Ferreira Santos';
        $this->assertEquals('AC', $egresso->initials);

        // [N1.5] Nome com espaços duplicados e espaços no início/fim
        $egresso->nome_completo = '  Betuel  Cambuta  ';
        $this->assertEquals('B', $egresso->initials);

        // [N1.6] Nome vazio: não deve gerar iniciais
        $egresso->nome_completo = '';
        $this->assertEquals('', $egresso->initials);
    }

    /**
     * Teste 2: valida o accessor `status_label` (tradução do estado).
     */
    public function test_get_status_label_attribute()
    {
        // [N2.1] Instancia o modelo
        $egresso = new Egresso();

        // [N2.2] Estado conhecido: active -> Activo
        $egresso->status = 'active';
        $this->assertEquals('Activo', $egresso->status_label);

        // [N2.3] Estado conhecido: inactive -> Inactivo
        $egresso->status = 'inactive';
        $this->assertEquals('Inactivo', $egresso->status_label);

        // [N2.4] Estado conhecido: lost_contact -> Sem Contacto
        $egresso->status = 'lost_contact';
        $this->assertEquals('Sem Contacto', $egresso->status_label);

        // [N2.5] Estado não mapeado: devolve o próprio valor
        $egresso->status = 'desconhecido';
        $this->assertEquals('desconhecido', $egresso->status_label);

        // [N2.6] Estado nulo: devolve null
        $egresso->status = null;
        $this->assertEquals(null, $egresso->status_label);
    }

    /**
     * Teste 3: valida o accessor `genero_label` (sigla -> texto por extenso).
     */
    public function test_get_genero_label_attribute()
    {
        // [N3.1] Instancia o modelo
        $egresso = new Egresso();

        // [N3.2] Género M -> Masculino
        $egresso->genero = 'M';
        $this->assertEquals('Masculino', $egresso->genero_label);

        // [N3.3] Género F -> Feminino
        $egresso->genero = 'F';
        $this->assertEquals('Feminino', $egresso->genero_label);

        // [N3.4] Género O -> Outro
        $egresso->genero = 'O';
        $this->assertEquals('Outro', $egresso->genero_label);

        // [N3.5] Sigla não mapeada: devolve o próprio valor
        $egresso->genero = 'X';
        $this->assertEquals('X', $egresso->genero_label);

        // [N3.6] Género nulo: devolve null
        $egresso->genero = null;
        $this->assertEquals(null, $egresso->genero_label);
    }

    /**
     * Teste 4: valida o accessor `foto_url` (foto própria e imagem padrão).
     */
    public function test_get_foto_url_attribute()
    {
        // [N4.1] Define o caminho relativo e o caminho absoluto da foto de teste
        $relativePath = 'uploads/egressos/foto_teste.jpg';
        $fullPath = public_path($relativePath);

        // [N4.2] Decisão: a pasta de destino não existe?
        if (!is_dir(dirname($fullPath))) {
            // [N4.3] Sim: cria a pasta
            mkdir(dirname($fullPath), 0755, true);
        }
        // [N4.4] Cria o ficheiro fictício da foto
        file_put_contents($fullPath, 'fake-content');

        // [N4.5] Egresso com foto: a URL deve conter o caminho informado
        $egresso = new Egresso();
        $egresso->foto_url = $relativePath;
        $this->assertStringContainsString($relativePath, $egresso->foto_url);

        // [N4.6] Egresso sem foto: deve usar a imagem padrão (fallback)
        $egresso->foto_url = null;
        $fotoUrl = (string) $egresso->foto_url;
        $this->assertStringContainsString('default-avatar.png', $fotoUrl);

        // [N4.7] Limpeza: remove o ficheiro fictício criado no teste
        unlink($fullPath);
    }

    /**
     * Teste 5: verifica a existência dos relacionamentos Eloquent no Model.
     */
    public function test_relacionamentos()
    {
        // [N5.1] Instancia o modelo
        $egresso = new Egresso();

        // [N5.2 a N5.9] Cada relação deve existir como método no Model
        $this->assertTrue(method_exists($egresso, 'user'));              // N5.2
        $this->assertTrue(method_exists($egresso, 'curso'));             // N5.3
        $this->assertTrue(method_exists($egresso, 'localizacoes'));      // N5.4
        $this->assertTrue(method_exists($egresso, 'profissionais'));     // N5.5
        $this->assertTrue(method_exists($egresso, 'publicacoes'));       // N5.6
        $this->assertTrue(method_exists($egresso, 'conexoesEnviadas'));  // N5.7
        $this->assertTrue(method_exists($egresso, 'conexoesRecebidas')); // N5.8
        $this->assertTrue(method_exists($egresso, 'candidaturas'));      // N5.9
    }
}