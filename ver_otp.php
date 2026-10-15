<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\OtpCode;

echo "=== TODOS OS OTPs (últimos 10) ===" . PHP_EOL . PHP_EOL;

$otps = OtpCode::orderBy('created_at', 'desc')->take(10)->get();

foreach ($otps as $o) {
    echo "[ID {$o->id}]" . PHP_EOL;
    echo "  Email: {$o->email}" . PHP_EOL;
    echo "  Code: {$o->code}" . PHP_EOL;
    echo "  Purpose: {$o->purpose}" . PHP_EOL;
    echo "  used_at: " . ($o->used_at ?? 'null') . PHP_EOL;
    echo "  expires_at: {$o->expires_at}" . PHP_EOL;
    echo "  Criado em: " . $o->created_at->format('d/m/Y H:i:s') . PHP_EOL;
    echo PHP_EOL;
}

echo "=== PROCURAR CÓDIGO '001525' ===" . PHP_EOL . PHP_EOL;

$encontrado = OtpCode::where('code', '001525')->first();

if ($encontrado) {
    echo "✅ Encontrado na BD:" . PHP_EOL;
    echo "  ID: {$encontrado->id}" . PHP_EOL;
    echo "  Email: {$encontrado->email}" . PHP_EOL;
    echo "  used_at: " . ($encontrado->used_at ?? 'null') . PHP_EOL;
    echo "  expires_at: {$encontrado->expires_at}" . PHP_EOL;
    echo "  Expirado: " . ($encontrado->expires_at->isPast() ? 'SIM ❌' : 'NÃO ✅') . PHP_EOL;
} else {
    echo "❌ Código '001525' NÃO está na BD!" . PHP_EOL;
}

echo PHP_EOL . "=== PROCURAR OTPs ATIVOS PARA bernardovascolimafrancisco03@gmail.com ===" . PHP_EOL . PHP_EOL;

$ativos = OtpCode::where('email', 'bernardovascolimafrancisco03@gmail.com')
    ->where('purpose', 'reclamar_conta')
    ->whereNull('used_at')
    ->where('expires_at', '>', now())
    ->get();

if ($ativos->isEmpty()) {
    echo "❌ Nenhum OTP ativo encontrado." . PHP_EOL;
} else {
    foreach ($ativos as $a) {
        echo "✅ [ID {$a->id}] Code: {$a->code} | Expira: {$a->expires_at}" . PHP_EOL;
    }
}