<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogs = [
            // 1
            [
                'id_blog_head' => 1,
                'id_blog_body' => 1,
                'id_blog_footer' => 1,
                'link' => 'tu-bar-en-la-mira'
            ],
            // 2
            [
                'id_blog_head' => 2,
                'id_blog_body' => 2,
                'id_blog_footer' => 2,
                'link' => 'ilumina-tu-negocio-con-estilo'
            ],
            // 3
            [
                'id_blog_head' => 3,
                'id_blog_body' => 3,
                'id_blog_footer' => 3,
                'link' => 'letras-acrilicas-modernidad-pura'
            ],
            // 4
            [
                'id_blog_head' => 4,
                'id_blog_body' => 4,
                'id_blog_footer' => 4,
                'link' => 'el-toque-dorado-de-tu-marca'
            ],
            // 5
            [
                'id_blog_head' => 5,
                'id_blog_body' => 5,
                'id_blog_footer' => 5,
                'link' => 'vinilos-decorativos-y-pavonados'
            ],
            // 6
            [
                'id_blog_head' => 6,
                'id_blog_body' => 6,
                'id_blog_footer' => 6,
                'link' => 'displays-digitales-interactivos'
            ],
            // 7
            [
                'id_blog_head' => 7,
                'id_blog_body' => 7,
                'id_blog_footer' => 7,
                'link' => 'cajas-de-luz-lightboxes'
            ],
            // 8
            [
                'id_blog_head' => 8,
                'id_blog_body' => 8,
                'id_blog_footer' => 8,
                'link' => 'letreros-luminosos-de-alto-impacto'
            ],
            // 9
            [
                'id_blog_head' => 9,
                'id_blog_body' => 9,
                'id_blog_footer' => 9,
                'link' => 'branding-corporativo-integral'
            ],
            // 10
            [
                'id_blog_head' => 10,
                'id_blog_body' => 10,
                'id_blog_footer' => 10,
                'link' => 'letras-de-acero-inoxidable'
            ],
            // 11
            [
                'id_blog_head' => 11,
                'id_blog_body' => 11,
                'id_blog_footer' => 11,
                'link' => 'senaletica-corporativa-wayfinding'
            ],
            // 12
            [
                'id_blog_head' => 12,
                'id_blog_body' => 12,
                'id_blog_footer' => 12,
                'link' => 'pantallas-led-publicitarias'
            ],
            // 13
            [
                'id_blog_head' => 13,
                'id_blog_body' => 13,
                'id_blog_footer' => 13,
                'link' => 'restaurantes-modernos-y-chic'
            ],
            // 14
            [
                'id_blog_head' => 14,
                'id_blog_body' => 14,
                'id_blog_footer' => 14,
                'link' => 'cafeterias-con-encanto'
            ],
            // 15
            [
                'id_blog_head' => 15,
                'id_blog_body' => 15,
                'id_blog_footer' => 15,
                'link' => 'fachadas-que-impactan'
            ],
            // 16
            [
                'id_blog_head' => 16,
                'id_blog_body' => 16,
                'id_blog_footer' => 16,
                'link' => 'oficinas-creativas-e-inspiradoras'
            ]
        ];

        DB::table('blogs')->insert($blogs);
    }
}
