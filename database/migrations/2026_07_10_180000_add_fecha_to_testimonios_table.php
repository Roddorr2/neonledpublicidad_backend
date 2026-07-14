<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonios', function (Blueprint $table) {
            // Fecha que se muestra en la card del carrusel (editable manualmente).
            // Si queda vacía, el frontend usa created_at como respaldo.
            $table->date('fecha')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('testimonios', function (Blueprint $table) {
            $table->dropColumn('fecha');
        });
    }
};