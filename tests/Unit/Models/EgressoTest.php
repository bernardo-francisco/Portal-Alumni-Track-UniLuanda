<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Egresso;

class EgressoTest extends TestCase
{
    public function test_get_initials_attribute()
    {
        $egresso = new Egresso();

        // Teste com nome completo
        $egresso->nome_completo = 'Bernardo Vasco Lima';
        $this->assertEquals('BV', $egresso->initials);
        
        // Teste com nome de uma palavra
        $egresso->nome_completo = 'Daniel';
        $this->assertEquals('D', $egresso->initials);

        $egresso->nome_completo = 'Ana Carolina Ferreira Santos';
        $this->assertEquals('AC', $egresso->initials);

        $egresso->nome_completo = '  Betuel  Cambuta  ';
        $this->assertEquals('B', $egresso->initials);

        // Teste com nome vazio
        $egresso->nome_completo = '';
        $this->assertEquals('', $egresso->initials);
    }

    public function test_get_status_label_attribute()
    {
        $egresso = new Egresso();
        $egresso->status = 'active';
        $this->assertEquals('Activo', $egresso->status_label);

        $egresso->status = 'inactive';
        $this->assertEquals('Inactivo', $egresso->status_label);

        $egresso->status = 'lost_contact';
        $this->assertEquals('Sem Contacto', $egresso->status_label);

        $egresso->status = 'desconhecido';
        $this->assertEquals('desconhecido', $egresso->status_label);

        $egresso->status = null;
        $this->assertEquals(null, $egresso->status_label);
    }

    public function test_get_genero_label_attribute()
    {
        $egresso = new Egresso();
        $egresso->genero = 'M';
        $this->assertEquals('Masculino', $egresso->genero_label);

        $egresso->genero = 'F';
        $this->assertEquals('Feminino', $egresso->genero_label);

        $egresso->genero = 'O';
        $this->assertEquals('Outro', $egresso->genero_label);

        $egresso->genero = 'X';
        $this->assertEquals('X', $egresso->genero_label);

        $egresso->genero = null;
        $this->assertEquals(null, $egresso->genero_label);
    }

    public function test_get_foto_url_attribute()
    {
        $relativePath = 'uploads/egressos/foto_teste.jpg';
        $fullPath = public_path($relativePath);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        file_put_contents($fullPath, 'fake-content');

        $egresso = new Egresso();
        $egresso->foto_url = $relativePath;
        $this->assertStringContainsString($relativePath, $egresso->foto_url);

        $egresso->foto_url = null;
        $fotoUrl = (string) $egresso->foto_url;
        $this->assertStringContainsString('default-avatar.png', $fotoUrl);

        unlink($fullPath);
    }

    public function test_relacionamentos()
    {
        $egresso = new Egresso();

        $this->assertTrue(method_exists($egresso, 'user'));
        $this->assertTrue(method_exists($egresso, 'curso'));
        $this->assertTrue(method_exists($egresso, 'localizacoes'));
        $this->assertTrue(method_exists($egresso, 'profissionais'));
        $this->assertTrue(method_exists($egresso, 'publicacoes'));
        $this->assertTrue(method_exists($egresso, 'conexoesEnviadas'));
        $this->assertTrue(method_exists($egresso, 'conexoesRecebidas'));
        $this->assertTrue(method_exists($egresso, 'candidaturas'));
    }
}