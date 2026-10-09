<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\UnidadeOrganica;

class CursosSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('📚 Iniciando seed de cursos...');

        // Buscar unidades existentes
        $unidades = UnidadeOrganica::pluck('id', 'sigla')->toArray();

        if (empty($unidades)) {
            $this->command->error('❌ Nenhuma unidade encontrada. Execute UnidadesOrganicasSeeder primeiro.');
            return;
        }

        $cursos = [
            // INSTIC
            [
                'unidade_sigla' => 'INSTIC',
                'nome' => 'Engenharia Informática',
                'codigo' => 'EI-001',
                'departamento' => 'Engenharia',
                'duracao' => 5,
            ],
            [
                'unidade_sigla' => 'INSTIC',
                'nome' => 'Engenharia de Telecomunicações',
                'codigo' => 'ET-001',
                'departamento' => 'Engenharia',
                'duracao' => 5,
            ],
            
            // IPGEST
            [
                'unidade_sigla' => 'IPGEST',
                'nome' => 'Gestão de Empresas',
                'codigo' => 'GE-001',
                'departamento' => 'Gestão',
                'duracao' => 4,
            ],
            
            // FSS
            [
                'unidade_sigla' => 'FSS',
                'nome' => 'Serviço Social',
                'codigo' => 'SS-001',
                'departamento' => 'Serviço Social',
                'duracao' => 4,
            ],
            
            // FAA
            [
                'unidade_sigla' => 'FAA',
                'nome' => 'Administração Pública',
                'codigo' => 'AP-001',
                'departamento' => 'Administração',
                'duracao' => 4,
            ],
        ];

        $count = 0;
        foreach ($cursos as $cursoData) {
            $unidadeId = $unidades[$cursoData['unidade_sigla']] ?? null;
            
            if (!$unidadeId) {
                $this->command->warn("⚠️ Unidade {$cursoData['unidade_sigla']} não encontrada.");
                continue;
            }

            Curso::updateOrCreate(
                ['codigo' => $cursoData['codigo']],
                [
                    'unidade_id' => $unidadeId,
                    'nome' => $cursoData['nome'],
                    'departamento' => $cursoData['departamento'],
                    'duracao' => $cursoData['duracao'],
                ]
            );
            
            $count++;
            $this->command->info("✅ Curso: {$cursoData['nome']} ({$cursoData['codigo']})");
        }

        $this->command->info("📚 {$count} cursos criados com sucesso!");
    }
}