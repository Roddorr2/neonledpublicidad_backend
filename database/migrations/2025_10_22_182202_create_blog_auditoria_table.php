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
        Schema::create('blog_auditoria', function (Blueprint $table) {
            $table->id('id_blog_auditoria');
            $table->unsignedBigInteger('id_empleado');
            $table->unsignedBigInteger('id_blog');
            $table->enum('accion', ['CREAR', 'ACTUALIZAR', 'ELIMINAR']);
            $table->timestamp('fecha_hora')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_auditoria');
    }
};
