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
            $table->unsignedBigInteger('id_plantilla_whatsapp')->nullable()->after('number_message');
            $table->string('message_id', 255)->nullable()->after('id_plantilla_whatsapp');
            $table->integer('attempts')->default(0)->after('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modal_wats', function (Blueprint $table) {
            $table->dropColumn(['id_plantilla_whatsapp', 'message_id', 'attempts']);
        });
    }
};
