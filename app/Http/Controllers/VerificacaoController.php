<?php

namespace App\Http\Controllers;

use App\Models\InscricaoEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerificacaoController extends Controller
{
    /**
     * Página principal de verificação (formulário)
     */
    public function index()
    {
        return view('verificacao.index');
    }

    /**
     * Verifica um código de comprovativo OU certificado
     */
    public function verificar($codigo)
    {
        $codigo = strtoupper(trim($codigo));

        // Validação básica do formato
        if (!preg_match('/^[A-F0-9]{8,20}$/', $codigo)) {
            return view('verificacao.resultado', [
                'valido'   => false,
                'codigo'   => $codigo,
                'motivo'   => 'Formato de código inválido.',
            ]);
        }

        // Buscar primeiro por certificado
        $inscricao = InscricaoEvento::with(['egresso', 'evento'])
            ->where('certificado_codigo', $codigo)
            ->first();

        $tipo = 'certificado';

        // Se não encontrar, tentar comprovativo
        if (!$inscricao) {
            $inscricao = InscricaoEvento::with(['egresso', 'evento'])
                ->where('codigo_comprovativo', $codigo)
                ->first();

            $tipo = 'comprovativo';
        }

        // Se não encontrou nenhum
        if (!$inscricao) {
            return view('verificacao.resultado', [
                'valido' => false,
                'codigo' => $codigo,
                'motivo' => 'Código não encontrado no sistema.',
            ]);
        }

        return view('verificacao.resultado', [
            'valido'     => true,
            'codigo'     => $codigo,
            'tipo'       => $tipo,
            'inscricao'  => $inscricao,
            'egresso'    => $inscricao->egresso,
            'evento'     => $inscricao->evento,
        ]);
    }
}