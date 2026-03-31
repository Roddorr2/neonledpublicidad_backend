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
            if (! Schema::hasColumn('campanias_whatsapp', 'envios_pendientes')) {
                $table->integer('envios_pendientes')->default(0)->after('envios_fallidos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            if (Schema::hasColumn('campanias_whatsapp', 'envios_pendientes')) {
                $table->dropColumn('envios_pendientes');
            }
        });
    }
};
