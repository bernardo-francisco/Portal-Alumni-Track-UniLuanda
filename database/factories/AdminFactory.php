<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use App\Models\UnidadeOrganica;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition()
    {
        return [
            'user_id' => null, // Será definido nos testes ou pode ser criado automaticamente
            'nome_completo' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->numerify('9#########'),
            'foto_url' => null,
            'unidade_id' => null,
            'cargo' => $this->faker->randomElement(['Administrador', 'Supervisor', 'Gestor']),
            'nivel' => $this->faker->randomElement(['master', 'geral', 'unidade', 'suporte']),
            'pode_gerenciar_admins' => false,
            'pode_gerenciar_egressos' => true,
            'pode_gerenciar_oportunidades' => true,
            'pode_gerenciar_eventos' => true,
            'pode_gerenciar_configuracoes' => false,
            'pode_visualizar_relatorios' => true,
            'ultimo_acesso' => null,
            'data_verificacao' => null,
            'primeiro_acesso' => true,
            'data_expiracao_senha' => null,
            'ativo' => true,
        ];
    }

    /**
     * Admin com nível master (Super Admin)
     */
    public function master()
    {
        return $this->state([
            'nivel' => 'master',
            'pode_gerenciar_admins' => true,
            'pode_gerenciar_configuracoes' => true,
        ]);
    }

    /**
     * Admin com nível geral
     */
    public function geral()
    {
        return $this->state([
            'nivel' => 'geral',
        ]);
    }

    /**
     * Admin com nível unidade
     */
    public function unidade($unidadeId)
    {
        return $this->state([
            'nivel' => 'unidade',
            'unidade_id' => $unidadeId,
        ]);
    }

    /**
     * Admin ativo
     */
    public function ativo()
    {
        return $this->state([
            'ativo' => true,
        ]);
    }

    /**
     * Admin inativo
     */
    public function inativo()
    {
        return $this->state([
            'ativo' => false,
        ]);
    }
}