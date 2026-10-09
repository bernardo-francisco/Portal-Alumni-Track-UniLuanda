<?php

use App\Providers\AppServiceProvider;
use App\Providers\BroadcastServiceProvider;
use Laravel\Breeze\BreezeServiceProvider;

return [
    AppServiceProvider::class,
    BroadcastServiceProvider::class,
    BreezeServiceProvider::class,
];