<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cloudinary_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('public_id')->unique();
            $table->string('secure_url', 1000)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cloudinary_uploads');
    }
};
