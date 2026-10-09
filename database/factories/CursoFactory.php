<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Database\Eloquent\Factories\Factory;

class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition()
    {
        return [
            'unidade_id' => UnidadeOrganica::factory(),

            'nome' => $this->faker->randomElement([
                'Engenharia Informática',
                'Engenharia Civil',
                'Engenharia Elétrica',
                'Gestão de Empresas',
                'Economia',
                'Direito',
                'Medicina',
                'Enfermagem',
                'Arquitetura',
                'Psicologia',
            ]),

            'codigo' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),

            'departamento' => $this->faker->word(),

            'duracao' => $this->faker->numberBetween(4, 6),
        ];
    }
}