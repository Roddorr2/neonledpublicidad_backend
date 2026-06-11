<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarjetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tarjetas = [
            [
                'titulo'       => 'El Factor Sorpresa y Distinción',
                'descripcion'  => 'Las letras de neón LED permiten personalizar la imagen de tu local, haciendo que el nombre de tu bar sea visible desde lejos. Un diseño llamativo puede convertirse en un sello distintivo y en un punto de referencia para los clientes.',
                'id_blog_body' => 1,
            ],
            [
                'titulo'       => 'Ambiente y Experiencia Visual',
                'descripcion'  => 'La iluminación juega un papel crucial en la atmósfera de un bar. Los colores vibrantes y cálidos del neón LED pueden transformar un espacio ordinario en un entorno acogedor e instagrameable.',
                'id_blog_body' => 1,
            ],
            [
                'titulo'       => 'Eficiencia Energética y Durabilidad',
                'descripcion'  => 'A diferencia del neón tradicional, las luces LED son más eficientes, consumen menos energía y tienen una vida útil más prolongada.',
                'id_blog_body' => 1,
            ],
            [
                'titulo'       => 'Marketing y Atracción de Clientes',
                'descripcion'  => 'Un letrero de neón LED bien diseñado es una herramienta de marketing poderosa, capaz de captar la atención y aumentar la visibilidad de tu local.',
                'id_blog_body' => 1,
            ],
        ];
        DB::table('tarjetas')->insert($tarjetas);
    }
}
