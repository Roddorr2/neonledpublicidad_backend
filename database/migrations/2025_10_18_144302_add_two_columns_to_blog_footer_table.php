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
            $table->string('keyword')->nullable()->default(null);
            $table->string('link')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_footers', function (Blueprint $table) {
            $table->dropColumn(['keyword', 'link']);
        });
    }
};
