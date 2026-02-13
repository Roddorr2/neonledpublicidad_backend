<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalservicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalServicios = [
            [
                'nombre' => 'Ana Torres Test',
                'telefono' => '983354321',
                'correo' => 'ana.torres@staging.neonled.com',
                'id_producto' => 1,
            ],
            [
                'nombre' => 'Lorena Rodriguez Test',
                'telefono' => '987384322',
                'correo' => 'lorena.rodriguez@staging.neonled.com',
                'id_producto' => 2,
            ],
            [
                'nombre' => 'Jose Santos Test',
                'telefono' => '987654323',
                'correo' => 'jose.santos@staging.neonled.com',
                'id_producto' => 3,
            ],
            [
                'nombre' => 'Luis Romero Test',
                'telefono' => '981154323',
                'correo' => 'luis.romero@staging.neonled.com',
                'id_producto' => 4,
            ],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('modalservicios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('modalservicios')->insert($modalServicios);
    }
}
