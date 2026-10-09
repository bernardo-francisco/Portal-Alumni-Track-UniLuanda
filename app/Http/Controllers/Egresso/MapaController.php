<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Localizacao;
use App\Models\Egresso;
use App\Models\Conexao;
use App\Models\Curso;
use App\Models\UnidadeOrganica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MapaController extends Controller
{
    /**
     * Função auxiliar para corrigir o caminho da foto
     */
    private function corrigirFoto($fotoUrl)
    {
        if (empty($fotoUrl)) {
            return null;
        }

        if (filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
            return $fotoUrl;
        }

        if (str_starts_with($fotoUrl, 'uploads/') || str_starts_with($fotoUrl, 'storage/')) {
            return asset($fotoUrl);
        }

        if (file_exists(public_path('uploads/' . $fotoUrl))) {
            return asset('uploads/' . $fotoUrl);
        }

        if (file_exists(public_path('storage/' . $fotoUrl))) {
            return asset('storage/' . $fotoUrl);
        }

        return asset($fotoUrl);
    }

    /**
     * Mostrar o mapa para o egresso com filtros
     */
    public function index(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                            ->with('warning', 'Complete seu perfil primeiro.');
        }

        // ============================================================
        // 1. BUSCAR TODAS AS LOCALIZAÇÕES COM FILTROS
        // ============================================================
        $query = Localizacao::with(['egresso' => function($q) {
            $q->with(['curso.unidade', 'user']);
        }])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->where('is_current', true);

        // 🔍 FILTRO POR CURSO
        if ($request->filled('curso_id')) {
            $query->whereHas('egresso', function($q) use ($request) {
                $q->where('curso_id', $request->curso_id);
            });
        }

        // 🔍 FILTRO POR UNIDADE
        if ($request->filled('unidade_id')) {
            $query->whereHas('egresso.curso', function($q) use ($request) {
                $q->where('unidade_id', $request->unidade_id);
            });
        }

        // 🔍 FILTRO POR STATUS
        if ($request->filled('status')) {
            $query->whereHas('egresso', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // 🔍 FILTRO POR PAÍS
        if ($request->filled('pais')) {
            $query->where('pais', 'LIKE', '%' . $request->pais . '%');
        }

        // 🔍 BUSCA POR NOME
        if ($request->filled('search')) {
            $query->whereHas('egresso', function($q) use ($request) {
                $q->where('nome_completo', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('numero_processo', 'LIKE', '%' . $request->search . '%');
            });
        }

        $localizacoes = $query->get();

        // ============================================================
        // 2. BUSCAR IDs DOS EGRESSOS QUE ESTÃO NA REDE
        // ============================================================
        $idsConexoes = Conexao::where(function($q) use ($egresso) {
                                $q->where('solicitante_id', $egresso->id)
                                  ->orWhere('destinatario_id', $egresso->id);
                            })
                            ->where('status', 'aceito')
                            ->pluck('solicitante_id')
                            ->merge(
                                Conexao::where(function($q) use ($egresso) {
                                    $q->where('solicitante_id', $egresso->id)
                                      ->orWhere('destinatario_id', $egresso->id);
                                })
                                ->where('status', 'aceito')
                                ->pluck('destinatario_id')
                            )
                            ->unique()
                            ->filter(fn($id) => $id != $egresso->id)
                            ->values()
                            ->toArray();

        // ============================================================
        // 3. PREPARAR OS MARCADORES (com cores diferenciadas)
        // ============================================================
        $markers = $localizacoes->map(function($loc) use ($egresso, $idsConexoes) {
            $isProprio = $loc->egresso_id == $egresso->id;
            $isConexao = in_array($loc->egresso_id, $idsConexoes);

            if ($isProprio) {
                $cor = '#0d6efd';
                $tipo = 'eu';
                $icone = '👤';
            } elseif ($isConexao) {
                $cor = '#22c55e';
                $tipo = 'conexao';
                $icone = '🤝';
            } else {
                $cor = '#6c757d';
                $tipo = 'outro';
                $icone = '👤';
            }

            $cursoNome = $loc->egresso->curso->nome ?? 'N/A';
            $unidadeSigla = $loc->egresso->curso->unidade->sigla ?? 'N/A';

            return [
                'lat' => (float) $loc->latitude,
                'lng' => (float) $loc->longitude,
                'nome' => $loc->egresso->nome_completo ?? 'Egresso',
                'numero' => $loc->egresso->numero_processo ?? '',
                'curso' => $cursoNome,
                'unidade' => $unidadeSigla,
                'pais' => $loc->pais,
                'cidade' => $loc->cidade ?? '',
                'endereco' => $loc->endereco ?? '',
                'foto' => $this->corrigirFoto($loc->egresso->foto_url),
                'id' => $loc->egresso_id,
                'tipo' => $tipo,
                'conexao' => $isConexao,
                'cor' => $cor,
                'icone' => $icone,
                'status' => $loc->egresso->status,
            ];
        });

        // ============================================================
        // 4. ESTATÍSTICAS
        // ============================================================
        $total_egressos = Egresso::count();
        $total_localizados = $localizacoes->count();
        $total_conexoes = count($idsConexoes);
        $total_conexoes_localizadas = $localizacoes->filter(function($loc) use ($idsConexoes) {
            return in_array($loc->egresso_id, $idsConexoes);
        })->count();

        // ============================================================
        // 5. MINHA LOCALIZAÇÃO
        // ============================================================
        $minhaLocalizacao = Localizacao::where('egresso_id', $egresso->id)
            ->where('is_current', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        // ============================================================
        // 6. DADOS PARA FILTROS
        // ============================================================
        $cursos = Curso::orderBy('nome')->get();
        $unidades = UnidadeOrganica::orderBy('nome')->get();
        
        // Lista de países distintos para filtro
        $paises = Localizacao::whereNotNull('pais')
            ->distinct()
            ->pluck('pais')
            ->sort()
            ->values()
            ->toArray();

        // ============================================================
        // 7. CHAVE DA API DO GOOGLE MAPS
        // ============================================================
        $googleApiKey = config('services.google_maps.key') ?? env('GOOGLE_MAPS_API_KEY');
        $googleMapId = env('GOOGLE_MAPS_MAP_ID');

        Log::info('Google Maps - Configuração (Egresso)', [
            'api_key_exists' => !empty($googleApiKey),
            'api_key_length' => strlen($googleApiKey ?? ''),
            'total_markers' => $markers->count(),
            'filtros_aplicados' => $request->all(),
        ]);

        return view('egresso.mapa.index', compact(
            'localizacoes',
            'markers',
            'total_egressos',
            'total_localizados',
            'total_conexoes',
            'total_conexoes_localizadas',
            'minhaLocalizacao',
            'egresso',
            'googleApiKey',
            'googleMapId',
            'idsConexoes',
            'cursos',
            'unidades',
            'paises'
        ));
    }

    /**
     * Buscar egresso por número de processo (AJAX)
     */
    public function buscarEgresso(Request $request)
    {
        $numeroProcesso = $request->input('numero_processo');
        $egressoLogado = Auth::user()->egresso;

        if (empty($numeroProcesso)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, digite um número de processo.'
            ]);
        }

        $egresso = Egresso::where('numero_processo', $numeroProcesso)->first();

        if (!$egresso) {
            return response()->json([
                'success' => false,
                'message' => 'Egresso não encontrado.'
            ]);
        }

        $isProprio = $egresso->id == $egressoLogado->id;
        $isConexao = Conexao::where(function($q) use ($egressoLogado, $egresso) {
                                $q->where('solicitante_id', $egressoLogado->id)
                                  ->where('destinatario_id', $egresso->id);
                            })
                            ->orWhere(function($q) use ($egressoLogado, $egresso) {
                                $q->where('solicitante_id', $egresso->id)
                                  ->where('destinatario_id', $egressoLogado->id);
                            })
                            ->where('status', 'aceito')
                            ->exists();

        $localizacao = Localizacao::where('egresso_id', $egresso->id)
            ->where('is_current', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        if (!$localizacao) {
            return response()->json([
                'success' => false,
                'message' => 'Egresso não possui localização registada.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Egresso encontrado com sucesso!',
            'egresso' => [
                'id' => $egresso->id,
                'nome' => $egresso->nome_completo,
                'numero_processo' => $egresso->numero_processo,
                'foto' => $this->corrigirFoto($egresso->foto_url),
                'is_proprio' => $isProprio,
                'is_conexao' => $isConexao,
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