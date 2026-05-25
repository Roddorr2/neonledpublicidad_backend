<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Contactanos;
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
            BlogHeaderSeeder::class,
            BlogFooterSeeder::class,
            CommendTarjetaSeeder::class,
            BlogBodySeeder::class,
            TarjetaSeeder::class,
            BlogSeeder::class,
            ServicioSeeder::class,
            ModalservicioSeeder::class,
            WatModalSeeder::class,
            MailModalSeeder::class,
            ReclamacionSeeder::class,
            ContactanosSeeder::class,
            EmpleadoSeeder::class,
            ProductoSeeder::class,
            PlantillasEmailSeeder::class,
            PlantillasWhatsappSeeder::class,
            CardSeeder::class,
            ContactanosSeeder::class,
            ClienteSeeder::class,
            PropuestaSeeder::class,

            //se agrego esto: PopupConfigSeeder AL FINAL (después de ProductoSeeder)
//          para que los productos ya existan cuando se ejecute
            PopupConfigSeeder::class,
        ]);
    }
}
