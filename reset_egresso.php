<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Resetar o egresso
$e = \App\Models\Egresso::where('numero_processo', '99999999')->first();

if ($e) {
    $e->update([
        'claimed_at'         => null,
        'claimed_ip'         => null,
        'claimed_user_agent' => null,
        'status_validacao'   => 'pendente',
        'status'             => 'inactive',
        'verificado'         => false,
        'data_verificacao'   => null,
        'email'              => null,
    ]);

    if ($e->user) {
        $e->user->update([
            'email'             => '20211519@uniluanda.ao',
            'is_active'         => false,
            'email_verified_at' => null,
        ]);
    }

    echo "✅ Egresso resetado\n";
}

// Apagar OTPs
\App\Models\OtpCode::where('email', 'bernardovasvolima@gmail.com')->delete();

// Limpar rate limiter
cache()->flush();

echo "✅ Reset OK\n";