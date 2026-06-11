<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'administrador',
            ],
            [
                'nombre' => 'ventas',
            ],
            [
                'nombre' => 'marketing',
            ],
            [
                'nombre' => 'cliente',
            ],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('roles')->insert($roles);
    }
}
