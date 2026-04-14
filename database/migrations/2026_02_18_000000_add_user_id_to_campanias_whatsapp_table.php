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
        if (Schema::hasColumn('campanias_whatsapp', 'user_id')) {
            return;
        }

        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id_servicio');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('campanias_whatsapp', 'user_id')) {
            return;
        }

        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // Ignore if FK does not exist in this environment.
            }

            try {
                $table->dropIndex(['user_id']);
            } catch (\Throwable $e) {
                // Ignore if index does not exist in this environment.
            }

            $table->dropColumn('user_id');
        });
    }
};
