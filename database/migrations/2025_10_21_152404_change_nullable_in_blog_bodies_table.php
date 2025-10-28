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
        Schema::table('blog_bodies', function (Blueprint $table) {
            $table->text('public_image1')->nullable()->change();
            $table->text('public_image2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_bodies', function (Blueprint $table) {
            $table->text('public_image1')->nullable(false)->change();
            $table->text('public_image2')->nullable(false)->change();
        });
    }
};
