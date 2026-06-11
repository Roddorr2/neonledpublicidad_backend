<?php

// =========================================
// FUNCIONES RELACIONADAS CON WHATSAPP
// =========================================

if (! function_exists('formatearTelefonoWhatsApp')) {
    /**
     * Formatea un número de teléfono para WhatsApp.
     * Añade el código de país 51 (Perú) si no lo tiene.
     * Elimina espacios, guiones y otros caracteres no numéricos.
     *
     * @param  string $telefono El número de teléfono a formatear.
     * @return string El número formateado con código de país.
     */
    function formatearTelefonoWhatsApp($telefono)
    {
        if (empty($telefono)) {
            return '';
        }

        // Eliminar todos los caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        // Si ya empieza con 51, no añadir de nuevo
        if (substr($telefono, 0, 2) === '51') {
            return $telefono;
        }

        // Añadir código de país 51 (Perú)
        return '51' . $telefono;
    }
}

if (! function_exists('validarTelefonoPeruano')) {
    /**
     * Valida si un número de teléfono peruano es válido.
     * Los números móviles en Perú tienen 9 dígitos y empiezan con 9.
     * Acepta números con o sin código de país 51.
     *
     * @param  string $telefono El número de teléfono a validar (con o sin código de país).
     * @return bool   True si es válido, false en caso contrario.
     */
    function validarTelefonoPeruano($telefono)
    {
        // Eliminar el código de país si existe
        $telefono = preg_replace('/^51/', '', $telefono);

        // Eliminar caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);

        // Validar: debe tener 9 dígitos y empezar con 9
        return strlen($telefono) === 9 && substr($telefono, 0, 1) === '9';
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * Construye la URL completa para un endpoint de la API de WhatsApp.
     * Combina la URL base del servicio WhatsApp con la ruta especificada.
     *
     * @param  string $path Ruta del endpoint (ej: '/api/whatsapp/health').
     * @return string La URL completa del endpoint.
     */
    function whatsapp_url(string $path): string
    {
        return rtrim(config('services.whatsapp.url'), '/') . '/' . ltrim($path, '/');
    }
}

if (! function_exists('whatsapp_api_key')) {
    /**
     * Obtiene la API key del servicio WhatsApp desde la configuración.
     * Retorna una cadena vacía si no está configurada.
     *
     * @return string La API key del servicio WhatsApp.
     */
    function whatsapp_api_key(): string
    {
        return config('services.whatsapp.apikey') ?? '';
    }
}

// =========================================
// FUNCIONES PARA MANEJO DE ARRAYS
// =========================================

if (! function_exists('chunksArray')) {
    /**
     * Divide un array en chunks (lotes) del tamaño especificado.
     * Útil para procesar arrays grandes en lotes.
     *
     * @param  array $array El array a dividir.
     * @param  int   $size  El tamaño de cada chunk (por defecto 50).
     * @return array Un array de arrays (chunks).
     */
    function chunksArray($array, $size = 50)
    {
        // return array_chunk($array, $size);
        if ($size <= 0) {
            return [];
        }

        return array_chunk($array, $size);
    }
}
