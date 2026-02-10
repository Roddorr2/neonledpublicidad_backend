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
        Schema::create('campanias_whatsapp', function (Blueprint $table) {
            $table->id('id_campania');
            $table->unsignedBigInteger('id_servicio');
            $table->text('parrafo');
            $table->string('imagen_url', 300);
            $table->enum('estado', ['pendiente', 'en_proceso', 'completada', 'cancelada', 'error'])->default('pendiente');
            $table->integer('total_destinatarios')->default(0);
            $table->integer('envios_exitosos')->default(0);
            $table->integer('envios_fallidos')->default(0);
            $table->integer('envios_pendientes')->default(0);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
            
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios')->onDelete('cascade');
            $table->index('id_servicio');
            $table->index('estado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campanias_whatsapp');
    }
};
