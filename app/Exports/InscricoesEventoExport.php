<?php

namespace App\Exports;

use App\Models\Evento;
use Illuminate\Support\Str;

class InscricoesEventoExport
{
    /**
     * Gera CSV de inscrições de um evento
     */
    public static function download($eventoId)
    {
        $evento = Evento::with(['inscricoes.egresso.curso'])->findOrFail($eventoId);

        $filename = 'inscritos_' . Str::slug($evento->titulo) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($evento) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 (para acentos abrirem bem no Excel)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeçalhos
            fputcsv($file, [
                'ID',
                'Nome',
                'Email',
                'Telefone',
                'Curso',
                'Inscrição',
                'Presença',
                'Check-in',
            ], ';');

            // Linhas
            foreach ($evento->inscricoes as $inscricao) {
                $egresso = $inscricao->egresso;

                fputcsv($file, [
                    $inscricao->id,
                    $egresso?->nome_completo ?? 'N/A',
                    $egresso?->email ?? 'N/A',
                    $egresso?->telefone ?? 'N/A',
                    $egresso?->curso?->nome ?? 'N/A',
                    $inscricao->created_at->format('d/m/Y H:i'),
                    $inscricao->presente ? 'Presente' : 'Ausente',
                    $inscricao->checkin_em ? $inscricao->checkin_em->format('d/m/Y H:i') : '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}