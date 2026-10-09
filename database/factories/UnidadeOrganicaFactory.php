<?php

namespace Database\Factories;

use App\Models\UnidadeOrganica;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadeOrganicaFactory extends Factory
{
    protected $model = UnidadeOrganica::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->unique()->company(),
            'sigla' => $this->faker->unique()->lexify('UNI???'),
            'descricao' => $this->faker->sentence(),
        ];
    }
}

