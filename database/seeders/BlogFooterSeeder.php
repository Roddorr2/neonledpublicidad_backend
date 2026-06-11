<?php

namespace Database\Seeders;

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
                'titulo'        => 'Conclusion',
                'descripcion'   => 'Invertir en luces neón LED no solo mejora la estética de tu bar, sino que también influye en la percepción de los clientes y fortalece tu marca. ¡Haz que tu bar brille con luz propia!',
                'public_image1' => '/blog/blog-2.webp',
                'public_image2' => '/blog/blog-2.webp',
                'public_image3' => '/blog/blog-2.webp',
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('blog_footers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('blog_footers')->insert($blog_footers);
    }
}
