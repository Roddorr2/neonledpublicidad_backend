<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'url'    => env('WHATSAPP_API_URL'),
        'apikey' => env('WHATSAPP_SERVICE_API_KEY'),
        // Alias for compatibility with docs/legacy code that may reference api_key
        'api_key'               => env('WHATSAPP_SERVICE_API_KEY'),
        'rate_limit_per_minute' => env('WHATSAPP_RATE_LIMIT', 30),
    ],
    // Reseñas de Google Business (Places API - Place Details) para el
    // carrusel de testimonios en /nosotros. Requiere Place ID + API Key
    // con "Places API" habilitada en Google Cloud Console.
   // 'google_places' => [
       // 'api_key'      => env('GOOGLE_PLACES_API_KEY'),
        //'place_id'     => env('GOOGLE_PLACES_ID'),
        // Segundos que se cachea la respuesta antes de volver a llamar a Google
        //'cache_ttl'    => env('GOOGLE_PLACES_CACHE_TTL', 86400), // 24h por defecto
   // ],

];
