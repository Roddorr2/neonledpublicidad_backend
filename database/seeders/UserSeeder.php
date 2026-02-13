<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $users = [
            [
                'name' => 'Admin Staging',
                'email' => 'admin@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Jose Luis',
                'email' => 'joseluis@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Juan Carlos',
                'email' => 'juancarlos@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Krizzia Martina',
                'email' => 'krizzia@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Gonzalo Fernando',
                'email' => 'gonzalo@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Piero Alexander',
                'email' => 'piero@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Diego Torres',
                'email' => 'diego@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Juan Perez',
                'email' => 'cliente.juan@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Ana Garcia',
                'email' => 'cliente.ana@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],
            [
                'name' => 'Luis Torres',
                'email' => 'cliente.luis@staging.neonled.com',
                'password' => Hash::make('staging_password_123'),
            ],

        ];

        DB::table('users')->insert($users);
    }
}
