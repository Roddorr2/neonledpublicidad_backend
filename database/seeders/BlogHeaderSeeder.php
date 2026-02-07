<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blog_heads = [
            // 1
            [
                'titulo' => 'Tu Bar, en la Mira',
                'texto_frase' => 'Ilumina tu espacio, cautiva a tus clientes',
                'texto_descripcion' => 'Transforma la atmósfera de tu bar con luces neón LED vibrantes y llenas de estilo.',
                'public_image'=>'/blog/blog-1.webp'
            ],
            // 2
            [
                'titulo' => 'Ilumina tu Negocio con Estilo',
                'texto_frase' => 'Brilla con luz propia',
                'texto_descripcion' => 'Letreros LED que capturan miradas y definen tu identidad corporativa al instante.',
                'public_image'=>'/blog/blog-2.webp'
            ],
            // 3
            [
                'titulo' => 'Letras Acrílicas: Modernidad Pura',
                'texto_frase' => 'Transparencia y color',
                'texto_descripcion' => 'La mejor opción para una imagen corporativa limpia y profesional que destaca en cualquier entorno.',
                'public_image'=>'/blog/blog-3.webp'
            ],
            // 4
            [
                'titulo' => 'El Toque Dorado de tu Marca',
                'texto_frase' => 'Lujo y distinción',
                'texto_descripcion' => 'Eleva el estatus de tu negocio con letras doradas en 3D que comunican elegancia y solidez.',
                'public_image'=>'/blog/Blog4_header.webp'
            ],
            // 5
            [
                'titulo' => 'Vinilos Decorativos y Pavonados',
                'texto_frase' => 'Privacidad y Branding',
                'texto_descripcion' => 'Transforma cristales y paredes en herramientas de comunicación visual con acabados profesionales.',
                'public_image'=>'/blog/blog-5.webp'
            ],
            // 6
            [
                'titulo' => 'Displays Digitales Interactivos',
                'texto_frase' => 'Comunicación en movimiento',
                'texto_descripcion' => 'Mensajes dinámicos que informan y entretienen a tu audiencia mientras esperan.',
                'public_image'=>'/blog/blog-6.webp'
            ],
            // 7
            [
                'titulo' => 'Cajas de Luz (Lightboxes)',
                'texto_frase' => 'Impacto garantizado',
                'texto_descripcion' => 'La solución perfecta para destacar promociones y menús con una iluminación uniforme y vibrante.',
                'public_image'=>'/blog/blog-7.webp'
            ],
            // 8
            [
                'titulo' => 'Letreros Luminosos de Alto Impacto',
                'texto_frase' => 'Visibilidad 24/7',
                'texto_descripcion' => 'Asegura que tu negocio destaque tanto de día como de noche con tecnología de punta.',
                'public_image'=>'/blog/blog-8.webp'
            ],
            // 9
            [
                'titulo' => 'Branding Corporativo Integral',
                'texto_frase' => 'Identidad visual coherente',
                'texto_descripcion' => 'Unifica tu imagen de marca en todos tus espacios físicos con soluciones a medida.',
                'public_image'=>'/blog/blog-9.webp'
            ],
            // 10
            [
                'titulo' => 'Letras de Acero Inoxidable',
                'texto_frase' => 'Solidez y Elegancia',
                'texto_descripcion' => 'Transmite confianza y durabilidad con letras corpóreas de acero, ideales para fachadas corporativas.',
                'public_image'=>'/blog/blog-10.webp'
            ],
            // 11
            [
                'titulo' => 'Señalética Corporativa',
                'texto_frase' => 'Orden y Claridad',
                'texto_descripcion' => 'Guía a tus clientes y colaboradores con sistemas de señalización funcionales y estéticos.',
                'public_image'=>'/blog/blog-11.webp'
            ],
            // 12
            [
                'titulo' => 'Pantallas LED Publicitarias',
                'texto_frase' => 'Publicidad de Alto Impacto',
                'texto_descripcion' => 'Atrae todas las miradas con contenido dinámico en pantallas de gran formato y alta resolución.',
                'public_image'=>'/blog/blog-12.webp'
            ],
            // 13
            [
                'titulo' => 'Restaurantes Modernos y Chic',
                'texto_frase' => 'Experiencias gastronómicas',
                'texto_descripcion' => 'Diseña una atmósfera acogedora que invite a los comensales a quedarse más tiempo.',
                'public_image'=>'/blog/blog-13.webp'
            ],
            // 14
            [
                'titulo' => 'Cafeterías con Encanto',
                'texto_frase' => 'Aroma y luz',
                'texto_descripcion' => 'El complemento perfecto para un buen café es un ambiente cálido y bien iluminado.',
                'public_image'=>'/blog/blog-14.webp'
            ],
            // 15
            [
                'titulo' => 'Fachadas que Impactan',
                'texto_frase' => 'Arquitectura de luz',
                'texto_descripcion' => 'Convierte la fachada de tu edificio en un hito urbano nocturno imposible de ignorar.',
                'public_image'=>'/blog/blog-15.webp'
            ],
            // 16
            [
                'titulo' => 'Oficinas Creativas e Inspiradoras',
                'texto_frase' => 'Productividad brillante',
                'texto_descripcion' => 'Fomenta la creatividad y el bienestar de tu equipo con un diseño de iluminación moderno.',
                'public_image'=>'/blog/blog-16.webp'
            ]
        ];

        DB::table('blog_heads')->insert($blog_heads);
    }
}
