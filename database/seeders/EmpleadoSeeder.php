<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'nombre' => 'Admin',
                'apellido' => 'Staging',
                'email' => 'admin@staging.neonled.com',
                'dni' => '99999999',
                'telefono' => '999999999',
                'id_user' => 1,
                'id_rol' => 1,
            ],
            [
                'nombre' => 'Jose Luis',
                'apellido' => 'Test',
                'email' => 'joseluis@staging.neonled.com',
                'dni' => '88888888',
                'telefono' => '988888888',
                'id_user' => 2,
                'id_rol' => 2,
            ],
            [
                'nombre' => 'Juan Carlos',
                'apellido' => 'Test',
                'email' => 'juancarlos@staging.neonled.com',
                'dni' => '77777777',
                'telefono' => '977777777',
                'id_user' => 3,
                'id_rol' => 3,
            ],
            [
                'nombre' => 'Krizzia Martina',
                'apellido' => 'Test',
                'email' => 'krizzia@staging.neonled.com',
                'dni' => '66666666',
                'telefono' => '966666666',
                'id_user' => 4,
                'id_rol' => 3,
            ],
            [
                'nombre' => 'Gonzalo Fernando',
                'apellido' => 'Test',
                'email' => 'gonzalo@staging.neonled.com',
                'dni' => '55555555',
                'telefono' => '955555555',
                'id_user' => 5,
                'id_rol' => 2,
            ],
            [
                'nombre' => 'Piero Alexander',
                'apellido' => 'Test',
                'email' => 'piero@staging.neonled.com',
                'dni' => '44444444',
                'telefono' => '944444444',
                'id_user' => 6,
                'id_rol' => 1,
            ] , [
                'nombre' => 'Diego Arturo',
                'apellido' => 'Test',
                'email' => 'diego@staging.neonled.com',
                'dni' => '33333333',
                'telefono' => '933333333',
                'id_user' => 7,
                'id_rol' => 2,
            ]
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('empleados')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('empleados')->insert($empleados);
    }
}
