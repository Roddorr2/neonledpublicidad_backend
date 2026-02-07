<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            // 1. Tu Bar, en la Mira
            [
                'titulo' => "Consejos para Elegir el Letrero Perfecto",
                'texto1' => "Opta por colores que reflejen la personalidad de tu bar.",
                'texto2' => "Elige un diseño legible y atractivo.",
                'texto3' => "Considera el lugar de instalación para maximizar su impacto.",
            ],
            // 2. Ilumina tu Negocio
            [
                'titulo' => "Mantenimiento de tus Luces Neón",
                'texto1' => "Limpia regularmente con un paño suave y seco.",
                'texto2' => "Evita el uso de productos químicos abrasivos.",
                'texto3' => "Revisa las conexiones eléctricas periódicamente.",
            ],
            // 3. Letras Acrílicas
            [
                'titulo' => "Ventajas del Acrílico",
                'texto1' => "Durabilidad garantizada frente a cambios climáticos.",
                'texto2' => "Acabado brillante y elegante que resalta tu marca.",
                'texto3' => "Ideal para señalética tanto en interiores como exteriores.",
            ],
            // 4. Toque Dorado
            [
                'titulo' => "Elegancia en 3D",
                'texto1' => "Letras con volumen y gran prestancia visual.",
                'texto2' => "Acabado dorado premium para negocios exclusivos.",
                'texto3' => "Material resistente a la oxidación y corrosión.",
            ],
            // 5. Vinilos Decorativos
            [
                'titulo' => "Vinilos: Versatilidad Total",
                'texto1' => "Ideal para privacidad en divisores de vidrio.",
                'texto2' => "Instalación rápida sin obras ni suciedad.",
                'texto3' => "Refuerza tu branding en paredes y ventanas.",
            ],
            // 6. Displays Digitales
            [
                'titulo' => "Comunicación Dinámica",
                'texto1' => "Actualiza tu contenido en tiempo real fácilmente.",
                'texto2' => "Muestra videos, animaciones y promociones rotativas.",
                'texto3' => "Mayor tasa de retención de mensaje vs cartelería estática.",
            ],
            // 7. Cajas de Luz
            [
                'titulo' => "Cajas de Luz de Alto Brillo",
                'texto1' => "Iluminación uniforme sin sombras molestas.",
                'texto2' => "Sistema snap-frame para cambio fácil de gráficas.",
                'texto3' => "Bajo consumo energético y larga vida útil.",
            ],
            // 8. Letreros Luminosos
            [
                'titulo' => "Visibilidad Nocturna",
                'texto1' => "Asegura que tu negocio sea visible 24/7.",
                'texto2' => "Tecnología LED de alta eficiencia y bajo consumo.",
                'texto3' => "Colores vibrantes que no se desvanecen con el tiempo.",
            ],
            // 9. Branding Corporativo
            [
                'titulo' => "Identidad de Marca Sólida",
                'texto1' => "Refuerza tu logo con iluminación estratégica.",
                'texto2' => "Crea un ambiente profesional en tu recepción.",
                'texto3' => "La primera impresión es la que cuenta para tus clientes.",
            ],
            // 10. Letras de Acero
            [
                'titulo' => "Elegancia en Acero",
                'texto1' => "Resistencia extrema a la corrosión y el clima.",
                'texto2' => "Acabados en pulido espejo o satinado elegante.",
                'texto3' => "Ideal para fachadas de edificios corporativos.",
            ],
            // 11. Señalética
            [
                'titulo' => "Señalética Eficiente",
                'texto1' => "Facilita la orientación de visitas dentro de tu empresa.",
                'texto2' => "Cumple con normativas de seguridad y accesibilidad.",
                'texto3' => "Diseños modulares fáciles de actualizar.",
            ],
            // 12. Pantallas LED
            [
                'titulo' => "Pantallas Gigantes LED",
                'texto1' => "Brillo ajustable para visibilidad bajo luz solar directa.",
                'texto2' => "Resistencia IP65 para exteriores y lluvias.",
                'texto3' => "Control remoto de contenidos vía software.",
            ],
            // 13. Restaurantes Modernos
            [
                'titulo' => "Experiencia Gastronómica",
                'texto1' => "Ilumina el menú y áreas clave con estilo.",
                'texto2' => "Crea atmósferas íntimas con luz regulable.",
                'texto3' => "Destaca tu oferta culinaria visualmente.",
            ],
            // 14. Cafeterías con Encanto
            [
                'titulo' => "Coffee & Lights",
                'texto1' => "Ambiente acogedor para atraer a nómadas digitales.",
                'texto2' => "Destaca tu barra de café con iluminación cálida.",
                'texto3' => "Un letrero 'Open' original marca la diferencia.",
            ],
            // 15. Fachadas Impactantes
            [
                'titulo' => "Arquitectura e Iluminación",
                'texto1' => "Resalta líneas arquitectónicas de tu fachada.",
                'texto2' => "Iluminación wash para texturas y relieves.",
                'texto3' => "Convierte tu edificio en un hito urbano nocturno.",
            ],
            // 16. Oficinas Creativas
            [
                'titulo' => "Productividad y Diseño",
                'texto1' => "Espacios de trabajo que inspiran creatividad.",
                'texto2' => "Iluminación funcional combinada con estética.",
                'texto3' => "Logotipos corporativos que motivan al equipo.",
            ]
        ];

        DB::table('commend_tarjetas')->insert($commend_tarjetas);
    }
}
