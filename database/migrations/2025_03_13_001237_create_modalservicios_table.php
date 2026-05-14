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
        if (Schema::hasTable('modalservicios')) {
            return;
        }

        Schema::create('modalservicios', function (Blueprint $table) {
            $table->id('id_modalservicio');
            $table->string('nombre', 100);
            $table->string('telefono', 9);
            $table->string('correo', 200);
            $table->foreignId('id_servicio')->references('id_servicio')->on('servicios')->onDelete('cascade');
            $table->boolean('estado')->nullable()->default(1);
            $table->timestamp('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modalservicios');
    }
};
