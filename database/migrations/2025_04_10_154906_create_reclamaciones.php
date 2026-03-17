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
                if (Schema::hasTable('reclamaciones')) {
            return;
        }

Schema::create('reclamaciones', function (Blueprint $table) {
            $table->id('id_reclamacion');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 191);
            $table->string('telefono', 15);
            $table->string('departamento', 250);
            $table->string('direccion', 250);
            $table->string('distrito', 250);
            $table->foreignId('id_servicio')->references('id_servicio')->on('servicios')->onDelete('cascade');
            $table->date('fechaIncidente');
            $table->decimal('montoReclamado', 15, 3);
            $table->text('descripcionServicio');
            $table->boolean('checkReclamoForm')->default(false);
            $table->boolean('aceptaPoliticaPrivacidad')->default(false);
            $table->timestamp('fechaReclamo')->useCurrent();
            $table->enum('estadoReclamo', ['PENDIENTE', 'ATENDIDO'])->default('PENDIENTE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamaciones');
    }
};
