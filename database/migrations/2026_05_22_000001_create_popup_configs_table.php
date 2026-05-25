<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('popup_configs')) {
            return;
        }

        Schema::create('popup_configs', function (Blueprint $table) {
            $table->id('id_popup_config');

            // FK a productos (un popup por producto — unique)
            $table->unsignedBigInteger('id_producto')->unique();
            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('cascade');

            // Texto y colores
            $table->string('title_text', 80)->default('OBTÉN UNA COTIZACIÓN ¡GRATIS!');
            $table->string('title_color', 7)->default('#FFFFFF');
            $table->string('button_text', 25)->default('HAZLO YA');
            $table->string('button_color', 7)->default('#F97316');
            $table->string('service_color', 7)->default('#1E3A5F');
            $table->string('service_color_2', 7)->nullable();
            $table->string('gradient_direction', 20)->default('to bottom');

            // Timer (segundos antes de aparecer)
            $table->tinyInteger('trigger_time')->unsigned()->default(8);

            // Imagen izquierda (columna lateral desktop)
            $table->string('left_image_url', 500)->nullable();
            $table->string('left_image_public_id', 255)->nullable();
            $table->tinyInteger('left_opacity')->unsigned()->default(85);
            $table->string('left_alt', 255)->nullable();

            // Imagen derecha (fondo completo desktop)
            $table->string('right_image_url', 500)->nullable();
            $table->string('right_image_public_id', 255)->nullable();
            $table->tinyInteger('right_opacity')->unsigned()->default(100);
            $table->string('right_alt', 255)->nullable();

            // Imagen mobile (fondo completo mobile)
            $table->string('mobile_image_url', 500)->nullable();
            $table->string('mobile_image_public_id', 255)->nullable();
            $table->tinyInteger('mobile_opacity')->unsigned()->default(100);
            $table->string('mobile_alt', 255)->nullable();

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Índice para búsqueda por producto
            $table->index('id_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_configs');
    }
};