<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use App\Models\Egresso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicoController extends Controller
{
    /**
     * Lista de pedidos de serviços (com filtros)
     */
    public function index(Request $request)
    {
        $status     = $request->get('status', 'todos');
        $pesquisa   = trim($request->get('q', ''));
        $egressoId  = $request->get('egresso_id');
        $dataInicio = $request->get('data_inicio');
        $dataFim    = $request->get('data_fim');
        $ordenacao  = $request->get('ordenacao', 'recentes');

        $query = Servico::with('egresso');

        // ========================================================
        // FILTRO: Status
        // ========================================================
        if ($status !== 'todos' && !empty($status)) {
            $query->where('status', $status);
        }

        // ========================================================
        // FILTRO: Pesquisa (nome do egresso, serviço, descrição)
        // ========================================================
        if (!empty($pesquisa)) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('servico', 'like', "%{$pesquisa}%")
                  ->orWhere('descricao', 'like', "%{$pesquisa}%")
                  ->orWhereHas('egresso', function ($q2) use ($pesquisa) {
                      $q2->where('nome_completo', 'like', "%{$pesquisa}%")
                         ->orWhere('numero_processo', 'like', "%{$pesquisa}%");
                  });
            });
        }

        // ========================================================
        // FILTRO: Egresso específico
        // ========================================================
        if (!empty($egressoId)) {
            $query->where('egresso_id', $egressoId);
        }

        // ========================================================
        // FILTRO: Data início
        // ========================================================
        if (!empty($dataInicio)) {
            $query->whereDate('created_at', '>=', $dataInicio);
        }

        // ========================================================
        // FILTRO: Data fim
        // ========================================================
        if (!empty($dataFim)) {
            $query->whereDate('created_at', '<=', $dataFim);
        }

        // ========================================================
        // ORDENAÇÃO
        // ========================================================
        switch ($ordenacao) {
            case 'antigos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'atendidos_recentes':
                $query->where('status', 'atendido')
                      ->orderBy('updated_at', 'desc');
                break;
            case 'pendentes_primeiro':
                $query->orderByRaw("FIELD(status, 'pendente', 'andamento', 'atendido', 'cancelado')")
                      ->orderBy('created_at', 'desc');
                break;
            case 'recentes':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $pedidos = $query->paginate(20)->appends($request->query());

        // ========================================================
        // ESTATÍSTICAS (sem filtros, para os cards)
        // ========================================================
        $totalPedidos   = Servico::count();
        $pendentes      = Servico::where('status', 'pendente')->count();
        $andamento      = Servico::where('status', 'andamento')->count();
        $atendidos      = Servico::where('status', 'atendido')->count();
        $cancelados     = Servico::where('status', 'cancelado')->count();

        // Lista de egressos (para o dropdown)
        $egressos = Egresso::orderBy('nome_completo')
            ->get(['id', 'nome_completo']);

        $temFiltros = $request->hasAny(['q', 'status', 'egresso_id', 'data_inicio', 'data_fim', 'ordenacao'])
            && ($status !== 'todos' || !empty($pesquisa) || !empty($egressoId) || !empty($dataInicio) || !empty($dataFim));

        return view('admin.servicos.index', compact(
            'pedidos',
            'totalPedidos',
            'pendentes',
            'andamento',
            'atendidos',
            'cancelados',
            'egressos',
            'status',
            'temFiltros'
        ));
    }

    /**
     * Ver detalhes de um pedido
     */
    public function show($id)
    {
        $pedido = Servico::with('egresso')->findOrFail($id);
        return view('admin.servicos.show', compact('pedido'));
    }

    /**
     * Marcar pedido como atendido
     */
    public function atender(Request $request, $id)
    {
        $request->validate([
            'resposta_admin' => 'nullable|string|max:2000',
        ]);

        $pedido = Servico::findOrFail($id);

        $pedido->update([
            'status'         => 'atendido',
            'resposta_admin' => $request->resposta_admin,
            'atendido_em'    => now(),
        ]);

        return back()->with('success', 'Pedido atendido com sucesso!');
    }

    /**
     * Marcar pedido como em andamento
     */
    public function emAndamento($id)
    {
        $pedido = Servico::findOrFail($id);

        $pedido->update([
            'status' => 'andamento',
        ]);

        return back()->with('success', 'Pedido marcado como em andamento.');
    }

    /**
     * Cancelar pedido
     */
    public function cancelar($id)
    {
        $pedido = Servico::findOrFail($id);

        $pedido->update([
            'status' => 'cancelado',
        ]);

        return back()->with('success', 'Pedido cancelado.');
    }

    /**
     * Eliminar pedido
     */
    public function destroy($id)
    {
        $pedido = Servico::findOrFail($id);
        $pedido->delete();

        return back()->with('success', 'Pedido eliminado!');
    }
}