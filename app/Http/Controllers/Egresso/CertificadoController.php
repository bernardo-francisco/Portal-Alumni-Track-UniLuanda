<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\InscricaoEvento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CertificadoController extends Controller
{
    /**
     * Descarrega o certificado PDF de um evento
     */
    public function download($inscricaoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $inscricao = InscricaoEvento::with(['evento', 'egresso.curso'])
            ->where('id', $inscricaoId)
            ->where('egresso_id', $egresso->id)
            ->firstOrFail();

        if ($inscricao->evento->data_inicio && $inscricao->evento->data_inicio->isFuture()) {
            return back()->with('error', 'O certificado só fica disponível após o evento.');
        }

        if (!$inscricao->presente) {
            return back()->with('error', 'O certificado só é emitido para participantes presentes.');
        }

        // ✅ Garantir código do certificado
        if (!$inscricao->certificado_codigo) {
            $inscricao->update([
                'certificado_codigo' => InscricaoEvento::gerarCodigoComprovativo(),
            ]);
        }

        try {
            $pdf = Pdf::loadView('pdf.certificado', [
                    'inscricao' => $inscricao,
                    'evento'    => $inscricao->evento,
                    'codigo'    => $inscricao->certificado_codigo,
                ])
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont'          => 'DejaVu Sans',
                ]);

            $filename = 'certificado_' . Str::slug($egresso->nome_completo) . '_' . Str::slug($inscricao->evento->titulo) . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar certificado: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar certificado. Tente novamente.');
        }
    }

    public function preview($inscricaoId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $inscricao = InscricaoEvento::with(['evento', 'egresso.curso'])
            ->where('id', $inscricaoId)
            ->where('egresso_id', $egresso->id)
            ->firstOrFail();

        // ✅ Garantir código
        if (!$inscricao->certificado_codigo) {
            $inscricao->update([
                'certificado_codigo' => InscricaoEvento::gerarCodigoComprovativo(),
            ]);
        }

        $pdf = Pdf::loadView('pdf.certificado', [
                'inscricao' => $inscricao,
                'evento'    => $inscricao->evento,
                'codigo'    => $inscricao->certificado_codigo,
            ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

        return $pdf->stream('certificado.pdf');
    }
}