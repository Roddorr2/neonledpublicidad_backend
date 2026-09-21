<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Staging',
                'email' => 'admin@staging.neonled.com',
            ],
            [
                'name' => 'Jose Luis',
                'email' => 'joseluis@staging.neonled.com',
            ],
            [
                'name' => 'Juan Carlos',
                'email' => 'juancarlos@staging.neonled.com',
            ],
            [
                'name' => 'Krizzia Martina',
                'email' => 'krizzia@staging.neonled.com',
            ],
            [
                'name' => 'Gonzalo Fernando',
                'email' => 'gonzalo@staging.neonled.com',
            ],
            [
                'name' => 'Piero Alexander',
                'email' => 'piero@staging.neonled.com',
            ],
            [
                'name' => 'Diego Torres',
                'email' => 'diego@staging.neonled.com',
            ],
            [
                'name' => 'Juan Perez',
                'email' => 'cliente.juan@staging.neonled.com',
            ],
            [
                'name' => 'Ana Garcia',
                'email' => 'cliente.ana@staging.neonled.com',
            ],
            [
                'name' => 'Luis Torres',
                'email' => 'cliente.luis@staging.neonled.com',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('staging_password_123'),
                ]
            );
        }

        $this->command->info('Usuarios creados/actualizados correctamente.');
    }
}