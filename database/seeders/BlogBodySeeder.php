<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogBodySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blog_bodies = [
            // 1
            [
                'id_commend_tarjeta' => 1,
                'titulo' => 'Atrae Miradas, Gana Clientes',
                'descripcion' => 'La iluminación es el alma de un bar. No se trata solo de ver, sino de sentir. Un letrero neón LED personalizado no es un gasto, es una inversión directa en la identidad de tu marca.',
                'public_image1'=>'/blog/Bar_letras_neonled.webp',
                'public_image2'=>'/blog/letra_neonled.png',
                'public_image3'=>'/blog/blog-1.webp',
            ],
            // 2
            [
                'id_commend_tarjeta' => 2,
                'titulo' => 'Tu Marca en Luz',
                'descripcion' => 'En un mercado saturado, la visibilidad es poder. Los letreros LED ofrecen una versatilidad inigualable, permitiendo cambiar colores y efectos para mantener tu fachada siempre fresca y atractiva.',
                'public_image1'=>'/blog/blog-1.webp',
                'public_image2'=>'/blog/blog-2.webp',
                'public_image3'=>'/blog/letrerosneon12.jpg',
            ],
            // 3
            [
                'id_commend_tarjeta' => 3,
                'titulo' => 'Elegancia Transparente',
                'descripcion' => 'El acrílico combina resistencia y estética premium. Ya sea iluminado o retroiluminado, este material proyecta una imagen de profesionalismo y atención al detalle que tus clientes valorarán.',
                'public_image1'=>'/blog/ACRILICO.png',
                'public_image2'=>'/blog/fondo_plantilla1.png',
                'public_image3'=>'/blog/blog-3.webp',
            ],
            // 4
            [
                'id_commend_tarjeta' => 4,
                'titulo' => 'El Estándar de Oro',
                'descripcion' => 'El dorado siempre ha sido símbolo de éxito. Nuestras letras volumétricas con acabado dorado transforman cualquier recepción o fachada en una declaración de principios: aquí hay calidad.',
                'public_image1'=>'/blog/letras_doradas.png',
                'public_image2'=>'/blog/letras_doradas2.jpg',
                'public_image3'=>'/blog/blog-4.webp',
            ],
            // 5
            [
                'id_commend_tarjeta' => 5,
                'titulo' => 'Versatilidad en Vinilo',
                'descripcion' => 'Desde pavonados para privacidad en oficinas hasta vinilos de corte con tu logotipo. Ofrecemos soluciones adhesivas de alta durabilidad que refuerzan tu identidad corporativa en cualquier superficie lisa.',
                'public_image1'=>'/blog/HOLOGRAFICO.png',
                'public_image2'=>'/blog/DISPLAY.png',
                'public_image3'=>'/blog/blog-5.webp',
            ],
            // 6
            [
                'id_commend_tarjeta' => 6,
                'titulo' => 'Contenido que Conecta',
                'descripcion' => 'Olvida la cartelería impresa que caduca. Los displays digitales te permiten actualizar promociones, menús y anuncios en tiempo real, maximizando cada oportunidad de venta.',
                'public_image1'=>'/blog/DISPLAY.png',
                'public_image2'=>'/blog/fondo_looking_diseñoweb.webp',
                'public_image3'=>'/blog/blog-6.webp',
            ],
            // 7
            [
                'id_commend_tarjeta' => 7,
                'titulo' => 'Luz que Vende',
                'descripcion' => 'Las cajas de luz (Lightboxes) son esenciales para retail y gastronomía. Su iluminación retroiluminada hace que las imágenes y textos sean legibles y atractivos, aumentando el deseo de compra.',
                'public_image1'=>'/blog/motoled.jpg',
                'public_image2'=>'/blog/led123.jpg',
                'public_image3'=>'/blog/blog-7.webp',
            ],
            // 8
            [
                'id_commend_tarjeta' => 8,
                'titulo' => 'Luz que Guía',
                'descripcion' => 'Un negocio oscuro es un negocio cerrado. Los letreros luminosos actúan como un faro para tus clientes, aumentando el tráfico y la seguridad de tu local durante la noche.',
                'public_image1'=>'/blog/letrero_luminoso.png',
                'public_image2'=>'/blog/letrero_luminoso2.png',
                'public_image3'=>'/blog/blog-8.webp',
            ],
            // 9
            [
                'id_commend_tarjeta' => 9,
                'titulo' => 'Coherencia Visual',
                'descripcion' => 'Tu marca debe hablar el mismo idioma en todas partes. Ofrecemos soluciones integrales que alinean tu señalética interior y exterior para una experiencia de cliente fluida.',
                'public_image1'=>'/blog/branding_1.webp',
                'public_image2'=>'/blog/branding_2.webp',
                'public_image3'=>'/blog/blog-9.webp',
            ],
            // 10
            [
                'id_commend_tarjeta' => 10,
                'titulo' => 'Acero: Sinónimo de Calidad',
                'descripcion' => 'El acero inoxidable no solo es resistente a la intemperie, sino que proyecta una imagen de solidez y permanencia. Ideal para bufetes, clínicas y sedes corporativas que buscan sobriedad.',
                'public_image1'=>'/blog/blog-2.webp',
                'public_image2'=>'/blog/body2_galeria1.webp',
                'public_image3'=>'/blog/blog-10.webp',
            ],
            // 11
            [
                'id_commend_tarjeta' => 11,
                'titulo' => 'Wayfinding Efectivo',
                'descripcion' => 'Una buena señalética mejora la experiencia del usuario. Diseñamos directorios, tótems y señalización de seguridad que cumplen normativas y facilitan la navegación en tus instalaciones.',
                'public_image1'=>'/blog/blog-3.webp',
                'public_image2'=>'/blog/footer2_plantilla2.webp',
                'public_image3'=>'/blog/blog-11.webp',
            ],
            // 12
            [
                'id_commend_tarjeta' => 12,
                'titulo' => 'Impacto Visual Masivo',
                'descripcion' => 'Las pantallas LED son el futuro de la publicidad exterior. Capta la atención de peatones y conductores con contenido visual vibrante y de alta definición que no pasa desapercibido.',
                'public_image1'=>'/blog/blog-4.webp',
                'public_image2'=>'/blog/Blog4_header.webp',
                'public_image3'=>'/blog/blog-12.webp',
            ],
            // 13
            [
                'id_commend_tarjeta' => 13,
                'titulo' => 'Gastronomía Visual',
                'descripcion' => 'En la restauración moderna, el ambiente es tan importante como el plato. Utilizamos luz cálida y focalizada para crear espacios íntimos y resaltar la presentación de tus alimentos.',
                'public_image1'=>'/blog/blog-5.webp',
                'public_image2'=>'/blog/body2_titulo.webp',
                'public_image3'=>'/blog/blog-13.webp',
            ],
            // 14
            [
                'id_commend_tarjeta' => 14,
                'titulo' => 'El Arte del Café',
                'descripcion' => 'Las cafeterías son refugios urbanos. Creamos atmósferas relajantes con neones suaves y señalética vintage que invitan a tus clientes a desconectar y disfrutar.',
                'public_image1'=>'/blog/blog-6.webp',
                'public_image2'=>'/blog/fondo_blog.png',
                'public_image3'=>'/blog/blog-14.webp',
            ],
            // 15
            [
                'id_commend_tarjeta' => 15,
                'titulo' => 'Iconos Urbanos',
                'descripcion' => 'La iluminación arquitectónica resalta la belleza estructural de tu edificio. Convertimos tu fachada en una obra de arte nocturna que refuerza tu presencia en la ciudad.',
                'public_image1'=>'/blog/blog-7.webp',
                'public_image2'=>'/blog/letrerosneon12.jpg',
                'public_image3'=>'/blog/blog-15.webp',
            ],
            // 16
            [
                'id_commend_tarjeta' => 16,
                'titulo' => 'Espacios que Motivan',
                'descripcion' => 'El entorno influye en el rendimiento. Oficinas con iluminación dinámica y neones corporativos fomentan la creatividad y el sentido de pertenencia entre los colaboradores.',
                'public_image1'=>'/blog/blog-8.webp',
                'public_image2'=>'/blog/branding_2.webp',
                'public_image3'=>'/blog/blog-16.webp',
            ]
        ];

        DB::table('blog_bodies')->insert($blog_bodies);
    }
}
