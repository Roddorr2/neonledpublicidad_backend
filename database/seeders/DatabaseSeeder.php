<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Deshabilitar foreign key checks para todo el seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $this->call([
            UserSeeder::class,
            RolSeeder::class,
            PermisosSeeder::class,
            EmpleadoSeeder::class,
            BlogHeaderSeeder::class,
            CommendTarjetaSeeder::class,
            BlogBodySeeder::class,
            BlogFooterSeeder::class,
            BlogSeeder::class,
            TarjetaSeeder::class,
            BlogSeeder::class,
            ProductoSeeder::class,
            ServicioSeeder::class,
            ModalservicioSeeder::class,
            WatModalSeeder::class,
            MailModalSeeder::class,
            ReclamacionSeeder::class,
            ContactanosSeeder::class,
            ProductoSeeder::class,
            PlantillasEmailSeeder::class,
            PlantillasWhatsappSeeder::class,
            CardSeeder::class,
            ClienteSeeder::class,
            PropuestaSeeder::class,
            PopupConfigSeeder::class,
        ]);
        
        // Rehabilitar foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
