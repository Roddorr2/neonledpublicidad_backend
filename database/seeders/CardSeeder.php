<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            [
                'titulo'             => 'Tu Bar, en la Mira',
                'descripcion'        => 'Haz que el nombre de tu bar destaque con letras neón LED. Crea un ambiente único que atraiga miradas y clientes. ¡Ilumina tu identidad! 🍹🔆',
                'public_image'       => '/blog/letra_neonled.png',
                'id_plantilla'       => 3,
                'id_blog'            => 1,
                'id_empleado'        => 2,
                'estado_publicacion'   => true,
            ],
        ];

        DB::table('cards')->insert($cards);
    }
}
