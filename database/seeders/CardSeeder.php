<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardSeeder extends Seeder
{

    public function run(): void
    {
        // Borrar datos existentes para evitar conflictos y duplicados
        DB::table('cards')->delete();

        $cards = [
            // 1
            [
                'titulo' => 'Tu Bar, en la Mira',
                'descripcion' => 'Haz que el nombre de tu bar destaque con letras neón LED. Crea un ambiente único que atraiga miradas y clientes. ¡Ilumina tu identidad! 🍹🔆',
                'public_image' => '/blog/Bar_letras_neonled.webp',
                'id_plantilla' => 1,
                'id_blog' => 1,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 2
            [
                'titulo' => 'Ilumina tu Negocio con Estilo', 
                'descripcion' => 'Transforma tu local con letreros LED que capturan miradas y definen tu marca. Diseño moderno, instalación profesional y resultados inmediatos. ¡Brilla con luz propia! ✨🏢',
                'public_image' => '/blog/letrerosneon12.jpg',
                'id_plantilla' => 2,
                'id_blog' => 2, 
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 3
            [
                'titulo' => 'Letras Acrílicas: Modernidad Pura',
                'descripcion' => 'El acrílico ofrece versatilidad y elegancia. Ideal para lograr acabados impecables en tu imagen corporativa. ¡Destaca con estilo! 💎',
                'public_image' => '/blog/ACRILICO.png',
                'id_plantilla' => 3,
                'id_blog' => 3,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 4
            [
                'titulo' => 'El Toque Dorado de tu Marca',
                'descripcion' => 'Dale a tu negocio un estatus premium con letras doradas en 3D. Clientes que perciben valor, pagan valor. ¡Elegancia garantizada! 🌟',
                'public_image' => '/blog/letras_doradas2.jpg',
                'id_plantilla' => 1,
                'id_blog' => 4,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 5
            [
                'titulo' => 'Vinilos Decorativos y Pavonados',
                'descripcion' => 'Privacidad y marca en uno. Vinilos pavonados para oficinas y cortes decorativos para superficies. ¡Personaliza tu espacio! 🏢✨',
                'public_image' => '/blog/HOLOGRAFICO.png',
                'id_plantilla' => 2,
                'id_blog' => 5,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 6
            [
                'titulo' => 'Displays Digitales Interactivos',
                'descripcion' => 'Comunica más en menos tiempo. Pantallas dinámicas para menús, promociones y entretenimiento. ¡Actualiza tu contenido al instante! 📺📲',
                'public_image' => '/blog/DISPLAY.png',
                'id_plantilla' => 3,
                'id_blog' => 6,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 7
            [
                'titulo' => 'Cajas de Luz (Lightboxes)',
                'descripcion' => 'Haz que tus promociones brillen. Lightboxes ultradelgados y de alto impacto para retail y restaurantes. ¡Ilumina tu oferta! 🍔�',
                'public_image' => '/blog/motoled.jpg',
                'id_plantilla' => 1,
                'id_blog' => 7,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 8
            [
                'titulo' => 'Letreros Luminosos de Alto Impacto',
                'descripcion' => 'Que la noche no apague tu negocio. Sé visible 24/7 con letreros luminosos de alta durabilidad y bajo consumo. 🌙💡',
                'public_image' => '/blog/letrero_luminoso.png',
                'id_plantilla' => 2,
                'id_blog' => 8,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 9
            [
                'titulo' => 'Branding Corporativo Integral',
                'descripcion' => 'Desde el lobby hasta la fachada, unifica tu imagen. Soluciones integrales de rotulación e iluminación para empresas líderes. 🏢🤝',
                'public_image' => '/blog/branding_1.webp',
                'id_plantilla' => 3,
                'id_blog' => 9,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 10
            [
                'titulo' => 'Letras de Acero Inoxidable',
                'descripcion' => 'Elegancia y resistencia eterna. Letras corpóreas en acero para una imagen corporativa sólida y premium. �️✨',
                'public_image' => '/blog/letra_neonled.png',
                'id_plantilla' => 1,
                'id_blog' => 10,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 11
            [
                'titulo' => 'Señalética Corporativa',
                'descripcion' => 'Guía y organiza con estilo. Sistemas de señalización interna y externa para empresas e instituciones. ➡️�',
                'public_image' => '/blog/footer2_plantilla2.webp',
                'id_plantilla' => 2,
                'id_blog' => 11,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 12
            [
                'titulo' => 'Pantallas LED Publicitarias',
                'descripcion' => 'Publicidad dinámica a gran escala. Pantallas LED gigantes para exteriores que captan todas las miradas. 🎥�️',
                'public_image' => '/blog/led123.jpg',
                'id_plantilla' => 3,
                'id_blog' => 12,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 13
            [
                'titulo' => 'Restaurantes Modernos y Chic',
                'descripcion' => 'Crea el ambiente perfecto para cenar. Iluminación que despierta los sentidos y hace que tus platos luzcan irresistibles. 🍽️🍷',
                'public_image' => '/blog/body2_titulo.webp',
                'id_plantilla' => 1,
                'id_blog' => 13,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 14
            [
                'titulo' => 'Cafeterías con Encanto',
                'descripcion' => 'Un espacio acogedor para el mejor café. Atrae a clientes que buscan un lugar cálido y con estilo para relajarse. ☕📖',
                'public_image' => '/blog/fondo_blog.png',
                'id_plantilla' => 2,
                'id_blog' => 14,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 15
            [
                'titulo' => 'Fachadas que Impactan',
                'descripcion' => 'Tu edificio, un icono urbano. Iluminación arquitectónica que resalta la belleza de tu infraestructura por la noche. 🌆🏗️',
                'public_image' => '/blog/letrerosneon1234.jpg',
                'id_plantilla' => 3,
                'id_blog' => 15,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ],
            // 16
            [
                'titulo' => 'Oficinas Creativas e Inspiradoras',
                'descripcion' => 'Diseña espacios donde fluyan las ideas. Iluminación funcional y decorativa para potenciar la creatividad de tu equipo. 💡🧠',
                'public_image' => '/blog/branding_2.webp',
                'id_plantilla' => 1,
                'id_blog' => 16,
                'id_empleado' => 2,
                'estado_publicacion' => 1,
            ]
        ];

        DB::table('cards')->insert($cards);
    }
}
