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
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('id_empleado')->nullable()->after('id_producto')->constrained('empleados', 'id_empleado')->onDelete('set null');

            $table->string('path_main')->nullable();
            $table->string('path_background')->nullable();

            $table->string('path1')->nullable();
            $table->string('tituloimg1')->nullable();
            $table->text('descripcionimg1')->nullable();
            $table->string('path2')->nullable();
            $table->string('tituloimg2')->nullable();
            $table->text('descripcionimg2')->nullable();
            $table->string('path3')->nullable();
            $table->string('tituloimg3')->nullable();
            $table->text('descripcionimg3')->nullable();


            $table->text('caracteristicas_descrip')->nullable();
            $table->text('ventajas_descrip')->nullable();
            $table->text('consumoenergetico_descrip')->nullable();
            $table->text('iluminacion_descrip')->nullable();
            $table->text('durabilidad_descrip')->nullable();

            $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['id_empleado']);

            $table->dropColumn([
                'id_empleado',
                'path_main',
                'path_background',
                'path1','tituloimg1','descripcionimg1',
                'path2','tituloimg2','descripcionimg2',
                'path3','tituloimg3','descripcionimg3',
                'caracteristicas_descrip','ventajas_descrip',
                'consumoenergetico_descrip','iluminacion_descrip','durabilidad_descrip',
                'estado'
            ]);
        });
    }
};
