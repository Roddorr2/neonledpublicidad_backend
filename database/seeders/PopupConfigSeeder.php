<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PopupConfig;
use App\Models\Productos;

/**
 * PopupConfigSeeder
 *
 * Crea un popup_config por defecto para cada producto.
 * Los colores de servicio son distintos por producto para que
 * el marketing pueda identificarlos fácilmente al editar.
 *
 * Usa updateOrCreate para ser idempotente (se puede re-ejecutar sin duplicar).
 */
class PopupConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Configuraciones por defecto indexadas por nombre de producto
        // (el ID real lo resolvemos dinámicamente para no asumir orden fijo)
        $defaults = [
            'LETRAS DE ACRÍLICO' => [
                'service_color'   => '#1E3A5F',
                'service_color_2' => '#0F2340',
            ],
            'LETRAS DE ALUMINIO DORADAS 3D' => [
                'service_color'   => '#7C5E1E',
                'service_color_2' => '#4A3810',
            ],
            'LETRAS DE ALUMINIO PLATEADAS 3D' => [
                'service_color'   => '#3A4A5C',
                'service_color_2' => '#1F2D3D',
            ],
            'LETREROS LUMINOSOS' => [
                'service_color'   => '#1A3550',
                'service_color_2' => '#0D2035',
            ],
            'NEÓN LED' => [
                'service_color'   => '#1E1E6E',
                'service_color_2' => '#0D0D40',
            ],
            'IMPRESIÓN EN VINILO' => [
                'service_color'   => '#1E4030',
                'service_color_2' => '#0F2518',
            ],
            'MENÚ BOARD' => [
                'service_color'   => '#3D2010',
                'service_color_2' => '#1F1008',
            ],
            'LETRAS PINTADAS EN MDF' => [
                'service_color'   => '#2A1F10',
                'service_color_2' => '#150F08',
            ],
            'MONITORES DE PUBLICIDAD' => [
                'service_color'   => '#101030',
                'service_color_2' => '#080820',
            ],
            'PANTALLAS LED' => [
                'service_color'   => '#0A2040',
                'service_color_2' => '#051020',
            ],
            'HOLOGRÁFICO' => [
                'service_color'   => '#1A0A40',
                'service_color_2' => '#0D0520',
            ],
            'PIXEL LED' => [
                'service_color'   => '#0A1A30',
                'service_color_2' => '#050D18',
            ],
            'SILLAS LUMINOSAS' => [
                'service_color'   => '#3A1A50',
                'service_color_2' => '#1D0D28',
            ],
            'TECHOS LED' => [
                'service_color'   => '#0A3030',
                'service_color_2' => '#051818',
            ],
            'LETRAS DE NEÓN EN TUBOS DE VIDRIO' => [
                'service_color'   => '#1E1040',
                'service_color_2' => '#0F0820',
            ],
              
            'CAJAS LUMINOSAS' => [
                'service_color'   => '#1A3020',
                'service_color_2' => '#0D1810',
            ],
        ];

        // Valores base que comparten todos los productos
        $base = [
            'title_text'         => 'OBTÉN UNA COTIZACIÓN ¡GRATIS!',
            'title_color'        => '#FFFFFF',
            'button_text'        => 'HAZLO YA',
            'button_color'       => '#F97316',
            'gradient_direction' => 'to bottom',
            'trigger_time'       => 8,
            'left_opacity'       => 85,
            'right_opacity'      => 100,
            'mobile_opacity'     => 100,
        ];

        // Obtener todos los productos existentes en la BD
        $productos = Productos::all()->keyBy('nombre');

        foreach ($defaults as $nombreProducto => $colores) {
            $producto = $productos->get($nombreProducto);

            if (!$producto) {
                // El producto no existe todavía (BD vacía en este punto del seed)
                $this->command->warn("PopupConfigSeeder: producto '{$nombreProducto}' no encontrado, omitido.");
                continue;
            }

            PopupConfig::updateOrCreate(
                ['id_producto' => $producto->id_producto],
                array_merge($base, $colores)
            );
        }

        $this->command->info('PopupConfigSeeder: ' . count($defaults) . ' configuraciones procesadas.');
    }
}