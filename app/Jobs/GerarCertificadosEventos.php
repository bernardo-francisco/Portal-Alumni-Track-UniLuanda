<?php

namespace App\Jobs;

use App\Models\Evento;
use App\Models\InscricaoEvento;
use App\Services\NotificacaoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GerarCertificadosEventos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('🎓 [Certificados] A gerar certificados...');

        $eventos = Evento::where('is_active', true)
            ->where(function ($q) {
                $q->where('data_fim', '<', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('data_fim')
                         ->where('data_inicio', '<', now()->subDay());
                  });
            })
            ->with(['inscricoes' => function ($q) {
                $q->where('presente', true)
                  ->where('certificado_emitido', false);
            }])
            ->get();

        $total = 0;

        foreach ($eventos as $evento) {
            foreach ($evento->inscricoes as $inscricao) {
                try {
                    $this->gerarCertificado($inscricao, $evento);
                    $total++;
                } catch (\Exception $e) {
                    Log::error("Erro ao gerar certificado: {$e->getMessage()}", [
                        'inscricao_id' => $inscricao->id,
                    ]);
                }
            }
        }

        Log::info("🎓 [Certificados] {$total} certificados gerados.");
    }

    private function gerarCertificado(InscricaoEvento $inscricao, Evento $evento): void
    {
        // ✅ Gera código único
        $codigo = InscricaoEvento::gerarCodigoComprovativo();

        $pdf = Pdf::loadView('pdf.certificado', [
            'inscricao' => $inscricao->load('egresso.curso'),
            'evento'    => $evento,
            'codigo'    => $codigo,
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'DejaVu Sans',
        ]);

        $filename = "certificados/{$evento->id}/certificado_{$inscricao->id}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $inscricao->update([
            'certificado_emitido'    => true,
            'certificado_url'        => $filename,
            'certificado_codigo'     => $codigo,
            'certificado_emitido_em' => now(),
        ]);

        NotificacaoService::criarParaEgresso(
            $inscricao->egresso_id,
            "🎓 Certificado disponível!",
            "O teu certificado do evento '{$evento->titulo}' já está disponível.",
            'certificado',
            "/egresso/certificados/{$inscricao->id}/preview"
        );
    }
}