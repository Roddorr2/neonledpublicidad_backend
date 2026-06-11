<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('modalservicios', 'id_producto')) {
            return;
        }

        if (! Schema::hasColumn('modalservicios', 'id_servicio')) {
            return;
        }

        Schema::table('modalservicios', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_servicio']);
            } catch (Throwable $e) {
                // Ignore if FK does not exist in this environment.
            }
            $table->renameColumn('id_servicio', 'id_producto');
        });

        Schema::table('modalservicios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto')->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('modalservicios', 'id_servicio')) {
            return;
        }

        if (! Schema::hasColumn('modalservicios', 'id_producto')) {
            return;
        }

        Schema::table('modalservicios', function (Blueprint $table) {
            $table->renameColumn('id_producto', 'id_servicio');
            $table->unsignedBigInteger('id_servicio')->change();
            $table->foreign('id_servicio')
                ->references('id_servicio')
                ->on('servicios')
                ->onDelete('cascade');
        });
    }
};
