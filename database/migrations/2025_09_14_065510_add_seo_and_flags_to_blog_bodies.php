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
            if (! Schema::hasColumn('blog_bodies', 'alt_image1')) {
                $table->string('alt_image1')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'title_image1')) {
                $table->string('title_image1')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'alt_image2')) {
                $table->string('alt_image2')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'title_image2')) {
                $table->string('title_image2')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'alt_image3')) {
                $table->string('alt_image3')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'title_image3')) {
                $table->string('title_image3')->nullable();
            }
            if (! Schema::hasColumn('blog_bodies', 'flag_galeria')) {
                $table->boolean('flag_galeria')->default(1);
            }
            if (! Schema::hasColumn('blog_bodies', 'flag_consejos')) {
                $table->boolean('flag_consejos')->default(1);
            }
            if (! Schema::hasColumn('blog_bodies', 'flag_informacion')) {
                $table->boolean('flag_informacion')->default(1);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_bodies', function (Blueprint $table) {
            $columns = [
                'alt_image1', 'title_image1',
                'alt_image2', 'title_image2',
                'alt_image3', 'title_image3',
                'flag_galeria', 'flag_consejos', 'flag_informacion',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('blog_bodies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
