<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailModalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mail_modals = [
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 1,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 1,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 1,
                'number_message'   => 3,
                'fecha'            => now(),
            ],

            [
                'estado'           => 1,
                'error'            => 'Error de envio, no existe el correo',
                'id_modalservicio' => 2,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 2,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 2,
                'number_message'   => 3,
                'fecha'            => now(),
            ],

            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 3,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 3,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 1,
                'error'            => '',
                'id_modalservicio' => 3,
                'number_message'   => 3,
                'fecha'            => now(),
            ],

            [
                'estado'           => 1,
                'error'            => 'Error de envio, no existe el correo',
                'id_modalservicio' => 4,
                'number_message'   => 1,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 4,
                'number_message'   => 2,
                'fecha'            => now(),
            ],
            [
                'estado'           => 0,
                'error'            => '',
                'id_modalservicio' => 4,
                'number_message'   => 3,
                'fecha'            => now(),
            ],

        ];

        DB::table('modal_emails')->insert($mail_modals);
    }
}
