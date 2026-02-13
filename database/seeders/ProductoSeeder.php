<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            [
                "id_empleado" => 1,
                "nombre" => "Panel LED 60x60",
                "descripcion" => "Panel LED de alta eficiencia para iluminación comercial",

                // Imágenes
                "path_main" => "panel_main.jpg",
                "path_background" => "panel_bg.jpg",

                "path1" => "panel1.jpg",
                "tituloimg1" => "Panel LED 60x60",
                "descripcionimg1" => "Vista frontal del panel LED",

                "path2" => "panel2.jpg",
                "tituloimg2" => "Panel LED en instalación",
                "descripcionimg2" => "Panel LED instalado en techo modular",

                "path3" => "panel3.jpg",
                "tituloimg3" => "Panel LED encendido",
                "descripcionimg3" => "Panel LED encendido mostrando su iluminación",

                // Descripciones adicionales
                "caracteristicas_descrip" => "Eficiencia energética, fácil instalación, diseño delgado",
                "ventajas_descrip" => "Reduce costos de energía y mantenimiento",
                "consumoenergetico_descrip" => "36W por panel, alta luminosidad con bajo consumo",
                "iluminacion_descrip" => "Luz blanca neutra de 4000K",
                "durabilidad_descrip" => "Vida útil estimada de 50,000 horas",

                "estado" => 1
            ],
            [
                "id_empleado" => 1,
                "nombre" => "Letras Iluminadas",
                "descripcion" => "Letras corpóreas iluminadas con tecnología LED",

                // Imágenes
                "path_main" => "panel_main.jpg",
                "path_background" => "panel_bg.jpg",

                "path1" => "letras_iluminadas1.jpg",
                "tituloimg1" => "Letras LED frontal",
                "descripcionimg1" => "Letras iluminadas con LED frontal",

                "path2" => "letras_iluminadas2.jpg",
                "tituloimg2" => "Letras LED lateral",
                "descripcionimg2" => "Iluminación LED lateral para efecto moderno",

                "path3" => "letras_iluminadas3.jpg",
                "tituloimg3" => "Letras LED noche",
                "descripcionimg3" => "Vista nocturna de letras iluminadas",

                // Descripciones adicionales
                "caracteristicas_descrip" => "Material de acrílico y acero inoxidable, iluminación LED interna",
                "ventajas_descrip" => "Alta visibilidad, bajo consumo energético",
                "consumoenergetico_descrip" => "Consumo promedio 12V DC, 1.5A",
                "iluminacion_descrip" => "LED blanco o RGB",
                "durabilidad_descrip" => "Resistente al clima, durabilidad de 5 años",

                "estado" => 1
            ],
            [
                "id_empleado" => 2,
                "nombre" => "Letras Alto Relieve",
                "descripcion" => "Letras corpóreas en alto relieve para fachadas",

                // Imágenes
                "path_main" => "panel_main.jpg",
                "path_background" => "panel_bg.jpg",

                "path1" => "alto_relieve1.jpg",
                "tituloimg1" => "Letras en montaje",
                "descripcionimg1" => "Montaje de letras en relieve",

                "path2" => "alto_relieve2.jpg",
                "tituloimg2" => "Detalle de relieve",
                "descripcionimg2" => "Vista lateral del alto relieve",

                "path3" => "alto_relieve3.jpg",
                "tituloimg3" => "Letras finalizadas",
                "descripcionimg3" => "Letras instaladas en fachada",

                // Descripciones adicionales
                "caracteristicas_descrip" => "Fabricadas en aluminio, corte preciso",
                "ventajas_descrip" => "Diseño elegante y duradero",
                "consumoenergetico_descrip" => "No requiere energía (sin iluminación)",
                "iluminacion_descrip" => "Puede combinarse con iluminación externa",
                "durabilidad_descrip" => "Duración superior a 8 años",

                "estado" => 0
            ],
            [
                "id_empleado" => 2,
                "nombre" => "Letras en Acrílico",
                "descripcion" => "Letras corpóreas en acrílico para fachadas",

                // Imágenes
                "path_main" => "panel_main.jpg",
                "path_background" => "panel_bg.jpg",

                "path1" => "acrilico1.jpg",
                "tituloimg1" => "Letras acrílicas blancas",
                "descripcionimg1" => "Diseño limpio y moderno",

                "path2" => "acrilico2.jpg",
                "tituloimg2" => "Detalle de montaje",
                "descripcionimg2" => "Instalación sobre base metálica",

                "path3" => "acrilico3.jpg",
                "tituloimg3" => "Vista final",
                "descripcionimg3" => "Fachada con letras en acrílico",

                // Descripciones adicionales
                "caracteristicas_descrip" => "Material acrílico de alta calidad",
                "ventajas_descrip" => "Resistente a rayos UV y fácil mantenimiento",
                "consumoenergetico_descrip" => "No aplica",
                "iluminacion_descrip" => "Compatible con LED externo",
                "durabilidad_descrip" => "Durabilidad promedio de 6 años",

                "estado" => 0
            ]
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('productos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('productos')->insert($productos);
    }
}
