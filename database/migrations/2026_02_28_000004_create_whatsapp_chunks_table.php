<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_chunks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('parent_chunk_id')->nullable();
            $table->integer('chunk_index')->default(0);
            $table->integer('recipients_count')->default(0);
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->string('status')->default('pending');
            $table->json('meta')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('campaign_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_chunks');
    }
};
