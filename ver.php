<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Egresso;
use App\Models\User;

echo "=====================================================" . PHP_EOL;
echo "  EGRESSOS + USERS (mais recentes primeiro)" . PHP_EOL;
echo "=====================================================" . PHP_EOL . PHP_EOL;

$egressos = Egresso::orderBy('created_at', 'desc')->take(10)->get();

foreach ($egressos as $e) {
    $user = $e->user;

    echo "[ID {$e->id}] {$e->nome_completo}" . PHP_EOL;
    echo "    Nº processo: {$e->numero_processo}" . PHP_EOL;
    echo "    Email: " . ($e->email ?? 'null') . PHP_EOL;
    echo "    user_id: " . ($e->user_id ?? 'null') . PHP_EOL;
    echo "    user email: " . ($user->email ?? 'null') . PHP_EOL;
    echo "    Status: {$e->status} | Validação: {$e->status_validacao}" . PHP_EOL;
    echo "    Criado em: " . $e->created_at->format('d/m/Y H:i:s') . PHP_EOL;
    echo PHP_EOL;
}

echo "=====================================================" . PHP_EOL;
echo "  USERS (mais recentes)" . PHP_EOL;
echo "=====================================================" . PHP_EOL . PHP_EOL;

$users = User::orderBy('created_at', 'desc')->take(10)->get();

foreach ($users as $u) {
    echo "[ID {$u->id}] {$u->name}" . PHP_EOL;
    echo "    Email: {$u->email}" . PHP_EOL;
    echo "    Tipo: " . ($u->tipo ?? 'null') . " | Role: " . ($u->role ?? 'null') . PHP_EOL;
    echo "    is_active: " . ($u->is_active ? 'true' : 'false') . PHP_EOL;
    echo "    Criado em: " . $u->created_at->format('d/m/Y H:i:s') . PHP_EOL;
    echo PHP_EOL;
}