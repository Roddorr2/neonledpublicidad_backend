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
                if (Schema::hasTable('tarjetas')) {
            return;
        }

Schema::create('tarjetas', function (Blueprint $table) {
            $table->id('id_tarjeta');
            $table->string('titulo');
            $table->text('descripcion');
            $table->foreignId('id_blog_body')->references('id_blog_body')->on('blog_bodies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_tarjetas');
    }
};
