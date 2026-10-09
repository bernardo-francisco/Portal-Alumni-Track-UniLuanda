<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompletoExport implements WithMultipleSheets
{
    protected array $dados;

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    public function sheets(): array
{
    $d = $this->dados;

    $taxa          = $d['taxaEmpregabilidade'] ?? 0;
    $totalEgressos = $d['totalEgressos'] ?? 0;

    return [

        // =========================================================
        // 1. EGRESSOS — LISTA PRIMEIRO
        // =========================================================
        new SecaoExport(
            'Egressos',
            ['Nº Processo', 'Nome', 'Email', 'Curso', 'Unidade', 'Ano Formatura', 'Status', 'Localização', 'Cargo Atual'],
            $d['egressos']->map(fn($e) => [
                $e->numero_processo ?? '',
                $e->nome_completo ?? '',
                $e->email ?? '',
                $e->curso->nome ?? '',
                $e->curso->unidade->sigla ?? '',
                $e->ano_formatura ?? '',
                $e->status ?? '',
                $e->localizacaoAtual
                    ? trim(($e->localizacaoAtual->cidade ?? '') . ', ' . ($e->localizacaoAtual->pais ?? ''), ', ')
                    : '',
                $e->profissionalAtual->cargo ?? '',
            ])->toArray()
        ),

        // =========================================================
        // 2. RESUMO EXECUTIVO
        // =========================================================
        new SecaoExport(
            'Resumo Executivo',
            ['Métrica', 'Valor'],
            [
                ['Egressos Registados',      $d['totalEgressos']],
                ['Empregados',               $d['empregados']],
                ['Taxa de Empregabilidade',  number_format($taxa, 2) . '%'],
                ['Países Alcançados',        $d['totalPaises']],
                ['Conexões na Rede',         $d['totalConexoes']],
                ['Mensagens Trocadas',       $d['totalMensagens']],
                ['Chamadas Realizadas',      $d['totalChamadas']],
                ['Áudios Enviados',          $d['totalAudios']],
                ['Candidaturas',             $d['totalCandidaturas']],
                ['Oportunidades',            $d['totalOportunidades']],
                ['Eventos',                  $d['totalEventos']],
                ['Inscrições',               $d['totalInscricoes']],
                ['Feedbacks',                $d['totalFeedbacks']],
                ['Serviços Solicitados',     $d['totalServicos']],
                ['Notificações',             $d['totalNotificacoes']],
                ['Registos Profissionais',   $d['totalProfissionais']],
            ]
        ),

        
    ];
}}