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
        Schema::table('modal_wats', function (Blueprint $table) {
            if (! Schema::hasColumn('modal_wats', 'message_id')) {
                $table->string('message_id')->nullable()->after('fecha');
            }
            if (! Schema::hasColumn('modal_wats', 'attempts')) {
                $table->integer('attempts')->default(0)->after('message_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modal_wats', function (Blueprint $table) {
            if (Schema::hasColumn('modal_wats', 'attempts')) {
                $table->dropColumn('attempts');
            }
            if (Schema::hasColumn('modal_wats', 'message_id')) {
                $table->dropColumn('message_id');
            }
        });
    }
};
