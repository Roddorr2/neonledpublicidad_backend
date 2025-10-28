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
            [
                'titulo' => 'Conclusion',
                'descripcion' => 'Invertir en luces neón LED no solo mejora la estética de tu bar, sino que también influye en la percepción de los clientes y fortalece tu marca. ¡Haz que tu bar brille con luz propia!',
                'public_image1'=>'/blog/blog-2.jpg',
                'public_image2'=>'/blog/blog-2.jpg',
                'public_image3'=>'/blog/blog-2.jpg',
                // 'estado' => true,
                // 'keyword' => 'luces neón LED, bar, publicidad, marketing',
                // 'link' => 'https://neonledpublicidad.com/blog/beneficios-luces-neon-led-para-tu-bar',
            ],
        ];

        DB::table('blog_footers')->insert($blog_footers);
    }
}
