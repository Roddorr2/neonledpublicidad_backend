<?php

namespace Database\Seeders;

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
                'nombre'      => 'Ana Torres EJEMPLO',
                'telefono'    => '983354321',
                'correo'      => 'ana@gmail.com',
                'id_producto' => 1,
            ],
            [
                'nombre'      => 'Lorena Rodriguez EJEMPLO',
                'telefono'    => '987384322',
                'correo'      => 'lorena@gmail.com',
                'id_producto' => 2,
            ],
            [
                'nombre'      => 'Jose Santos EJEMPLO',
                'telefono'    => '987654323',
                'correo'      => 'jose@gmail.com',
                'id_producto' => 3,
            ],
            [
                'nombre'      => 'Luis Romero EJEMPLO',
                'telefono'    => '981154323',
                'correo'      => 'luisito@gmail.com',
                'id_producto' => 4,
            ],
        ];

        DB::table('modalservicios')->insert($modalServicios);
    }
}
