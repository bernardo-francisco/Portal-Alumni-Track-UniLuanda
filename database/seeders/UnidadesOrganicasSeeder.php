<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnidadeOrganica;

class UnidadesOrganicasSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏛️ Iniciando seed de unidades orgânicas...');

        $unidades = [
            [
                'sigla' => 'INSTIC',
                'nome' => 'Instituto Superior de Tecnologias de Informação e Comunicação',
                'descricao' => 'Unidade responsável pelos cursos de tecnologia e comunicação.',
            ],
            [
                'sigla' => 'IPGEST',
                'nome' => 'Instituto Politécnico de Gestão',
                'descricao' => 'Unidade responsável pelos cursos de gestão e economia.',
            ],
            [
                'sigla' => 'FSS',
                'nome' => 'Faculdade de Serviço Social',
                'descricao' => 'Unidade responsável pelos cursos de serviço social e ciências sociais.',
            ],
            [
                'sigla' => 'FAA',
                'nome' => 'Faculdade de Administração e Artes',
                'descricao' => 'Unidade responsável pelos cursos de administração e artes.',
            ],
        ];

        $count = 0;
        foreach ($unidades as $unidade) {
            UnidadeOrganica::updateOrCreate(
                ['sigla' => $unidade['sigla']],
                [
                    'nome' => $unidade['nome'],
                    'descricao' => $unidade['descricao'],
                ]
            );
            $count++;
            $this->command->info("✅ Unidade criada: {$unidade['sigla']} - {$unidade['nome']}");
        }

        $this->command->info("🏛️ {$count} unidades criadas com sucesso!");
    }
}