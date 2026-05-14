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
            if (! Schema::hasColumn('productos', 'id_empleado')) {
                $table->foreignId('id_empleado')->nullable()->after('id_producto')->constrained('empleados', 'id_empleado')->onDelete('set null');
            }

            if (! Schema::hasColumn('productos', 'path_main')) {
                $table->string('path_main')->nullable();
            }
            if (! Schema::hasColumn('productos', 'path_background')) {
                $table->string('path_background')->nullable();
            }

            if (! Schema::hasColumn('productos', 'path1')) {
                $table->string('path1')->nullable();
            }
            if (! Schema::hasColumn('productos', 'tituloimg1')) {
                $table->string('tituloimg1')->nullable();
            }
            if (! Schema::hasColumn('productos', 'descripcionimg1')) {
                $table->text('descripcionimg1')->nullable();
            }
            if (! Schema::hasColumn('productos', 'path2')) {
                $table->string('path2')->nullable();
            }
            if (! Schema::hasColumn('productos', 'tituloimg2')) {
                $table->string('tituloimg2')->nullable();
            }
            if (! Schema::hasColumn('productos', 'descripcionimg2')) {
                $table->text('descripcionimg2')->nullable();
            }
            if (! Schema::hasColumn('productos', 'path3')) {
                $table->string('path3')->nullable();
            }
            if (! Schema::hasColumn('productos', 'tituloimg3')) {
                $table->string('tituloimg3')->nullable();
            }
            if (! Schema::hasColumn('productos', 'descripcionimg3')) {
                $table->text('descripcionimg3')->nullable();
            }

            if (! Schema::hasColumn('productos', 'caracteristicas_descrip')) {
                $table->text('caracteristicas_descrip')->nullable();
            }
            if (! Schema::hasColumn('productos', 'ventajas_descrip')) {
                $table->text('ventajas_descrip')->nullable();
            }
            if (! Schema::hasColumn('productos', 'consumoenergetico_descrip')) {
                $table->text('consumoenergetico_descrip')->nullable();
            }
            if (! Schema::hasColumn('productos', 'iluminacion_descrip')) {
                $table->text('iluminacion_descrip')->nullable();
            }
            if (! Schema::hasColumn('productos', 'durabilidad_descrip')) {
                $table->text('durabilidad_descrip')->nullable();
            }

            if (! Schema::hasColumn('productos', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('productos', 'estado')) {
                $table->tinyInteger('estado')->default(1)->comment('1=activo, 0=inactivo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'id_empleado')) {
                try {
                    $table->dropForeign(['id_empleado']);
                } catch (Throwable $e) {
                    // Ignore when foreign key was not created in this environment.
                }
                $table->dropColumn('id_empleado');
            }

            $columns = [
                'path_main',
                'path_background',
                'path1', 'tituloimg1', 'descripcionimg1',
                'path2', 'tituloimg2', 'descripcionimg2',
                'path3', 'tituloimg3', 'descripcionimg3',
                'caracteristicas_descrip', 'ventajas_descrip',
                'consumoenergetico_descrip', 'iluminacion_descrip', 'durabilidad_descrip',
                'created_at',
                'estado',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('productos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
