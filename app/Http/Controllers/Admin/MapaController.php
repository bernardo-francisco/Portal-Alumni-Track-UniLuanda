<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localizacao;
use App\Models\Egresso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapaController extends Controller
{
    // Função auxiliar para corrigir o caminho da foto
    private function corrigirFoto($fotoUrl)
    {
        if (empty($fotoUrl)) {
            return null;
        }

        // Se já for uma URL completa
        if (filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
            return $fotoUrl;
        }

        // Se começar com 'uploads/' ou 'storage/'
        if (str_starts_with($fotoUrl, 'uploads/') || str_starts_with($fotoUrl, 'storage/')) {
            return asset($fotoUrl);
        }

        // Tentar com 'uploads/'
        if (file_exists(public_path('uploads/' . $fotoUrl))) {
            return asset('uploads/' . $fotoUrl);
        }

        // Tentar com 'storage/'
        if (file_exists(public_path('storage/' . $fotoUrl))) {
            return asset('storage/' . $fotoUrl);
        }

        // Fallback
        return asset($fotoUrl);
    }

    public function index()
    {
        $localizacoes = Localizacao::with('egresso')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_current', true)
            ->get();

        $total_egressos = Egresso::count();
        $total_localizados = $localizacoes->count();

        // Dados para o mapa (JSON)
        $markers = $localizacoes->map(function($loc) {
            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'nome' => $loc->egresso->nome_completo ?? 'Egresso',
                'numero' => $loc->egresso->numero_processo ?? '',
                'pais' => $loc->pais,
                'cidade' => $loc->cidade ?? '',
                'endereco' => $loc->endereco ?? '',
                'foto' => $this->corrigirFoto($loc->egresso->foto_url),
                'id' => $loc->egresso_id,
            ];
        });

        return view('admin.mapa.index', compact('localizacoes', 'total_egressos', 'total_localizados', 'markers'));
    }

    // ============================================================
    // 🔍 MÉTODO PARA BUSCAR EGRESSO POR NÚMERO DE PROCESSO
    // ============================================================
    public function buscarEgresso(Request $request)
    {
        $numeroProcesso = $request->input('numero_processo');
        
        // Validação: Número de processo vazio
        if (empty($numeroProcesso)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, digite um número de processo.'
            ]);
        }

        // Buscar egresso pelo número de processo
        $egresso = Egresso::where('numero_processo', $numeroProcesso)->first();

        // Validação: Egresso não encontrado
        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Egresso não encontrado.'
            ]);
        }

        // Buscar localização atual do egresso
        $localizacao = Localizacao::where('egresso_id', $egresso->id)
            ->where('is_current', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        // Validação: Egresso não possui localização
        if (!$localizacao) {
            return response()->json([
                'success' => false,
                'message' => 'Egresso não possui localização registada.'
            ]);
        }

        // ✅ Sucesso - Egresso encontrado com localização
        return response()->json([
            'success' => true,
            'message' => 'Egresso encontrado com sucesso!',
            'egresso' => [
                'id' => $egresso->id,
                'nome' => $egresso->nome_completo,
                'numero_processo' => $egresso->numero_processo,
                'foto' => $this->corrigirFoto($egresso->foto_url),
            ],
            'localizacao' => [
                'lat' => (float) $localizacao->latitude,
                'lng' => (float) $localizacao->longitude,
                'pais' => $localizacao->pais ?? 'Não informado',
                'cidade' => $localizacao->cidade ?? 'Não informado',
                'endereco' => $localizacao->endereco ?? '',
            ]
        ]);
    }
}