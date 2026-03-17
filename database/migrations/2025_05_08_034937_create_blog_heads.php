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
                if (Schema::hasTable('blog_heads')) {
            return;
        }

Schema::create('blog_heads', function (Blueprint $table) {
            $table->id('id_blog_head');
            $table->string('titulo',50);
            $table->string('texto_frase',70);
            $table->string('texto_descripcion',120);
            $table->text('public_image');
            $table->text('url_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_heads');
    }
};
