<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleados = [
            [
                'nombre'   => 'Kevin Esteeven',
                'apellido' => 'Parimango Gomez',
                'email'    => 'keving.kpg@gmail.com',
                'dni'      => '72899618',
                'telefono' => '929686486',
                'id_user'  => 1,
                'id_rol'   => 1,
            ],
            [
                'nombre'   => 'Jose Luis',
                'apellido' => 'Gutierrez',
                'email'    => 'joseluisjlgd123@gmail.com',
                'dni'      => '75308553',
                'telefono' => '927249150',
                'id_user'  => 2,
                'id_rol'   => 1,
            ],
            [
                'nombre'   => 'Juan Carlos',
                'apellido' => 'Molina Orrego',
                'email'    => 'tmlighting@hotmail.com',
                'dni'      => '10299639',
                'telefono' => '936910425',
                'id_user'  => 3,
                'id_rol'   => 1,
            ],
            [
                'nombre'   => 'Krizzia Martina',
                'apellido' => 'Saavedra Navarro',
                'email'    => 'krizzia_saavedra201@hotmail.com',
                'dni'      => '72851260',
                'telefono' => '938405611',
                'id_user'  => 4,
                'id_rol'   => 1,
            ],
            [
                'nombre'   => 'Gonzalo Fernando',
                'apellido' => 'Gallardo Huertas',
                'email'    => 'gogozgallardo22@gmail.com',
                'dni'      => '73068386',
                'telefono' => '924783666',
                'id_user'  => 5,
                'id_rol'   => 1,
            ],
            [
                'nombre'   => 'Piero Alexander',
                'apellido' => 'Catacora Mamani',
                'email'    => 'pierocatacorayt13@gmail.com',
                'dni'      => '70430224',
                'telefono' => '985237799',
                'id_user'  => 6,
                'id_rol'   => 1,
            ], [
                'nombre'   => 'Diego Arturo',
                'apellido' => 'Torres Pacherres',
                'email'    => 'diego_torres_11@hotmail.com',
                'dni'      => '48314547',
                'telefono' => '986377441',
                'id_user'  => 7,
                'id_rol'   => 1,
            ],
        ];
        DB::table('empleados')->insert($empleados);
    }
}
