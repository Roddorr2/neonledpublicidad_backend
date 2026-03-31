<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            // Intentar eliminar FK antigua si existe
            try {
                $table->dropForeign(['id_servicio']);
            } catch (\Exception $e) {
                // FK puede no existir aún; ignorar
            }

            // Crear FK hacia productos.id_producto
            $table->foreign('id_servicio')
                ->references('id_producto')
                ->on('productos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_servicio']);
            } catch (\Exception $e) {
                // ignore
            }

            // Restaurar FK hacia servicios.id_servicio
            $table->foreign('id_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->onDelete('cascade');
        });
    }
};
