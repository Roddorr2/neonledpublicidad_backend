<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantillasWhatsappSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $messagesByProduct = $this->messagesByProduct();
        $rows = [];

        foreach ($messagesByProduct as $idProducto => $messages) {
            foreach ($messages as $numeroPlantilla => $mensaje) {
                $rows[] = [
                    'id_producto' => (int) $idProducto,
                    'numero_plantilla' => (int) $numeroPlantilla,
                    'nombre' => null,
                    'mensaje' => trim($mensaje),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('plantillas_whatsapp')->upsert(
            $rows,
            ['id_producto', 'numero_plantilla'],
            ['mensaje', 'updated_at']
        );
    }

    private function messagesByProduct(): array
    {
        return [
            1 => [
                1 => "✨ Letras de Acrílico Personalizadas\nFabricadas en acrílico de alta calidad. Son resistentes, ligeras y totalmente personalizables en tamaño, color y tipografía, con opción de iluminación LED.",
                2 => "🏢 Impacto visual inmediato\nIdeales para fachadas, interiores, señalética y eventos. Mejoran la visibilidad del negocio y refuerzan la identidad de marca de forma elegante.",
                3 => "⭐ Tu marca con un acabado profesional\nOfrecen un diseño moderno, duradero y adaptable a cualquier espacio, logrando que tu marca destaque de día y de noche.",
            ],
            2 => [
                1 => "✨ Letras de Aluminio Doradas 3D\nFabricadas en aluminio con acabado metálico dorado. Son resistentes, elegantes y personalizables en tamaño y tipografía, con opción de retroiluminación.",
                2 => "🏢 Presencia y lujo visual\nIdeales para oficinas, tiendas, restaurantes y señalización premium. Aportan un alto impacto visual y transmiten prestigio desde el primer momento.",
                3 => "⭐ Un acabado que eleva tu marca\nSu efecto dorado 3D proyecta exclusividad, durabilidad y sofisticación, diferenciando tu espacio con un estilo de alta gama.",
            ],
            3 => [
                1 => "Hola 👋\n Las Letras de Aluminio Plateadas 3D ofrecen una apariencia moderna, profesional y elegante, ideales para espacios que buscan proyectar solidez, limpieza y tecnología.\n Su acabado metálico (brillante o satinado) genera un reflejo sobrio que se adapta fácilmente a distintos tipos de superficies e iluminación, siendo una solución duradera y versátil para la señalización interior y exterior.",
                2 => "Este tipo de letras se utiliza ampliamente en tiendas, oficinas, restaurantes, bares, empresas corporativas, tragamonedas, instituciones educativas y proyectos de branding.\n Gracias a su efecto tridimensional, logran mayor visibilidad, presencia y recordación de marca, elevando la percepción del espacio y generando un impacto visual inmediato en clientes y visitantes.",
                3 => "A diferencia de otros materiales, las Letras de Aluminio Plateadas 3D destacan por su acabado premium, resistencia y estética contemporánea.\n Son una excelente inversión para quienes buscan diferenciar su marca, transmitir profesionalismo y mantener una imagen sólida y moderna a largo plazo, tanto en interiores como en exteriores.",
            ],
            4 => [
                1 => "✨ Letreros Luminosos Personalizados\n Fabricados con materiales resistentes y sistemas de iluminación LED de alta eficiencia. Son totalmente personalizables en tamaño, color, tipografía y tipo de luz, ideales para destacar tu marca de día y de noche.\n.",
                2 => "🏪 Haz que tu negocio se vea desde lejos\n Perfectos para fachadas, interiores y puntos estratégicos. Aumentan la visibilidad y atraen más miradas, incluso en zonas de alto tránsito.",
                3 => "⭐ Tu marca siempre encendida\n Diseño duradero, iluminación eficiente y acabados profesionales que proyectan confianza y calidad.",
            ],
            5 => [
                1 => "✨ Neón LED Personalizado\n Fabricado con tecnología LED flexible, segura y de bajo consumo. Disponible en distintos colores, formas y frases, ideal para decoración y branding moderno.",
                2 => "✨ Decora, comunica y crea ambiente\n Ideal para tiendas, bares, eventos y espacios instagrameables. El neón LED transforma cualquier espacio en un punto visual atractivo.",
                3 => "⭐ Diseño que expresa personalidad\n Una forma moderna y creativa de diferenciar tu marca y conectar emocionalmente con tu público.",
            ],
            6 => [
                1 => "✨ Impresión en Vinilo Decorativo\n Vinilos de alta calidad, resistentes y personalizables en diseño, tamaño y acabado. Ideales para interiores y exteriores.",
                2 => "🏬 Transforma tu espacio sin obras\n Perfectos para vitrinas, paredes, branding y campañas temporales. Cambia la imagen de tu negocio de forma rápida y efectiva.",
                3 => "⭐ Comunica con diseño\n Los vinilos permiten renovar, promocionar y personalizar tu espacio sin grandes inversiones.",
            ],
            7 => [
                1 => "📋 Menú Boards Personalizados\n Fabricados con materiales de alta calidad y diseños totalmente personalizables. Ideales para mostrar precios, promociones y productos de forma clara, moderna y ordenada, con opción de iluminación LED.",
                2 => "🍔 Orden, claridad y más ventas\n Ideales para restaurantes, cafeterías, food courts y dark kitchens. Facilitan la lectura del menú, mejoran la experiencia del cliente y agilizan la toma de decisiones.",
                3 => "⭐ Tu carta, con imagen profesional\n Diseño moderno, resistente y adaptable a cualquier concepto gastronómico. Un menú board bien diseñado comunica calidad, organización y confianza.",
            ],
            8 => [
                1 => "👋 ¡Bienvenido! Nuestros monitores de publicidad digital muestran contenido dinámico y de alto impacto. Ideales para promociones, menús digitales y publicidad comercial.",
                2 => "Capta más miradas en segundos 👀 Los monitores digitales comunican promociones en tiempo real y mejoran la experiencia del cliente. Más atención, más recordación.",
                3 => "Moderniza tu negocio con pantallas digitales 🚀\nActualiza contenido al instante, reduce impresos y proyecta una imagen innovadora.\nPublicidad que sí se nota.",
            ],
            9 => [
                1 => "¡Hola! Nuestras letras pintadas en MDF son personalizadas, resistentes y con acabados premium. Ideales para interiores, marcas, stands y decoración comercial. Tú eliges tamaño, color y estilo 🎨",
                2 => "¿Buscas que tu marca se note? ✨ Las letras MDF pintadas aportan volumen, elegancia y presencia visual inmediata. Perfectas para locales, recepciones y vitrinas.",
                3 => "Haz que tu espacio hable por tu marca 🔥\nNuestras letras MDF combinan diseño, precisión y personalización total.\nUna inversión estética que eleva tu imagen profesional.",
            ],
            10 => [
                1 => "💡Pantallas LED Personalizadas\nFabricadas con tecnología LED de alta luminosidad que garantizan visibilidad total de videos y mensajes dinámicos, incluso de día. Son 100% personalizables en tamaño y formato, ideales para modernizar la publicidad en tiendas retail o eventos.",
                2 => "👀Visibilidad que atrae\nUtilizadas en tiendas retail, centros comerciales y eventos, las pantallas LED ayudan a captar la atención del público, reforzar lanzamientos de marca y comunicar promociones de forma clara y atractiva.",
                3 => "💎Valor que se percibe\nLas pantallas LED transforman la forma en que tu marca se comunica. Aportan una imagen moderna, sólida y confiable, ayudando a que tu negocio destaque y genere mayor impacto visual frente a su público.",
            ],
            11 => [
                1 => "🚀 Bienvenido/a. Nuestros hologramas convierten tus ideas en experiencias visuales únicas. Conoce soluciones innovadoras que destacan tu producto desde el primer instante.",
                2 => "Los negocios que usan hologramas 3D logran que la gente se detenga un 50% más de tiempo frente a su vitrina. 🤯 Es la herramienta perfecta para que tu marca no solo se vea, sino que se recuerde por mucho tiempo. 🚀",
                3 => "Ser de los primeros en usar tecnología 3D te pone pasos adelante de tu competencia. ✨ Es publicidad que se hace sola: ¡tus clientes grabarán el letrero y lo compartirán en redes! 🤳",
            ],
            12 => [
                1 => "Refleja tu personalidad 💡✨\nFabricadas y adaptadas a medida para cualquier ambiente, nuestros túneles led cuentan con pixeles leds de alta luminosidad que generan un efecto dinámico y de profundidad 3D. Además, puedes personalizar los colores y patrones para reflejar tu estilo único.",
                2 => "Dale vida a lugares aburridos ➡️🚀\nTiene el poder de transformar un espacio \"muerto\" en el protagonista del lugar. Se instala con facilidad, pero se convierte en una experiencia visual envolvente.",
                3 => "El Túnel Led es la tendencia en decoración tech actual. 🚀💡\nHaz que los eventos de tu marca sean memorables, nuestros túneles led te permiten crear efectos de flujo con, no solo un color plano, sino que varios colores al mismo tiempo. Es como tener un arco iris personalizado.",
            ],
            13 => [
                1 => "Haz que tu espacio destaque desde el primer instante ✨\nLas Sillas LED unen diseño moderno e iluminación para crear ambientes llamativos.",
                2 => "Un ambiente bien iluminado transforma por completo un espacio ✨\nCon Sillas LED, crea una experiencia visual que atrae miradas y genera impacto.",
                3 => "Las Sillas LED iluminan tu espacio con bajo consumo, batería recargable y colores personalizables.\nHaz que tu ambiente se vea como realmente merece. ✨",
            ],
            14 => [
                1 => "✨ Techos LED personalizados que integran diseño arquitectónico e iluminación eficiente.\n Luz uniforme, regulable y adaptable en tamaño, intensidad y tono cálido o frío.",
                2 => "🚀 Crea ambientes con mayor amplitud, ideales para tiendas o espacios sin ventanas. Transforma lugares oscuros en entornos modernos con mayor confort visual y sensación de altura.",
                3 => "⭐ Diseño minimalista y funcional\nOfrecen un acabado premium que se integra al techo sin cables a la vista. Es la solución más estética y duradera para renovar cualquier espacio.",
            ],
            15 => [
                1 => "✨ Letras de Neón en tubos de vidrio artesanales.\nLuz continua y vibrante, moldeadas a mano con acabados únicos.\nTotalmente personalizables en texto, tamaño y color.",
                2 => "✨ Impacto visual que se nota\n Las Letras de Neón en Vidrio mejoran la visibilidad de tu negocio, especialmente de noche, haciendo que tu local sea fácil de identificar y recordar.",
                3 => "Las Letras de Neón en tubos de vidrio atraen miradas y diferencian tu marca 🔥\nEleva la imagen de tu negocio y genera mayor interés de clientes.",
            ],
        ];
    }
}
