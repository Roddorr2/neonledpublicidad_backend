<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('texto', 600);
            $table->unsignedTinyInteger('rating')->default(5); // 1 a 5
            $table->string('avatar_url')->nullable();
            $table->string('avatar_public_id')->nullable(); // para poder borrar en Cloudinary
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0); // menor número = sale primero en el carrusel
            $table->timestamps();

            $table->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonios');
    }
};