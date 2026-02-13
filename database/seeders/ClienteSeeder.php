<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'cliente.juan@staging.neonled.com',
                'telefono' => '999111222',
                'distrito' => 'Miraflores',
                'imagen_perfil' => null,
                'imagen_perfil_url' => null,
                'id_user' => 8,
                'id_rol' => 4,
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'García',
                'email' => 'cliente.ana@staging.neonled.com',
                'telefono' => '988777666',
                'distrito' => 'San Isidro',
                'imagen_perfil' => null,
                'imagen_perfil_url' => null,
                'id_user' => 9,
                'id_rol' => 4,
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Torres',
                'email' => 'cliente.luis@staging.neonled.com',
                'telefono' => '955444333',
                'distrito' => 'Surco',
                'imagen_perfil' => null,
                'imagen_perfil_url' => null,
                'id_user' => 10,
                'id_rol' => 4,
            ],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('clientes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('clientes')->insert($clientes);
    }
}
