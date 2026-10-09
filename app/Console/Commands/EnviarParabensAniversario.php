<?php

namespace App\Console\Commands;

use App\Mail\ParabensAniversarioMail;
use App\Models\Egresso;
use App\Services\NotificacaoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarParabensAniversario extends Command
{
    protected $signature = 'egressos:parabens';

    protected $description = 'Envia mensagens de parabéns aos egressos aniversariantes';

    public function handle(): int
    {
        $this->info('🎂 A enviar parabéns aos aniversariantes...');

        $hoje = now();

        // Egressos que fazem anos hoje
        $egressos = Egresso::whereMonth('data_nascimento', $hoje->month)
            ->whereDay('data_nascimento', $hoje->day)
            ->where('status', 'active')
            ->get();

        if ($egressos->isEmpty()) {
            $this->info('ℹ️  Nenhum aniversariante hoje.');
            return self::SUCCESS;
        }

        $total = 0;

        foreach ($egressos as $egresso) {
            try {
                // 1. Email
                if ($egresso->email) {
                    Mail::to($egresso->email)
                        ->send(new ParabensAniversarioMail($egresso));

                    Log::info("✅ Parabéns de aniversário enviados para: {$egresso->email}");
                }

                // 2. Notificação no sino
                NotificacaoService::criarParaEgresso(
                    $egresso->id,
                    "🎂 Feliz Aniversário!",
                    "A equipa da UniLuanda deseja-lhe um excelente dia!",
                    'sistema'
                );

                $total++;

            } catch (\Throwable $e) {
                Log::error("❌ Erro ao enviar parabéns para {$egresso->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ {$total} mensagens de parabéns enviadas.");
        return self::SUCCESS;
    }
}