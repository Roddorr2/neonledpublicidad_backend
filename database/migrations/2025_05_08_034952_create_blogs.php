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
        if (Schema::hasTable('blogs')) {
            return;
        }

        Schema::create('blogs', function (Blueprint $table) {
            $table->id('id_blog');
            $table->foreignId('id_blog_head')->unique()->references('id_blog_head')->on('blog_heads')->onDelete('cascade');
            $table->foreignId('id_blog_body')->unique()->references('id_blog_body')->on('blog_bodies')->onDelete('cascade');
            $table->foreignId('id_blog_footer')->unique()->references('id_blog_footer')->on('blog_footers')->onDelete('cascade');
            $table->timestamp('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
