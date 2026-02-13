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
            [
                'titulo' => 'Tu Bar, en la Mira',
                'texto_frase' => 'Ilumina tu espacio, cautiva a tus clientes',
                'texto_descripcion' => 'Transforma la atmósfera de tu bar con luces neón LED vibrantes y llenas de estilo.',
                'public_image'=>'/blog/letra_neonled2.png'
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('blog_heads')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('blog_heads')->insert($blog_heads);
    }
}
