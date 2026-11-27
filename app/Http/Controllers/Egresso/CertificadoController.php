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
     * Descarrega o certificado PDF de um evento.
     *
     * Regras de emissão (todas obrigatórias):
     *  - Inscrição pertence ao egresso autenticado
     *  - Evento já aconteceu (data_inicio no passado e não nula)
     *  - Egresso marcado como presente
     *  - Inscrição com status "confirmada"
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

        // ── Validações de elegibilidade ────────────────────────────────
        $erro = $this->validarElegibilidade($inscricao);

        if ($erro !== null) {
            return back()->with('error', $erro);
        }

        // ✅ Garantir código do certificado (emissão formal)
        $this->garantirCodigoCertificado($inscricao);

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

            $filename = $this->nomeFicheiro($egresso, $inscricao);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar certificado: ' . $e->getMessage(), [
                'inscricao_id' => $inscricao->id,
                'egresso_id'   => $egresso->id,
            ]);

            return back()->with('error', 'Erro ao gerar certificado. Tente novamente.');
        }
    }

    /**
     * Pré-visualiza o certificado PDF (stream no browser).
     *
     * Aplica as MESMAS regras de elegibilidade do download.
     * O código é gerado apenas em memória — NÃO é persistido nesta ação,
     * para que "ver o preview" não constitua emissão formal do certificado.
     */
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

        // ── Validações de elegibilidade (iguais ao download) ───────────
        $erro = $this->validarElegibilidade($inscricao);

        if ($erro !== null) {
            return back()->with('error', $erro);
        }

        // Preview: usa o código existente se houver; caso contrário gera
        // um código temporário SEM persistir (não emite formalmente).
        $codigo = $inscricao->certificado_codigo
            ?: InscricaoEvento::gerarCodigoComprovativo();

        try {
            $pdf = Pdf::loadView('pdf.certificado', [
                    'inscricao' => $inscricao,
                    'evento'    => $inscricao->evento,
                    'codigo'    => $codigo,
                ])
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isRemoteEnabled'      => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont'          => 'DejaVu Sans',
                ]);

            return $pdf->stream('certificado.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao pré-visualizar certificado: ' . $e->getMessage(), [
                'inscricao_id' => $inscricao->id,
                'egresso_id'   => $egresso->id,
            ]);

            return back()->with('error', 'Erro ao gerar certificado. Tente novamente.');
        }
    }

    /* ================================================================
     |  Helpers privados
     ================================================================ */

    /**
     * Verifica todas as regras de negócio para emissão/visualização
     * do certificado. Devolve a mensagem de erro, ou null se elegível.
     */
    private function validarElegibilidade(InscricaoEvento $inscricao): ?string
    {
        $evento = $inscricao->evento;

        // 1. Evento tem data definida e já passou
        if (!$evento->data_inicio || $evento->data_inicio->isFuture()) {
            return 'O certificado só fica disponível após o evento.';
        }

        // 2. Inscrição confirmada
        if ($inscricao->status !== 'confirmada') {
            return 'Apenas inscrições confirmadas podem emitir certificado.';
        }

        // 3. Presença registada
        if (!$inscricao->presente) {
            return 'O certificado só é emitido para participantes presentes.';
        }

        return null;
    }

    /**
     * Garante que a inscrição tem um código de certificado persistido.
     */
    private function garantirCodigoCertificado(InscricaoEvento $inscricao): void
    {
        if (!$inscricao->certificado_codigo) {
            $inscricao->update([
                'certificado_codigo' => InscricaoEvento::gerarCodigoComprovativo(),
            ]);
            $inscricao->refresh();
        }
    }

    /**
     * Constrói o nome do ficheiro PDF, com fallbacks seguros.
     */
    private function nomeFicheiro($egresso, InscricaoEvento $inscricao): string
    {
        $nome  = Str::slug($egresso->nome_completo ?? 'egresso');
        $titulo = Str::slug($inscricao->evento->titulo ?? 'evento');

        $nome   = Str::limit($nome ?: 'egresso', 40, '');
        $titulo = Str::limit($titulo ?: 'evento', 60, '');

        return "certificado_{$nome}_{$titulo}.pdf";
    }
}