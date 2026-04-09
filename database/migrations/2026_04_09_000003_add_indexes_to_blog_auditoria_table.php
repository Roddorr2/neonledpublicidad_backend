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
        Schema::table('blog_auditoria', function (Blueprint $table) {
            $table->index(['accion', 'fecha_hora']);
            $table->index('id_blog');
            $table->index(['id_empleado', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_auditoria', function (Blueprint $table) {
            $table->dropIndex('blog_auditoria_accion_fecha_hora_index');
            $table->dropIndex('blog_auditoria_id_blog_index');
            $table->dropIndex('blog_auditoria_id_empleado_fecha_hora_index');
        });
    }
};
