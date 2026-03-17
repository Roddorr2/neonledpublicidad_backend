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
            if (! Schema::hasColumn('blog_heads', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }

            if (! Schema::hasColumn('blog_heads', 'meta_descripcion')) {
                $table->string('meta_descripcion')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_heads', function (Blueprint $table) {
            if (Schema::hasColumn('blog_heads', 'meta_title')) {
                $table->dropColumn('meta_title');
            }

            if (Schema::hasColumn('blog_heads', 'meta_descripcion')) {
                $table->dropColumn('meta_descripcion');
            }
        });
    }
};
