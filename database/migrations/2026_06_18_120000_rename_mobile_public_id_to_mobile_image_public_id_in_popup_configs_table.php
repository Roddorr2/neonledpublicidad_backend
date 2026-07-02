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
        Schema::table('popup_configs', function (Blueprint $table) {
            $table->renameColumn('mobile_public_id', 'mobile_image_public_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('popup_configs', function (Blueprint $table) {
            $table->renameColumn('mobile_image_public_id', 'mobile_public_id');
        });
    }
};