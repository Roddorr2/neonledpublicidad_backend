<?php

return [
    // Límite diario por flujo (campañas o modal flow se usan por separado)
    'daily_limit' => env('WHATSAPP_DAILY_LIMIT', 50),

    // Zona horaria usada para programar envíos
    'timezone' => env('WHATSAPP_TIMEZONE', 'America/Lima'),

    // Ventana de envío por día (hora local)
    'window_start' => env('WHATSAPP_WINDOW_START', '08:00'),
    'window_end' => env('WHATSAPP_WINDOW_END', '23:00'),

    // Chunk settings
    'chunk_size' => env('WHATSAPP_CHUNK_SIZE', 20),
    'chunk_spacing_minutes' => env('WHATSAPP_CHUNK_SPACING_MINUTES', 2),
];
