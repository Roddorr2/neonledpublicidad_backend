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
            $table->unsignedBigInteger('reservation_id')->nullable()->after('number_message');
            $table->timestamp('scheduled_at')->nullable()->after('reservation_id');
            $table->string('flow_type', 32)->nullable()->after('scheduled_at');
            $table->unsignedBigInteger('campaign_id')->nullable()->after('flow_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modal_wats', function (Blueprint $table) {
            $table->dropColumn(['reservation_id', 'scheduled_at', 'flow_type', 'campaign_id']);
        });
    }
};
