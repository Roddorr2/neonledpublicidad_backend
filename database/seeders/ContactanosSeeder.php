<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactanosSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('contactanos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('contactanos')->insert([
            [
                'nombre'                   => 'Juan',
                'apellido'                 => 'Pérez',
                'telefono'                 => '987654321',
                'distrito'                 => 'Miraflores',
                'email'                    => 'juan@example.com',
                'detalle_reclamacion'      => 'CONSULTA',
                'mensaje'                  => 'Me gustaría saber más sobre sus servicios.',
                'estado'                   => false,
                'fecha_hora'               => Carbon::now(),
                'fecha_hora_actualizacion' => null,
            ],
            [
                'nombre'                   => 'Lucía',
                'apellido'                 => 'García',
                'telefono'                 => '912345678',
                'distrito'                 => 'San Isidro',
                'email'                    => 'lucia@example.com',
                'detalle_reclamacion'      => 'RECLAMO',
                'mensaje'                  => 'Tuve un problema con un servicio.',
                'estado'                   => true,
                'fecha_hora'               => Carbon::now()->subDays(2),
                'fecha_hora_actualizacion' => Carbon::now()->subDay(),
            ],
        ]);
    }
}
