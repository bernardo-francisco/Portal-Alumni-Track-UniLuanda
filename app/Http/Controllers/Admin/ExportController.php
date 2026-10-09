<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CandidaturasExport;
use App\Exports\InscricoesEventoExport;
use App\Http\Controllers\Controller;
use App\Models\Candidatura;
use App\Models\Evento;
use App\Models\Oportunidade;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExportController extends Controller
{
    // ============================================================
    // 📊 EXPORTAR CANDIDATURAS (CSV)
    // ============================================================
    public function candidaturasCsv(Request $request)
    {
        return CandidaturasExport::download([
            'status'          => $request->get('status', 'todos'),
            'oportunidade_id' => $request->get('oportunidade_id'),
        ]);
    }

    // ============================================================
    // 📄 RELATÓRIO CANDIDATURAS (PDF)
    // ============================================================
    public function candidaturasPdf(Request $request)
    {
        $oportunidadeId = $request->get('oportunidade_id');
        $status = $request->get('status', 'todos');

        $query = Candidatura::with(['egresso.curso', 'oportunidade']);

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        if ($oportunidadeId) {
            $query->where('oportunidade_id', $oportunidadeId);
        }

        $candidaturas = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total'      => $candidaturas->count(),
            'pendente'   => $candidaturas->where('status', 'pendente')->count(),
            'em_analise' => $candidaturas->where('status', 'em_analise')->count(),
            'entrevista' => $candidaturas->where('status', 'entrevista')->count(),
            'aprovado'   => $candidaturas->where('status', 'aprovado')->count(),
            'rejeitado'  => $candidaturas->where('status', 'rejeitado')->count(),
        ];

        $oportunidade = $oportunidadeId ? Oportunidade::find($oportunidadeId) : null;

        try {
            // ✅ Usa a view na pasta admin/relatorios/pdf
            $pdf = Pdf::loadView('admin.relatorios.pdf.candidaturas', compact(
                'candidaturas',
                'stats',
                'oportunidade'
            ))->setPaper('a4', 'portrait');

            $filename = 'relatorio_candidaturas_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório PDF: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 📊 EXPORTAR INSCRITOS DE UM EVENTO (CSV)
    // ============================================================
    public function inscritosEventoCsv($eventoId)
    {
        return InscricoesEventoExport::download($eventoId);
    }
}