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
            [
                'titulo' => 'Tu Bar, en la Mira',
                'descripcion' => 'Las luces neón LED se han convertido en un elemento diferenciador en el mundo de la hospitalidad. No solo son visualmente atractivos, sino que también refuerzan la identidad de tu negocio. En este artículo, exploraremos cómo las letras luminosas pueden marcar la diferencia en la experiencia de tus clientes.',
                'id_commend_tarjeta' => 1,
                'public_image1'=>'/blog/blog-2.jpg',
                'public_image2'=>'/blog/blog-2.jpg',
                'public_image3'=>'/blog/blog-2.jpg'
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('blog_bodies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('blog_bodies')->insert($blog_bodies);
    }
}
