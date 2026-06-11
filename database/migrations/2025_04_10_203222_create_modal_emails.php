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
        if (Schema::hasTable('modal_emails')) {
            return;
        }

        Schema::create('modal_emails', function (Blueprint $table) {
            $table->id('id_modal_email');
            $table->boolean('estado')->default(0);
            $table->string('error', 500)->nullable();
            $table->foreignId('id_modalservicio')->constrained('modalservicios', 'id_modalservicio')->onDelete('cascade');
            $table->enum('number_message', [1, 2, 3]);
            $table->date('fecha')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modal_emails');
    }
};
