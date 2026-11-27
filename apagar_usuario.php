<?php
// apagar_usuario.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresso;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Schema;

// ============================================================
// CONFIGURAÇÃO: Define aqui o número de processo e o email
// ============================================================
$numeroProcesso = $argv[1] ?? '20210103'; // Nº do Gomes por defeito
$email          = $argv[2] ?? 'bernardovascolimafrancisco03@gmail.com';

echo "🔄 A apagar o utilizador do Nº de Processo: {$numeroProcesso}...\n";

// 1. Desativar verificação de chaves estrangeiras
Schema::disableForeignKeyConstraints();

try {
    // 2. Procurar o Egresso
    $egresso = Egresso::where('numero_processo', $numeroProcesso)->first();

    if ($egresso) {
        echo "🔍 Egresso encontrado: {$egresso->nome_completo}\n";
        
        // Apagar dependências (localizações, profissionais) se existirem
        if (method_exists($egresso, 'localizacoes')) {
            $egresso->localizacoes()->delete();
            echo "🗑️ Localizações apagadas.\n";
        }
        if (method_exists($egresso, 'profissionais')) {
            $egresso->profissionais()->delete();
            echo "🗑️ Profissionais apagados.\n";
        }

        $userId = $egresso->user_id;

        // Apagar o Egresso
        $egresso->delete();
        echo "🗑️ Perfil de Egresso apagado.\n";

        // Apagar a conta de login (User)
        if ($userId) {
            User::where('id', $userId)->delete();
            echo "🗑️ Conta de Utilizador apagada.\n";
        }
    } else {
        echo "⚠️ Nenhum egresso encontrado com o número de processo {$numeroProcesso}.\n";
    }

    // 3. Apagar os OTPs associados ao email
    if ($email) {
        OtpCode::where('email', $email)->delete();
        echo "🔑 Códigos OTP apagados.\n";
    }

} catch (\Exception $e) {
    echo "❌ Erro ao apagar: " . $e->getMessage() . "\n";
} finally {
    // 4. Reativar verificação de chaves estrangeiras
    Schema::enableForeignKeyConstraints();
}

// 5. Limpar a cache do sistema
\Illuminate\Support\Facades\Cache::flush();
echo "🧹 Cache limpa.\n";

echo "\n🎉 Processo concluído! O utilizador foi apagado.\n";
echo "Já podes ir ao browser e criar a conta novamente do zero.\n";