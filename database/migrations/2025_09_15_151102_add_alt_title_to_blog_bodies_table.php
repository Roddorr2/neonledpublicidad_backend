<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_bodies', function (Blueprint $table) {
            $table->string('alt_image1', 255)->nullable()->after('url_image1');
            $table->string('title_image1', 255)->nullable()->after('alt_image1');
            $table->string('alt_image2', 255)->nullable()->after('url_image2');
            $table->string('title_image2', 255)->nullable()->after('alt_image2');
            $table->string('alt_image3', 255)->nullable()->after('url_image3');
            $table->string('title_image3', 255)->nullable()->after('alt_image3');
        
        });
    }

    public function down(): void
    {
        Schema::table('blog_bodies', function (Blueprint $table) {
            $table->dropColumn(['alt_image1', 'title_image1', 'alt_image2', 'title_image2', 'alt_image3', 'title_image3']);
        });
    }
};
