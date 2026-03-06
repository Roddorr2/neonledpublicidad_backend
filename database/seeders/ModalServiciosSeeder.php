<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ModalServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phones = [
            '931640662',
            '922958101',
            '930190189',
            '963977829',
            '933946247',
            '967212984'
        ];

        $faker = Faker::create('es_PE');

        // Insert approx 50 records using the phone numbers above (duplicates with different names/emails)
        for ($i = 0; $i < 50; $i++) {
            $phone = $phones[$i % count($phones)];
            $name = $faker->firstName . ' ' . $faker->lastName . ' ' . ($i + 1);
            $email = 'test+' . ($i + 1) . '@example.com';

            // Insert into modalservicios
            $id = DB::table('modalservicios')->insertGetId([
                'nombre' => $name,
                'telefono' => $phone,
                'correo' => $email,
                'id_producto' => 9,
                'fecha' => Carbon::now()->toDateTimeString(),
                'estado' => 1
            ], 'id_modalservicio');

            // Create a corresponding modal_wats row to be used by planner
            DB::table('modal_wats')->insert([
                'estado' => 0,
                'error' => null,
                'id_modalservicio' => $id,
                'number_message' => 1,
                'fecha' => Carbon::now()->toDateTimeString(),
                'reservation_id' => null,
                'scheduled_at' => null,
                'flow_type' => null,
                'campaign_id' => null,
            ]);
        }
    }
}
