<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use App\Models\Admin;
use App\Models\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServicoController extends Controller
{
    /**
     * Lista os pedidos do egresso (com filtros)
     */
    public function index(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        // ========================================================
        // FILTROS
        // ========================================================
        $status     = $request->get('status', 'todos');
        $pesquisa   = trim($request->get('q', ''));
        $dataInicio = $request->get('data_inicio');
        $dataFim    = $request->get('data_fim');
        $ordenacao  = $request->get('ordenacao', 'recentes');

        $query = Servico::where('egresso_id', $egresso->id);

        // Filtro: Status
        if ($status !== 'todos' && !empty($status)) {
            $query->where('status', $status);
        }

        // Filtro: Pesquisa (serviço ou descrição)
        if (!empty($pesquisa)) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('servico', 'like', "%{$pesquisa}%")
                  ->orWhere('descricao', 'like', "%{$pesquisa}%");
            });
        }

        // Filtro: Data início
        if (!empty($dataInicio)) {
            $query->whereDate('created_at', '>=', $dataInicio);
        }

        // Filtro: Data fim
        if (!empty($dataFim)) {
            $query->whereDate('created_at', '<=', $dataFim);
        }

        // Ordenação
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

        $pedidos = $query->paginate(10)->appends($request->query());

        // ========================================================
        // ESTATÍSTICAS (sem filtros, para os cards)
        // ========================================================
        $total_pedidos = Servico::where('egresso_id', $egresso->id)->count();
        $pendentes     = Servico::where('egresso_id', $egresso->id)->where('status', 'pendente')->count();
        $andamento     = Servico::where('egresso_id', $egresso->id)->where('status', 'andamento')->count();
        $atendidos     = Servico::where('egresso_id', $egresso->id)->where('status', 'atendido')->count();
        $cancelados    = Servico::where('egresso_id', $egresso->id)->where('status', 'cancelado')->count();

        $statusAtual = $status;

        $temFiltros = $request->hasAny(['q', 'egresso_id', 'data_inicio', 'data_fim', 'ordenacao'])
            || ($status !== 'todos' && !empty($status))
            || !empty($pesquisa)
            || !empty($dataInicio)
            || !empty($dataFim);

        return view('egresso.servicos.index', compact(
            'pedidos',
            'total_pedidos',
            'pendentes',
            'atendidos',
            'andamento',
            'cancelados',
            'statusAtual',
            'temFiltros'
        ));
    }

    /**
     * Formulário de novo pedido
     */
    public function create()
    {
        return view('egresso.servicos.solicitar');
    }

    /**
     * Criar novo pedido
     */
    public function store(Request $request)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $validated = $request->validate([
            'servico'   => 'required|string|max:100',
            'descricao' => 'required|string|max:2000',
        ]);

        $pedido = Servico::create([
            'egresso_id' => $egresso->id,
            'servico'    => $validated['servico'],
            'descricao'  => $validated['descricao'],
            'status'     => 'pendente',
        ]);

        // Notificar admins
        try {
            $admins = Admin::all();
            foreach ($admins as $adminAccount) {
                Notificacao::create([
                    'admin_id' => $adminAccount->id,
                    'tipo'     => 'servico',
                    'titulo'   => '🛠️ Novo pedido de serviço',
                    'mensagem' => $egresso->nome_completo . ' solicitou: ' . $validated['servico'],
                    'link'     => route('admin.servicos.show', $pedido->id),
                    'lida'     => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao notificar admins sobre novo serviço: ' . $e->getMessage());
        }

        return redirect()->route('egresso.servicos.index')
            ->with('success', 'Pedido enviado com sucesso! Aguarde o atendimento.');
    }

    /**
     * Cancelar pedido (só se estiver pendente)
     */
    public function destroy($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            return redirect()->route('egresso.perfil.editar')
                ->with('warning', 'Complete seu perfil primeiro.');
        }

        $pedido = Servico::where('id', $id)
            ->where('egresso_id', $egresso->id)
            ->firstOrFail();

        if ($pedido->status !== 'pendente') {
            return back()->with('error', 'Não é possível cancelar este pedido.');
        }

        $pedido->update(['status' => 'cancelado']);

        return back()->with('success', 'Pedido cancelado com sucesso.');
    }
}