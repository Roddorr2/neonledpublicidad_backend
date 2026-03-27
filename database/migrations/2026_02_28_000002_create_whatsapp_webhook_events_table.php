<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chunk_id')->nullable();
            $table->unsignedBigInteger('id_modal_wat')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('status')->nullable();
            $table->json('raw')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->timestamps();

            $table->index('chunk_id');
            $table->index('id_modal_wat');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_webhook_events');
    }
};
