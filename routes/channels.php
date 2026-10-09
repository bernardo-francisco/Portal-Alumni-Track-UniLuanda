<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| CANAL PRIVADO DO UTILIZADOR
|--------------------------------------------------------------------------
*/
Broadcast::channel(
    'user.{userId}',
    function ($user, $userId) {
        if (!$user) return false;

        $egresso = $user->egresso;
        if (!$egresso) return false;

        return (int) $egresso->id === (int) $userId;
    },
    ['guards' => ['web']]
);

/*
|--------------------------------------------------------------------------
| CANAL PRIVADO DA SALA WEBRTC
|--------------------------------------------------------------------------
*/
Broadcast::channel(
    'video-call.{roomId}',
    function ($user, $roomId) {
        if (!$user) return false;
        if (!$user->egresso) return false;
        return true;
    },
    ['guards' => ['web']]
);