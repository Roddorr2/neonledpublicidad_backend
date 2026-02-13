<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use  App\Models\Cliente;
use App\Models\Propuesta;
use Illuminate\Support\Facades\DB;
class PropuestaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $clientes = Cliente::all();

        // Asegúrate de tener clientes en la base de datos
        if ($clientes->isEmpty()) {
            $this->command->warn('⚠️ No hay clientes en la base de datos. No se generaron propuestas.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Propuesta::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($clientes as $cliente) {
            // Generar 2 propuestas por cliente
            for ($i = 1; $i <= 2; $i++) {
                Propuesta::create([
                    'id_cliente' => $cliente->id,
                    'nombre' => "Propuesta {$i} - Proyecto LED para {$cliente->nombre}",
                    'descripcion' => "Propuesta de solución de iluminación LED para el cliente {$cliente->nombre} {$cliente->apellido}. Incluye análisis del espacio, recomendaciones de diseño y presupuesto detallado.",
                ]);
            }
        }
    }
}
