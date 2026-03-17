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
                if (Schema::hasTable('cards')) {
            return;
        }

Schema::create('cards', function (Blueprint $table) {
            $table->id('id_card');
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('public_image');
            $table->text('url_image')->nullable();
            $table->bigInteger('id_plantilla')->nullable();
            $table->foreignId('id_blog')->unique()->references('id_blog')->on('blogs')->onDelete('cascade');
            $table->foreignId('id_empleado')->references('id_empleado')->on('empleados')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
