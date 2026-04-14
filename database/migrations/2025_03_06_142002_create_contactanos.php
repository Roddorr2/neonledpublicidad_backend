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
        if (Schema::hasTable('contactanos')) {
            return;
        }

        Schema::create('contactanos', function (Blueprint $table) {
            $table->id("id_contactanos");
            $table->string("nombre", 100);
            $table->string("apellido", 100);
            $table->string("telefono", 15);
            $table->string("distrito", 100);
            $table->string("email", 100);
            $table->enum('detalle_reclamacion', ['CONSULTA', 'RECLAMO', 'SUGERENCIA'])->default('CONSULTA');
            $table->text("mensaje");
            $table->boolean("estado")->default(0);
            $table->timestamp("fecha_hora");
            $table->timestamp("fecha_hora_actualizacion")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactanos');
    }
};
