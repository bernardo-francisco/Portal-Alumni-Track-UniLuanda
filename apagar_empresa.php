<?php
// apagar_empresa.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Empresa;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Schema;

$email = $argv[1] ?? 'bernardovascolimafrancisco03@gmail.com';

echo "🔄 A apagar a empresa e utilizador: {$email}...\n";

Schema::disableForeignKeyConstraints();

try {
    $user = User::where('email', $email)->first();

    if ($user) {
        echo "🔍 Utilizador encontrado: {$user->name}\n";
        
        if ($user->empresa) {
            $empresa = $user->empresa;
            
            // Apagar dependências da empresa (oportunidades, candidaturas)
            if (method_exists($empresa, 'oportunidades')) {
                // Apagar candidaturas relacionadas primeiro
                foreach ($empresa->oportunidades as $op) {
                    $op->candidaturas()->delete();
                }
                $empresa->oportunidades()->delete();
                echo "🗑️ Oportunidades e candidaturas apagadas.\n";
            }
            
            $empresa->delete();
            echo "🗑️ Perfil da Empresa apagado.\n";
        }

        $user->delete();
        echo "🗑️ Conta de Utilizador apagada.\n";
    } else {
        echo "⚠️ Nenhum utilizador encontrado com o email {$email}.\n";
    }

    // Apagar OTPs
    if (class_exists(OtpCode::class)) {
        OtpCode::where('email', $email)->delete();
        echo "🔑 Códigos OTP apagados.\n";
    }

} catch (\Exception $e) {
    echo "❌ Erro ao apagar: " . $e->getMessage() . "\n";
} finally {
    Schema::enableForeignKeyConstraints();
}

\Illuminate\Support\Facades\Cache::flush();
echo "🧹 Cache limpa.\n";

echo "\n🎉 Processo concluído! Já podes registar a empresa novamente.\n";