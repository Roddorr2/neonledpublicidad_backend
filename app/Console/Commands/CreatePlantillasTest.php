<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Productos;
use App\Models\PlantillaWhatsapp;

class CreatePlantillasTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plantillas:create-test {id_producto}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create three test PlantillaWhatsapp records (numero_plantilla 1,2,3) for given product id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id_producto = $this->argument('id_producto');

        $producto = Productos::find($id_producto);
        if (! $producto) {
            $this->error("Producto with id {$id_producto} not found.");
            return 1;
        }

        $created = [];
        foreach ([1,2,3] as $numero) {
            $plantilla = PlantillaWhatsapp::firstOrCreate(
                ['id_producto' => $id_producto, 'numero_plantilla' => $numero],
                [
                    'nombre' => "Plantilla {$numero}",
                    'mensaje' => "Mensaje de prueba para producto {$id_producto} - variante {$numero}",
                    'imagen_url' => null,
                    'imagen_public_id' => null,
                    'created_by' => null,
                    'updated_by' => null,
                ]
            );

            $created[] = [
                'id_plantilla_whatsapp' => $plantilla->id_plantilla_whatsapp,
                'id_producto' => $plantilla->id_producto,
                'numero_plantilla' => $plantilla->numero_plantilla,
                'mensaje' => $plantilla->mensaje,
                'imagen_url' => $plantilla->imagen_url
            ];
        }

        $this->info(json_encode(['success' => true, 'data' => $created], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}
