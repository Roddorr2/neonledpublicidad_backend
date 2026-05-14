<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommendTarjetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commend_tarjetas = [
            [
                'titulo' => 'Consejos para Elegir el Letrero Perfecto',
                'texto1' => 'Opta por colores que reflejen la personalidad de tu bar.',
                'texto2' => 'Elige un diseño legible y atractivo.',
                'texto3' => 'Considera el lugar de instalación para maximizar su impacto.',
            ],
        ];

        DB::table('commend_tarjetas')->insert($commend_tarjetas);
    }
}
