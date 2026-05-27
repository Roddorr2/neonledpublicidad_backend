<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WatModal;

class WatModalSeeder extends Seeder
{
    public function run(): void
    {
        $wat_modals = [
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 1,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 1,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 2,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 2,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 3,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 3,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 4,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 4,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
        ];

        $creados = 0;
        $actualizados = 0;

        foreach ($wat_modals as $data) {
            $modal = WatModal::updateOrCreate(
                [
                    // Atributos para buscar si ya existe
                    'id_modalservicio' => $data['id_modalservicio'],
                    'number_message'   => $data['number_message'],
                ],
                $data // Datos a insertar o actualizar
            );

            if ($modal->wasRecentlyCreated) {
                $creados++;
            } else {
                $actualizados++;
            }
        }

        $this->command->info("WatModalSeeder: {$creados} creados, {$actualizados} actualizados.");
    }
}