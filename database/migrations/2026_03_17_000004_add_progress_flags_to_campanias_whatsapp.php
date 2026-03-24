<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            if (!Schema::hasColumn('campanias_whatsapp', 'progress_milestone')) {
                $table->unsignedTinyInteger('progress_milestone')->nullable()->after('envios_pendientes');
            }

            if (!Schema::hasColumn('campanias_whatsapp', 'progress_version')) {
                $table->unsignedInteger('progress_version')->default(0)->after('progress_milestone');
            }

            if (!Schema::hasColumn('campanias_whatsapp', 'progress_milestone_updated_at')) {
                $table->timestamp('progress_milestone_updated_at')->nullable()->after('progress_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campanias_whatsapp', function (Blueprint $table) {
            if (Schema::hasColumn('campanias_whatsapp', 'progress_milestone_updated_at')) {
                $table->dropColumn('progress_milestone_updated_at');
            }

            if (Schema::hasColumn('campanias_whatsapp', 'progress_version')) {
                $table->dropColumn('progress_version');
            }

            if (Schema::hasColumn('campanias_whatsapp', 'progress_milestone')) {
                $table->dropColumn('progress_milestone');
            }
        });
    }
};
