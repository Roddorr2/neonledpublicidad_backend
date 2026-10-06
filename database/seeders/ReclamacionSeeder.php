<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReclamacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('reclamaciones')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('reclamaciones')->insert([
            [
                'nombre'                   => 'Carlos',
                'apellido'                 => 'Ramírez',
                'email'                    => 'carlos@example.com',
                'telefono'                 => '998877665',
                'departamento'             => 'Lima',
                'direccion'                => 'Av. Siempre Viva 123',
                'distrito'                 => 'Surco',
                'id_servicio'              => 1,
                'fechaIncidente'           => '2025-03-15',
                'montoReclamado'           => 120.50,
                'descripcionServicio'      => 'El servicio no cumplió lo prometido.',
                'checkReclamoForm'         => true,
                'aceptaPoliticaPrivacidad' => true,
                'fechaReclamo'             => Carbon::now(),
                'estadoReclamo'            => 'PENDIENTE',
            ],
            [
                'nombre'                   => 'Carlitos',
                'apellido'                 => 'Ramírez',
                'email'                    => 'carlitoss@example.com',
                'telefono'                 => '98177665',
                'departamento'             => 'La Libertad',
                'direccion'                => 'Av. Siempre Viva 123',
                'distrito'                 => 'Chepén',
                'id_servicio'              => 1,
                'fechaIncidente'           => '2025-03-15',
                'montoReclamado'           => 120.50,
                'descripcionServicio'      => 'El servicio no cumplió lo prometido.',
                'checkReclamoForm'         => true,
                'aceptaPoliticaPrivacidad' => true,
                'fechaReclamo'             => Carbon::now(),
                'estadoReclamo'            => 'ATENDIDO',
            ],
        ]);
    }
}
