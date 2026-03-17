<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('cards', 'estado_publicacion')) {
            Schema::table('cards', function (Blueprint $table) {
                $table->boolean('estado_publicacion')->default(false)->after('id_empleado')->nullable();
            });
        }

        if (Schema::hasColumn('cards', 'estado_publicacion')) {
            DB::table('cards')->update(['estado_publicacion' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cards', 'estado_publicacion')) {
            Schema::table('cards', function (Blueprint $table) {
                $table->dropColumn('estado_publicacion');
            });
        }
    }
};
