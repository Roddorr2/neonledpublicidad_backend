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
                'email' => 'juan.perez@example.com',
                'telefono' => '999111222',
                'distrito' => 'Miraflores',
                'imagen_perfil' => null,
                'imagen_perfil_url' => null,
                'id_user' => 8,
                'id_rol' => 4, // Asegúrate de que este rol exista
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'García',
                'email' => 'ana.garcia@example.com',
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
                'email' => 'luis.torres@example.com',
                'telefono' => '955444333',
                'distrito' => 'Surco',
                'imagen_perfil' => null,
                'imagen_perfil_url' => null,
                'id_user' => 10,
                'id_rol' => 4,
            ],
        ];
        DB::table('clientes')->insert($clientes);
    }
}
