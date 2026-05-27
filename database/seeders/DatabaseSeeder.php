<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolSeeder::class,
            PermisosSeeder::class,
            EmpleadoSeeder::class,
            BlogHeaderSeeder::class,
            BlogFooterSeeder::class,
            CommendTarjetaSeeder::class,
            BlogBodySeeder::class,
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
            ContactanosSeeder::class,
            ClienteSeeder::class,
            PropuestaSeeder::class,
            PopupConfigSeeder::class,
        ]);
    }
}
