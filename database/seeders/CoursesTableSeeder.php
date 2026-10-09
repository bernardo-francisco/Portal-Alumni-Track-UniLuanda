<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\UnidadeOrganica;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('📚 Iniciando seed de cursos...');

        // Buscar ou criar unidades
        $unidades = [
            'INSTIC' => UnidadeOrganica::firstOrCreate(
                ['sigla' => 'INSTIC'],
                ['nome' => 'Instituto Superior de Tecnologias de Informação e Comunicação']
            ),
            'IPGEST' => UnidadeOrganica::firstOrCreate(
                ['sigla' => 'IPGEST'],
                ['nome' => 'Instituto Politécnico de Gestão']
            ),
            'FSS' => UnidadeOrganica::firstOrCreate(
                ['sigla' => 'FSS'],
                ['nome' => 'Faculdade de Serviço Social']
            ),
            'FAA' => UnidadeOrganica::firstOrCreate(
                ['sigla' => 'FAA'],
                ['nome' => 'Faculdade de Administração e Artes']
            ),
        ];

        // Cursos por unidade
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
            [
                'unidade_sigla' => 'INSTIC',
                'nome' => 'Ciência da Computação',
                'codigo' => 'CC-001',
                'departamento' => 'Ciências',
                'duracao' => 4,
            ],
            
            // IPGEST
            [
                'unidade_sigla' => 'IPGEST',
                'nome' => 'Gestão de Empresas',
                'codigo' => 'GE-001',
                'departamento' => 'Gestão',
                'duracao' => 4,
            ],
            [
                'unidade_sigla' => 'IPGEST',
                'nome' => 'Economia',
                'codigo' => 'EC-001',
                'departamento' => 'Economia',
                'duracao' => 4,
            ],
            [
                'unidade_sigla' => 'IPGEST',
                'nome' => 'Contabilidade e Finanças',
                'codigo' => 'CF-001',
                'departamento' => 'Finanças',
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
            [
                'unidade_sigla' => 'FSS',
                'nome' => 'Psicologia',
                'codigo' => 'PS-001',
                'departamento' => 'Psicologia',
                'duracao' => 4,
            ],
            [
                'unidade_sigla' => 'FSS',
                'nome' => 'Sociologia',
                'codigo' => 'SO-001',
                'departamento' => 'Sociologia',
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
            [
                'unidade_sigla' => 'FAA',
                'nome' => 'Artes e Design',
                'codigo' => 'AD-001',
                'departamento' => 'Artes',
                'duracao' => 4,
            ],
        ];

        $count = 0;
        foreach ($cursos as $cursoData) {
            $unidade = $unidades[$cursoData['unidade_sigla']] ?? null;
            
            if (!$unidade) {
                $this->command->warn("⚠️ Unidade {$cursoData['unidade_sigla']} não encontrada.");
                continue;
            }

            Curso::updateOrCreate(
                [
                    'codigo' => $cursoData['codigo'],
                ],
                [
                    'unidade_id' => $unidade->id,
                    'nome' => $cursoData['nome'],
                    'departamento' => $cursoData['departamento'],
                    'duracao' => $cursoData['duracao'],
                ]
            );
            
            $count++;
            $this->command->info("✅ Curso criado: {$cursoData['nome']} ({$cursoData['codigo']})");
        }

        $this->command->info("📚 {$count} cursos criados com sucesso!");
    }
}