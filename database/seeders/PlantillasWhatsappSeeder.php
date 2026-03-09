<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantillasWhatsappSeeder extends Seeder
{
    public function run()
    {
        $plantillas = [];
        for ($prod = 1; $prod <= 4; $prod++) {
            for ($n = 1; $n <= 3; $n++) {
                $plantillas[] = [
                    'id_producto' => $prod,
                    'numero_plantilla' => $n,
                    'nombre' => null,
                    'mensaje' => "Hola {nombre}, este es un mensaje de prueba (producto {$prod} - plantilla {$n})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('plantillas_whatsapp')->insert($plantillas);
    }
}
