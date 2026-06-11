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
        if (Schema::hasTable('commend_tarjetas')) {
            return;
        }

        Schema::create('commend_tarjetas', function (Blueprint $table) {
            $table->id('id_commend_tarjeta');
            $table->string('titulo');
            $table->string('texto1');
            $table->string('texto2');
            $table->string('texto3')->nullable();
            $table->string('texto4')->nullable();
            $table->string('texto5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commend_tarjetas');
    }
};
