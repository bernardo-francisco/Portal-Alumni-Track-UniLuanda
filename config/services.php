<?php

return [
    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend'   => ['key' => env('RESEND_API_KEY')],
    'ses'      => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Google Maps
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    // ✅ PeerServer
    'peer' => [
        'host'   => env('PEER_HOST', 'localhost'),
        'port'   => (int) env('PEER_PORT', 9000),
        'path'   => env('PEER_PATH', '/myapp'),
        'secure' => (bool) env('PEER_SECURE', false),
    ],
];