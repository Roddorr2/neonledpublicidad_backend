<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modalservicios', function (Blueprint $table) {
            $table->dropForeign(['id_servicio']);
            $table->renameColumn('id_servicio', 'id_producto');
        });

        Schema::table('modalservicios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto')->change();
        });
    }

    public function down(): void
    {
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
