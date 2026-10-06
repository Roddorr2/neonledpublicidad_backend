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
        Schema::table('reclamaciones', function (Blueprint $table) {
            $table->string('tipoDocumento', 20)->nullable()->after('apellido');
            $table->string('dni', 15)->nullable()->after('tipoDocumento');
            $table->string('tipoReclamo')->nullable()->after('id_servicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reclamaciones', function (Blueprint $table) {
            $table->dropColumn(['tipoDocumento', 'dni', 'tipoReclamo']);
        });
    }
};
