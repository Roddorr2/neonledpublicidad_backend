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
                if (Schema::hasTable('propuestas')) {
            return;
        }

Schema::create('propuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cliente')->references('id')->on('clientes')->onDelete('cascade');
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propuesta');
    }
};
