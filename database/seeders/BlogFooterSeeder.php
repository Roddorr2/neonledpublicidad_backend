<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogFooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blog_footers = [
            // 1
            [
                'titulo' => 'Conclusión Brillante',
                'descripcion' => 'Invertir en luces neón LED no solo mejora la estética de tu bar, sino que también influye en la percepción de los clientes y fortalece tu marca. ¡Haz que tu bar brille con luz propia!',
                'public_image1'=>'/blog/Bar_letras_neonled.webp',
                'public_image2'=>'/blog/letrerosneon1234.jpg',
                'public_image3'=>'/blog/blog-1.webp',
            ],
            // 2
            [
                'titulo' => 'Identidad Definida',
                'descripcion' => 'Tu fachada es tu mejor vendedor silencioso. Asegúrate de que transmita el mensaje correcto con una iluminación profesional y de alta calidad.',
                'public_image1'=>'/blog/blog-1.webp',
                'public_image2'=>'/blog/branding_2.webp',
                'public_image3'=>'/blog/branding_1.webp',
            ],
            // 3
            [
                'titulo' => 'Acabado Profesional',
                'descripcion' => 'El acrílico es la elección predilecta para quienes buscan durabilidad sin sacrificar estética. Una inversión inteligente para tu imagen corporativa a largo plazo.',
                'public_image1'=>'/blog/ACRILICO.png',
                'public_image2'=>'/blog/fondo_plantilla1.png',
                'public_image3'=>'/blog/blog-3.webp',
            ],
            // 4
            [
                'titulo' => 'Lujo y Prestigio',
                'descripcion' => 'El acabado dorado 3D comunica éxito y prestigio inmediatos. Asegúrate de que tu marca hable el lenguaje de la excelencia desde el primer vistazo.',
                'public_image1'=>'/blog/letras_doradas.png',
                'public_image2'=>'/blog/letras_doradas2.jpg',
                'public_image3'=>'/blog/blog-4.webp',
            ],
            // 5
            [
                'titulo' => 'Estética Funcional',
                'descripcion' => 'El vinilo pavonado ofrece privacidad sin perder luz natural, mientras que el vinilo de corte comunica tu marca. Soluciones prácticas y estéticas para oficinas modernas.',
                'public_image1'=>'/blog/HOLOGRAFICO.png',
                'public_image2'=>'/blog/DISPLAY.png',
                'public_image3'=>'/blog/blog-5.webp',
            ],
            // 6
            [
                'titulo' => 'Conectando con el Cliente',
                'descripcion' => 'Los displays digitales reducen la brecha entre tu marca y tu audiencia. Mantén la conversación viva con contenido relevante y actualizado.',
                'public_image1'=>'/blog/DISPLAY.png',
                'public_image2'=>'/blog/fondo_looking_diseñoweb.webp',
                'public_image3'=>'/blog/blog-6.webp',
            ],
            // 7
            [
                'titulo' => 'Promociones que Brillan',
                'descripcion' => 'Las cajas de luz son una herramienta de venta directa. Asegura que tus mejores productos sean vistos con la claridad y brillos que merecen.',
                'public_image1'=>'/blog/motoled.jpg',
                'public_image2'=>'/blog/led123.jpg',
                'public_image3'=>'/blog/blog-7.webp',
            ],
            // 8
            [
                'titulo' => 'Visibilidad Total',
                'descripcion' => 'No dejes que la oscuridad oculte tu negocio. Un letrero luminoso es un faro que guía a tus clientes directamente a tu puerta.',
                'public_image1'=>'/blog/letrero_luminoso.png',
                'public_image2'=>'/blog/letrero_luminoso2.png',
                'public_image3'=>'/blog/blog-8.webp',
            ],
            // 9
            [
                'titulo' => 'Unificación de Marca',
                'descripcion' => 'La coherencia visual genera confianza. Implementa soluciones de branding integral para fortalecer tu posición en el mercado.',
                'public_image1'=>'/blog/branding_1.webp',
                'public_image2'=>'/blog/branding_2.webp',
                'public_image3'=>'/blog/blog-9.webp',
            ],
            // 10
            [
                'titulo' => 'Distinción Metálica',
                'descripcion' => 'Invierte en imagen. Las letras de acero inoxidable comunican prestigio y son una apuesta segura para marcas que valoran la excelencia y la longevidad.',
                'public_image1'=>'/blog/blog-2.webp',
                'public_image2'=>'/blog/body2_galeria1.webp',
                'public_image3'=>'/blog/blog-10.webp',
            ],
            // 11
            [
                'titulo' => 'Organización Visual',
                'descripcion' => 'Una empresa ordenada es una empresa eficiente. Nuestra señalética corporativa optimiza el flujo de personas y transmite profesionalismo en cada rincón.',
                'public_image1'=>'/blog/blog-3.webp',
                'public_image2'=>'/blog/footer2_plantilla2.webp',
                'public_image3'=>'/blog/blog-11.webp',
            ],
            // 12
            [
                'titulo' => 'Publicidad Gigante',
                'descripcion' => 'Domina el espacio visual. Las pantallas LED de gran formato son la herramienta definitiva para campañas publicitarias de alto impacto en exteriores.',
                'public_image1'=>'/blog/blog-4.webp',
                'public_image2'=>'/blog/Blog4_header.webp',
                'public_image3'=>'/blog/blog-12.webp',
            ],
            // 13
            [
                'titulo' => 'Sabor y Estilo',
                'descripcion' => 'La comida entra por los ojos, y la iluminación es el marco perfecto. Crea experiencias gastronómicas completas cuidando cada detalle visual.',
                'public_image1'=>'/blog/blog-5.webp',
                'public_image2'=>'/blog/body2_titulo.webp',
                'public_image3'=>'/blog/blog-13.webp',
            ],
            // 14
            [
                'titulo' => 'El Café Perfecto',
                'descripcion' => 'Diferénciate de la competencia. Una atmósfera única y acogedora fideliza a tus clientes y los invita a regresar siempre.',
                'public_image1'=>'/blog/blog-6.webp',
                'public_image2'=>'/blog/fondo_blog.png',
                'public_image3'=>'/blog/blog-14.webp',
            ],
            // 15
            [
                'titulo' => 'Hitos Urbanos',
                'descripcion' => 'Transforma el paisaje urbano. Una fachada iluminada no solo destaca tu edificio, sino que embellece la ciudad entera.',
                'public_image1'=>'/blog/blog-7.webp',
                'public_image2'=>'/blog/letrerosneon12.jpg',
                'public_image3'=>'/blog/blog-15.webp',
            ],
            // 16
            [
                'titulo' => 'Cultura Corporativa',
                'descripcion' => 'Invierte en tu equipo. Un espacio de trabajo estimulante es clave para atraer y retener al mejor talento creativo.',
                'public_image1'=>'/blog/blog-8.webp',
                'public_image2'=>'/blog/branding_2.webp',
                'public_image3'=>'/blog/blog-16.webp',
            ]
        ];

        DB::table('blog_footers')->insert($blog_footers);
    }
}
