<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReclamacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('reclamaciones')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('reclamaciones')->insert([
            [
                'nombre' => 'Carlos',
                'apellido' => 'Ramírez',
                'email' => 'carlos.test@staging.neonled.com',
                'telefono' => '998877665',
                'departamento' => 'Lima',
                'direccion' => 'Av. Test 123',
                'distrito' => 'Surco',
                'id_servicio' => 1,
                'fechaIncidente' => '2025-03-15',
                'montoReclamado' => 120.50,
                'descripcionServicio' => 'El servicio no cumplió lo prometido.',
                'checkReclamoForm' => true,
                'aceptaPoliticaPrivacidad' => true,
                'fechaReclamo' => Carbon::now(),
                'estadoReclamo' => 'PENDIENTE'
            ], 
            [
                'nombre' => 'Carlitos',
                'apellido' => 'Test',
                'email' => 'carlitos.test@staging.neonled.com',
                'telefono' => '987654321',
                'departamento' => 'La Libertad',
                'direccion' => 'Av. Test 456',
                'distrito' => 'Trujillo',
                'id_servicio' => 1,
                'fechaIncidente' => '2025-03-15',
                'montoReclamado' => 120.50,
                'descripcionServicio' => 'El servicio no cumplió lo prometido.',
                'checkReclamoForm' => true,
                'aceptaPoliticaPrivacidad' => true,
                'fechaReclamo' => Carbon::now(),
                'estadoReclamo' => 'ATENDIDO'
            ],
        ]);
    }
}
