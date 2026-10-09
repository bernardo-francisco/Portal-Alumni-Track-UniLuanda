<?php

namespace App\Console\Commands;

use App\Mail\LembreteEventoMail;
use App\Models\Evento;
use App\Models\InscricaoEvento;
use App\Services\NotificacaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarLembretesEventos extends Command
{
    protected $signature = 'eventos:lembretes {--tipo=24h : Tipo de lembrete (24h ou 1h)}';

    protected $description = 'Envia lembretes de eventos (24h ou 1h antes)';

    public function handle(): int
    {
        $tipo = $this->option('tipo');

        if (!in_array($tipo, ['24h', '1h'])) {
            $this->error('Tipo inválido. Usa "24h" ou "1h".');
            return self::FAILURE;
        }

        $this->info("📧 A enviar lembretes de eventos ({$tipo})...");

        // Determinar intervalo
        if ($tipo === '24h') {
            $inicio = now()->addHours(23);
            $fim    = now()->addHours(25);
        } else {
            $inicio = now()->addMinutes(50);
            $fim    = now()->addMinutes(70);
        }

        $eventos = Evento::with(['inscricoes.egresso'])
            ->whereBetween('data_inicio', [$inicio, $fim])
            ->where('is_active', true)
            ->get();

        if ($eventos->isEmpty()) {
            $this->info('ℹ️  Nenhum evento encontrado no intervalo.');
            return self::SUCCESS;
        }

        $total = 0;

        foreach ($eventos as $evento) {
            $this->line("🎪 Evento: {$evento->titulo} ({$evento->inscricoes->count()} inscritos)");

            foreach ($evento->inscricoes as $inscricao) {
                $egresso = $inscricao->egresso;

                if (!$egresso) continue;

                try {
                    // 1. Enviar email
                    if ($egresso->email) {
                        Mail::to($egresso->email)
                            ->send(new LembreteEventoMail($inscricao, "lembrete_{$tipo}"));

                        Log::info("✅ Lembrete {$tipo} enviado para: {$egresso->email}");
                    }

                    // 2. Notificação no sino
                    NotificacaoService::lembreteEvento($inscricao);

                    $total++;

                } catch (\Throwable $e) {
                    Log::error("❌ Erro ao enviar lembrete para inscrição {$inscricao->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("✅ {$total} lembretes enviados com sucesso.");
        return self::SUCCESS;
    }
}