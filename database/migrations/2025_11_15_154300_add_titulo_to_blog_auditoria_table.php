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
        Schema::table('blog_auditoria', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_auditoria', 'titulo')) {
                $table->string('titulo')->after('accion')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_auditoria', function (Blueprint $table) {
            if (Schema::hasColumn('blog_auditoria', 'titulo')) {
                $table->dropColumn('titulo');
            }
        });
    }
};
