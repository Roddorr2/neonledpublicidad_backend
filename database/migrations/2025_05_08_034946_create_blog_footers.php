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
        if (Schema::hasTable('blog_footers')) {
            return;
        }

        Schema::create('blog_footers', function (Blueprint $table) {
            $table->id('id_blog_footer');
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('public_image1')->nullable();
            $table->text('url_image1')->nullable();
            $table->text('public_image2')->nullable();
            $table->text('url_image2')->nullable();
            $table->text('public_image3')->nullable();
            $table->text('url_image3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_footers');
    }
};
