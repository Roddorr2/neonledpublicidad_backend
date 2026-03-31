<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_webhook_events', 'flow')) {
                $table->string('flow')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_webhook_events', 'flow')) {
                $table->dropColumn('flow');
            }
        });
    }
};
