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
        Schema::table('blog_footers', function (Blueprint $table) {
        $table->string('alt_image1')->nullable();
        $table->string('title_image1')->nullable();
        $table->string('alt_image2')->nullable();
        $table->string('title_image2')->nullable();
        $table->string('alt_image3')->nullable();
        $table->string('title_image3')->nullable();

        $table->boolean('estado')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_footers', function (Blueprint $table) {
            $table->dropColumn([
                'alt_image1', 'title_image1',
                'alt_image2', 'title_image2',
                'alt_image3', 'title_image3',
                'estado'
            ]);
        });
    }
};
