<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestWhatsappCampaignSeeder extends Seeder
{
    /**
     * Seed para pruebas de campaña de WhatsApp
     * Crea 3 registros de prueba con el número 931640662 en el servicio 1
     */
    public function run(): void
    {
        // Limpiar datos de prueba anteriores (opcional)
        DB::table('modal_wats')
            ->whereIn('id_modalservicio', function ($query) {
                $query->select('id_modalservicio')
                    ->from('modalservicios')
                    ->where('telefono', '9xxxxxxxx')
                    ->where('id_producto', 1);
            })
            ->delete();

        DB::table('modalservicios')
            ->where('telefono', '9xxxxxxxx')
            ->where('id_producto', 1)
            ->delete();

        // Crear 3 registros en modalservicios
        $modalserviciosIds = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $id = DB::table('modalservicios')->insertGetId([
                'nombre' => "Test Usuario $i",
                'correo' => "test{$i}@whatsapp-campaign.test",
                'telefono' => '9xxxxxxxx',
                'id_producto' => 1, // Servicio 1
                'estado' => 1,
            ]);
            
            $modalserviciosIds[] = $id;
        }

        // Crear registros correspondientes en modal_wats
        foreach ($modalserviciosIds as $index => $modalservicioId) {
            DB::table('modal_wats')->insert([
                'id_modalservicio' => $modalservicioId,
                'number_message' => '1', // ENUM: '1', '2' o '3'
                'estado' => 0, // 0 = pendiente
            ]);
        }

        $this->command->info('✅ Seeder ejecutado: 3 registros de prueba creados con número 9xxxxxxxx en servicio 1');
    }
}
