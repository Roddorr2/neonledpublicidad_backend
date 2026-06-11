<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Diseño Web y Desarrollo Web', 'descripcion' => 'Ofrecemos diseño y desarrollo web para ayudar a tu negocio a destacar en línea. Creamos sitios atractivos y funcionales que reflejan tu marca y mejoran la experiencia del usuario.'],
            ['nombre' => 'Gestión de Redes Sociales', 'descripcion' => 'Te ayudamos a construir una voz única para tu marca, interactúa de manera auténtica con tu audiencia y transforma tus seguidores en clientes fieles.'],
            ['nombre' => 'Marketing y Gestión Digital', 'descripcion' => 'Creamos campañas que no solo se ven, sino que se sienten. Potenciamos tu presencia online con tácticas personalizadas, llevándote al siguiente nivel con resultados medibles y un impacto real. Tu éxito digital comienza aquí.'],
            ['nombre' => 'Branding y Diseño', 'descripcion' => 'Creamos marcas que hablan, emocionan y conectan. Desde una identidad visual memorable hasta mensajes que resuenan profundamente, hacemos que tu empresa sea tan única como inolvidable.'],
        ];

        DB::table('servicios')->insert($servicios);
    }
}
