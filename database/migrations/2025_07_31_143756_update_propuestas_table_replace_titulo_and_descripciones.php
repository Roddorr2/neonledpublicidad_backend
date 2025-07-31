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
       Schema::table('propuestas', function (Blueprint $table) {
            $table->dropColumn([
                'titulo',
                'descripcion1',
                'descripcion2',
                'descripcion3',
                'descripcion4',
                'descripcion5',
                'descripcion6',
                'descripcion7',
                'descripcion8',
                'descripcion9',
                'descripcion10',
            ]);
            $table->string('nombre');
            $table->string('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propuestas', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'descripcion']);
            
            $table->string('titulo');
            $table->string('descripcion1');
            $table->string('descripcion2')->nullable();
            $table->string('descripcion3')->nullable();
            $table->string('descripcion4')->nullable();
            $table->string('descripcion5')->nullable();
            $table->string('descripcion6')->nullable();
            $table->string('descripcion7')->nullable();
            $table->string('descripcion8')->nullable();
            $table->string('descripcion9')->nullable();
            $table->string('descripcion10')->nullable();
        });
    }
};
