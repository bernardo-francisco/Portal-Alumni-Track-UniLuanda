<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function suggestJobs($skills)
    {
        if (empty($this->apiKey)) {
            return $this->getDefaultSuggestions($skills);
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um consultor de carreira especializado em ajudar ex-estudantes a encontrar oportunidades profissionais.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Com base nas seguintes habilidades: {$skills}, sugira 5 carreiras ou empregos adequados para um ex-estudante universitário em Angola. Retorne apenas como JSON com os campos: title, description, salary_range"
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);
            
            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                return json_decode($content, true) ?? $this->getDefaultSuggestions($skills);
            }
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
        }
        
        return $this->getDefaultSuggestions($skills);
    }

    private function getDefaultSuggestions($skills)
    {
        return [
            ['title' => 'Desenvolvedor de Software', 'description' => 'Desenvolvimento de aplicações e sistemas', 'salary_range' => '500k - 1.5M AOA'],
            ['title' => 'Analista de Dados', 'description' => 'Análise e interpretação de dados', 'salary_range' => '400k - 1.2M AOA'],
            ['title' => 'Gestor de Projetos', 'description' => 'Coordenação de equipas e projetos', 'salary_range' => '600k - 1.8M AOA'],
            ['title' => 'Consultor de TI', 'description' => 'Consultoria em tecnologia', 'salary_range' => '500k - 2M AOA'],
            ['title' => 'Especialista em Marketing Digital', 'description' => 'Estratégias de marketing online', 'salary_range' => '350k - 1M AOA'],
        ];
    }

    public function matchOpportunities($profile)
    {
        // Implementar matching de oportunidades baseado no perfil
        // Pode usar embeddings ou regras simples
        return [];
    }

    public function generateResumeTips($jobTitle)
    {
        if (empty($this->apiKey)) {
            return [
                'Destaque suas principais conquistas',
                'Use palavras-chave relevantes para a vaga',
                'Inclua projetos práticos realizados',
                'Mencione certificações e cursos complementares',
                'Personalize o currículo para cada candidatura'
            ];
        }
        
        // Implementar chamada à API OpenAI para dicas personalizadas
        return [];
    }
}