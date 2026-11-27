<?php
// reset_utilizador.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Cache;

// 1. Define o email que queres apagar (ou usa o primeiro argumento do terminal)
$email = $argv[1] ?? 'bernardovascolimafrancisco03@gmail.com';

echo "🔄 A tentar limpar o utilizador: {$email}...\n";

// 2. Procura o utilizador
$user = User::where('email', $email)->first();

if ($user) {
    // Apaga o perfil de egresso
    if ($user->egresso) {
        $user->egresso()->delete();
        echo "🗑️ Perfil de Egresso apagado.\n";
    }
    
    // Apaga a conta de login
    $user->delete();
    echo "✅ Conta de utilizador apagada.\n";
} else {
    echo "⚠️ Nenhum utilizador encontrado com este email.\n";
}

// 3. Limpa a cache e os limites de tentativas (Rate Limiter)
Cache::flush();
echo "🧹 Cache e limites de tentativas (Rate Limiter) limpos.\n";

// 4. Limpa os códigos OTP (se a tabela existir no teu sistema)
if (class_exists('\App\Models\OtpCode')) {
    \App\Models\OtpCode::where('email', $email)->delete();
    echo "🔑 Códigos OTP apagados.\n";
}

echo "\n🎉 Processo concluído! Já podes voltar a registar no browser.\n";