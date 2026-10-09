<?php

namespace App\Exports;

use App\Models\Candidatura;
use Illuminate\Support\Str;

class CandidaturasExport
{
    /**
     * Gera CSV de candidaturas
     */
    public static function download($filtros = [])
    {
        $query = Candidatura::with(['egresso.curso', 'oportunidade.unidade']);

        if (!empty($filtros['status']) && $filtros['status'] !== 'todos') {
            $query->where('status', $filtros['status']);
        }

        if (!empty($filtros['oportunidade_id'])) {
            $query->where('oportunidade_id', $filtros['oportunidade_id']);
        }

        $candidaturas = $query->orderBy('created_at', 'desc')->get();

        $filename = 'candidaturas_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($candidaturas) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'ID',
                'Egresso',
                'Email',
                'Telefone',
                'Curso',
                'Oportunidade',
                'Empresa',
                'Unidade',
                'Status',
                'Data Candidatura',
                'Data Entrevista',
                'Local Entrevista',
                'Motivo Rejeição',
            ], ';');

            // Linhas
            foreach ($candidaturas as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->egresso?->nome_completo ?? 'N/A',
                    $c->egresso?->email ?? 'N/A',
                    $c->egresso?->telefone ?? 'N/A',
                    $c->egresso?->curso?->nome ?? 'N/A',
                    $c->oportunidade?->titulo ?? 'N/A',
                    $c->oportunidade?->empresa ?? 'N/A',
                    $c->oportunidade?->unidade?->nome ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $c->status)),
                    $c->created_at->format('d/m/Y H:i'),
                    $c->data_entrevista ? $c->data_entrevista->format('d/m/Y H:i') : '-',
                    $c->local_entrevista ?? '-',
                    $c->motivo_rejeicao ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}