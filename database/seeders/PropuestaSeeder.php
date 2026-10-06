<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Propuesta;
use Illuminate\Database\Seeder;
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
                    'nombre' => "Propuesta {$i} de {$cliente->nombre}",
                    'descripcion' => "Esta es una descripción genérica para la propuesta {$i} del cliente {$cliente->nombre} {$cliente->apellido}.",
                ]);
            }
        }
    }
}
