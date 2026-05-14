<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add FK from whatsapp_chunks.campaign_id -> campanias_whatsapp.id_campania
        try {
            Schema::table('whatsapp_chunks', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_chunks', 'campaign_id')) {
                    $table->foreign('campaign_id', 'fk_whatsapp_chunks_campaign')
                        ->references('id_campania')->on('campanias_whatsapp')
                        ->onDelete('restrict');
                }
                if (Schema::hasColumn('whatsapp_chunks', 'reservation_id')) {
                    $table->foreign('reservation_id', 'fk_whatsapp_chunks_reservation')
                        ->references('id')->on('whatsapp_campaign_reservations')
                        ->onDelete('set null');
                }
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not add FK on whatsapp_chunks: ' . $e->getMessage());
        }

        // Add FK from whatsapp_campaign_reservations.campaign_id -> campanias_whatsapp.id_campania
        try {
            Schema::table('whatsapp_campaign_reservations', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_campaign_reservations', 'campaign_id')) {
                    $table->foreign('campaign_id', 'fk_whatsapp_reservations_campaign')
                        ->references('id_campania')->on('campanias_whatsapp')
                        ->onDelete('set null');

                    // Unique index on (campaign_id, date) to avoid duplicate reservations for same campaign/day
                    $table->unique(['campaign_id', 'date'], 'uq_whatsapp_reservations_campaign_date');
                }
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not add FK/index on whatsapp_campaign_reservations: ' . $e->getMessage());
        }

        // Add FKs on whatsapp_webhook_events
        try {
            Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_webhook_events', 'chunk_id')) {
                    $table->foreign('chunk_id', 'fk_whatsapp_events_chunk')
                        ->references('id')->on('whatsapp_chunks')
                        ->onDelete('set null');
                }
                if (Schema::hasColumn('whatsapp_webhook_events', 'campania_id')) {
                    $table->foreign('campania_id', 'fk_whatsapp_events_campania')
                        ->references('id_campania')->on('campanias_whatsapp')
                        ->onDelete('set null');
                }
                if (Schema::hasColumn('whatsapp_webhook_events', 'id_modal_wat')) {
                    $table->foreign('id_modal_wat', 'fk_whatsapp_events_modal_wat')
                        ->references('id_modal_wat')->on('modal_wats')
                        ->onDelete('set null');
                }
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not add FK on whatsapp_webhook_events: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Drop FKs if exist
        try {
            Schema::table('whatsapp_webhook_events', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                // Attempt to drop by name; failures are caught
                $table->dropForeign('fk_whatsapp_events_chunk');
                $table->dropForeign('fk_whatsapp_events_campania');
                $table->dropForeign('fk_whatsapp_events_modal_wat');
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not drop FKs on whatsapp_webhook_events: ' . $e->getMessage());
        }

        try {
            Schema::table('whatsapp_campaign_reservations', function (Blueprint $table) {
                $table->dropUnique('uq_whatsapp_reservations_campaign_date');
                $table->dropForeign('fk_whatsapp_reservations_campaign');
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not drop FK/index on whatsapp_campaign_reservations: ' . $e->getMessage());
        }

        try {
            Schema::table('whatsapp_chunks', function (Blueprint $table) {
                $table->dropForeign('fk_whatsapp_chunks_campaign');
                $table->dropForeign('fk_whatsapp_chunks_reservation');
            });
        } catch (Exception $e) {
            DB::statement('-- warning: could not drop FKs on whatsapp_chunks: ' . $e->getMessage());
        }
    }
};
