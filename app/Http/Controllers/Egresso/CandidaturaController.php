<?php

namespace App\Http\Controllers\Egresso;

use App\Http\Controllers\Controller;
use App\Models\Candidatura;
use App\Models\Oportunidade;
use App\Services\NotificacaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CandidaturaController extends Controller
{
    // ============================================================
    // 📋 MINHAS CANDIDATURAS (lista)
    // ============================================================
    public function index()
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $candidaturas = Candidatura::with(['oportunidade'])
            ->where('egresso_id', $egresso->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ Apontar para o ficheiro que já existe
        return view('egresso.oportunidades.candidaturas', compact('candidaturas'));
    }

    // ============================================================
    // 👁️ VER DETALHES DE UMA CANDIDATURA
    // ============================================================
    public function show($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $candidatura = Candidatura::with(['oportunidade', 'egresso'])
            ->where('egresso_id', $egresso->id)
            ->findOrFail($id);

        return view('egresso.candidaturas.show', compact('candidatura'));
    }

    // ============================================================
    // ✅ CONFIRMAR PRESENÇA NA ENTREVISTA
    // ============================================================
    public function confirmarPresenca($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $candidatura = Candidatura::where('egresso_id', $egresso->id)
            ->whereNotNull('data_entrevista')
            ->findOrFail($id);

        // Se já confirmou, não faz nada
        if ($candidatura->confirmado_pelo_egresso) {
            return back()->with('info', 'Já tinhas confirmado a tua presença.');
        }

        try {
            $candidatura->update([
                'confirmado_pelo_egresso' => true,
                'confirmado_em'           => now(),
            ]);

            // Notificar admin + egresso
            try {
                NotificacaoService::presencaConfirmada($candidatura);
            } catch (\Exception $e) {
                Log::warning('Erro ao notificar presença confirmada: ' . $e->getMessage());
            }

            return back()->with('success', 'Presença confirmada com sucesso! Boa sorte na entrevista.');

        } catch (\Exception $e) {
            Log::error('Erro ao confirmar presença: ' . $e->getMessage());
            return back()->with('error', 'Erro ao confirmar presença. Tenta novamente.');
        }
    }

    // ============================================================
    // 📅 GERAR FICHEIRO .ICS (Adicionar entrevista ao calendário)
    // ============================================================
    public function entrevistaIcs($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $candidatura = Candidatura::with(['oportunidade'])
            ->where('egresso_id', $egresso->id)
            ->findOrFail($id);

        if (!$candidatura->data_entrevista) {
            abort(404, 'Entrevista não marcada.');
        }

        $inicio = $candidatura->data_entrevista->copy();
        $fim = $inicio->copy()->addHour();

        $titulo = 'Entrevista - ' . ($candidatura->oportunidade->titulo ?? 'Oportunidade');
        $local = $candidatura->local_entrevista ?? 'A confirmar';
        $descricao = 'Entrevista para a oportunidade: ' . ($candidatura->oportunidade->titulo ?? '');

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//UniLuanda//Egressos//PT\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . $candidatura->id . "-" . now()->timestamp . "@uniluanda.ao\r\n";
        $ics .= "DTSTAMP:" . now()->format('Ymd\THis') . "\r\n";
        $ics .= "DTSTART:" . $inicio->format('Ymd\THis') . "\r\n";
        $ics .= "DTEND:" . $fim->format('Ymd\THis') . "\r\n";
        $ics .= "SUMMARY:" . $this->escapeIcs($titulo) . "\r\n";
        $ics .= "DESCRIPTION:" . $this->escapeIcs($descricao) . "\r\n";
        $ics .= "LOCATION:" . $this->escapeIcs($local) . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "BEGIN:VALARM\r\n";
        $ics .= "TRIGGER:-PT1H\r\n";
        $ics .= "ACTION:DISPLAY\r\n";
        $ics .= "DESCRIPTION:Lembrete: Entrevista em 1 hora\r\n";
        $ics .= "END:VALARM\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="entrevista-' . $candidatura->id . '.ics"',
        ]);
    }

    // ============================================================
    // 🆕 CRIAR CANDIDATURA
    // ============================================================
    public function store(Request $request, $oportunidadeId)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $request->validate([
            'mensagem_motivacional' => 'nullable|string|max:2000',
            'cv_anexo'              => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $oportunidade = Oportunidade::findOrFail($oportunidadeId);

        $existe = Candidatura::where('egresso_id', $egresso->id)
            ->where('oportunidade_id', $oportunidade->id)
            ->exists();

        if ($existe) {
            return back()->with('warning', 'Já te candidataste a esta oportunidade.');
        }

        try {
            $cvPath = null;
            if ($request->hasFile('cv_anexo')) {
                $cvPath = $request->file('cv_anexo')->store('cvs', 'public');
            }

            $candidatura = Candidatura::create([
                'egresso_id'            => $egresso->id,
                'oportunidade_id'       => $oportunidade->id,
                'mensagem_motivacional' => $request->mensagem_motivacional,
                'cv_anexo'              => $cvPath,
                'status'                => 'pendente',
            ]);

            try {
                NotificacaoService::novaCandidatura(
                    $egresso,
                    $oportunidade,
                    $candidatura->id
                );
            } catch (\Exception $e) {
                Log::warning('Erro ao notificar nova candidatura: ' . $e->getMessage());
            }

            return redirect()
                ->route('egresso.minhas.candidaturas')
                ->with('success', 'Candidatura enviada com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar candidatura: ' . $e->getMessage());
            return back()->with('error', 'Erro ao enviar candidatura. Tenta novamente.');
        }
    }

    // ============================================================
    // ❌ CANCELAR CANDIDATURA
    // ============================================================
    public function cancelar($id)
    {
        $egresso = Auth::user()->egresso;

        if (!$egresso) {
            abort(403, 'Egresso não encontrado.');
        }

        $candidatura = Candidatura::where('egresso_id', $egresso->id)
            ->findOrFail($id);

        if ($candidatura->status !== 'pendente') {
            return back()->with('error', 'Só podes cancelar candidaturas pendentes.');
        }

        try {
            $candidatura->delete();

            return redirect()
                ->route('egresso.minhas.candidaturas')
                ->with('success', 'Candidatura cancelada com sucesso.');

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar candidatura: ' . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar candidatura.');
        }
    }

    // ============================================================
    // 🔧 HELPER — ESCAPAR TEXTO PARA ICS
    // ============================================================
    private function escapeIcs(string $texto): string
    {
        $texto = str_replace(
            ["\\", ",", ";", "\n", "\r"],
            ["\\\\", "\\,", "\\;", "\\n", ""],
            $texto
        );

        return $texto;
    }
}