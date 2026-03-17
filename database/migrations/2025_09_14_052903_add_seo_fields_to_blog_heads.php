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
        Schema::table('blog_heads', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_heads', 'alt')) {
                $table->string('alt')->nullable();
            }

            if (! Schema::hasColumn('blog_heads', 'title')) {
                $table->string('title')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_heads', function (Blueprint $table) {
            if (Schema::hasColumn('blog_heads', 'alt')) {
                $table->dropColumn('alt');
            }

            if (Schema::hasColumn('blog_heads', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
