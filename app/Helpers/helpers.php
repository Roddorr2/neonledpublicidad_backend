<?php

if (!function_exists('formatearTelefonoWhatsApp')) {
    /**
     * Formatea un número de teléfono para WhatsApp
     * Añade el código de país 51 si no lo tiene
     * Elimina espacios, guiones y otros caracteres
     * @param string $telefono
     * @return string
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

if (!function_exists('validarTelefonoPeruano')) {
    /**
     * Valida si un número de teléfono peruano es válido
     * Los números móviles en Perú tienen 9 dígitos y empiezan con 9
     * @param string $telefono (sin código de país)
     * @return bool
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

if (!function_exists('chunksArray')) {
    /**
     * Divide un array en chunks (lotes) del tamaño especificado
     * @param array $array
     * @param int $size
     * @return array
     */
    function chunksArray($array, $size = 50)
    {
        return array_chunk($array, $size);
    }
}
