<?php

namespace Database\Factories;

use App\Models\Egresso;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

class EgressoFactory extends Factory
{
    protected $model = Egresso::class;

    public function definition()
    {
        return [
            'user_id' => null,
            'curso_id' => Curso::factory(),
            'numero_processo' => $this->faker->unique()->numerify('#########'),
            'nome_completo' => $this->faker->name(),
            'genero' => $this->faker->randomElement(['M', 'F', 'O']),
            'data_nascimento' => $this->faker->date('Y-m-d', '2005-01-01'),
            'email' => null,
            'telefone' => $this->faker->numerify('9#########'),
            'foto_url' => null,
            'ano_formatura' => $this->faker->numberBetween(2010, 2026),
            'nota_final' => $this->faker->randomFloat(2, 10, 20),
            // 🔥 ADICIONAR CAMPOS DE VALIDAÇÃO
            'status_validacao' => 'aprovado', // Padrão: aprovado para testes
            'motivo_reprovacao' => null,
            'observacoes_validacao' => null,
            'validado_por' => null,
            'data_validacao' => null,
            'status' => 'active',
            'observacoes' => null,
            'data_verificacao' => null,
            'verificado' => true,
        ];
    }

    /**
     * Egresso que ainda não possui uma conta.
     */
    public function semConta()
    {
        return $this->state([
            'user_id' => null,
            'email' => null,
            'status_validacao' => 'pendente',
            'verificado' => false,
        ]);
    }

    /**
     * Egresso aprovado e ativo.
     */
    public function aprovado()
    {
        return $this->state([
            'status_validacao' => 'aprovado',
            'verificado' => true,
        ]);
    }

    /**
     * Egresso pendente de validação.
     */
    public function pendente()
    {
        return $this->state([
            'status_validacao' => 'pendente',
            'verificado' => false,
        ]);
    }

    /**
     * Egresso reprovado.
     */
    public function reprovado()
    {
        return $this->state([
            'status_validacao' => 'reprovado',
            'motivo_reprovacao' => 'Documentação incompleta',
            'verificado' => false,
        ]);
    }
}