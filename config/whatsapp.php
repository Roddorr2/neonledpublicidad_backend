<?php

return [
    // Límite diario por flujo (campañas o modal flow se usan por separado)
    'daily_limit' => env('WHATSAPP_DAILY_LIMIT', 50),
    // Límite diario por flujo (campañas o modal flow se usan por separado)
    'daily_limit' => env('WHATSAPP_DAILY_LIMIT', 50),

    // Zona horaria usada para programar envíos
    'timezone' => env('WHATSAPP_TIMEZONE', 'America/Lima'),

    // Ventana de envío por día (hora local)
    'window_start' => env('WHATSAPP_WINDOW_START', '08:00'),
    'window_end' => env('WHATSAPP_WINDOW_END', '23:00'),

    // Chunk settings
    'chunk_size' => env('WHATSAPP_CHUNK_SIZE', 20),
    // Preferred: spacing in seconds for fine-grained testing. If not set,
    // legacy `WHATSAPP_CHUNK_SPACING_MINUTES` will be used (minutes * 60).
    'chunk_spacing_seconds' => env('WHATSAPP_CHUNK_SPACING_SECONDS', null),
    'chunk_spacing_minutes' => env('WHATSAPP_CHUNK_SPACING_MINUTES', 2),

    // Stale chunk detection: seconds before marking a processing/sent chunk as stale (requires recovery)
    'stale_processing_seconds' => env('WHATSAPP_STALE_PROCESSING_SECONDS', 120),

    // Maximum recovery attempts per chunk before marking as partial (incomplete)
    'max_recovery_attempts' => env('WHATSAPP_MAX_RECOVERY_ATTEMPTS', 3),
];
