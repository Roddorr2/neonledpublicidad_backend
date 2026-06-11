<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_configs', function (Blueprint $table) {
            $table->enum('trigger_type', ['time', 'click'])->default('time')->after('trigger_time');
            
            $table->enum('layout', ['left-image', 'right-image'])->default('left-image')->after('trigger_type');
            
            $table->boolean('show_logo')->default(true)->after('layout');
            
            $table->string('left_text', 255)->nullable()->after('show_logo');
        });
    }

    public function down(): void
    {
        Schema::table('popup_configs', function (Blueprint $table) {
            $table->dropColumn([
                'trigger_type',
                'layout',
                'show_logo',
                'left_text',
            ]);
        });
    }
};