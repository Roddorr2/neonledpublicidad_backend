<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('blog_heads', function (Blueprint $table) {
            $table->string('alt_image', 255)->nullable()->after('url_image');
            $table->string('title_image', 255)->nullable()->after('alt_image');
        });
    }

    
    public function down(): void
    {
        Schema::table('blog_heads', function (Blueprint $table) {
            $table->dropColumn(['alt_image', 'title_image']);
        });
    }
};
