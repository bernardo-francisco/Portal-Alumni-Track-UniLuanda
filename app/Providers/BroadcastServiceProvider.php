<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ Middleware 'web' — para ter acesso à sessão no /broadcasting/auth
        Broadcast::routes(['middleware' => ['web']]);

        require base_path('routes/channels.php');
    }
}