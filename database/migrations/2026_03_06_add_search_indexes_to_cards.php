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
        Schema::table('cards', function (Blueprint $table) {
            // Agregar índices para optimizar búsquedas de texto
            // Necesarios para que las consultas LIKE sean eficientes
            $table->index('titulo');
            $table->index('descripcion', 255); // Limitar a 255 caracteres para mejor performance
            // Índice compuesto para búsquedas por estado + título
            $table->index(['estado_publicacion', 'titulo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Eliminar índices si se revierte la migración
            $table->dropIndex('cards_titulo_index');
            $table->dropIndex('cards_descripcion_index');
            $table->dropIndex('cards_estado_publicacion_titulo_index');
        });
    }
};
