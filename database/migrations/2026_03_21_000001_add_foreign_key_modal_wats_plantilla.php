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
        if (! Schema::hasColumn('modal_wats', 'id_plantilla_whatsapp')) {
            Schema::table('modal_wats', function (Blueprint $table) {
                $table->unsignedBigInteger('id_plantilla_whatsapp')->nullable()->after('number_message');
            });
        }

        Schema::table('modal_wats', function (Blueprint $table) {
            $table->foreign('id_plantilla_whatsapp')
                ->references('id_plantilla_whatsapp')
                ->on('plantillas_whatsapp')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modal_wats', function (Blueprint $table) {
            $table->dropForeign(['id_plantilla_whatsapp']);
        });
    }
};
