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
        Schema::create('popup_configs', function (Blueprint $table) {
            $table->id('id_popup_config');
            $table->unsignedBigInteger('id_producto')->unique();
            $table->string('title_text', 80);
            $table->string('title_color', 7);
            $table->string('button_text', 25);
            $table->string('button_color', 7);
            $table->string('service_color', 7);
            $table->string('service_color_2', 7)->nullable();
            $table->string('gradient_direction', 20);
            $table->tinyInteger('trigger_time')->unsigned();
            $table->string('left_image_url', 500)->nullable();
            $table->string('left_public_id', 500)->nullable();
            $table->tinyInteger('left_opacity')->unsigned()->default(100);
            $table->string('left_alt', 255)->nullable();
            $table->string('right_image_url', 500)->nullable();
            $table->string('right_public_id', 500)->nullable();
            $table->tinyInteger('right_opacity')->unsigned()->default(100);
            $table->string('right_alt', 255)->nullable();
            $table->string('mobile_image_url', 500)->nullable();
            $table->string('mobile_public_id', 500)->nullable();
            $table->tinyInteger('mobile_opacity')->unsigned()->default(100);
            $table->string('mobile_alt', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_configs');
    }
};
