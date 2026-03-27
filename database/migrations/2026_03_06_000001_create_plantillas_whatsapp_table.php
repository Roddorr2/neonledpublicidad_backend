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
        Schema::create('plantillas_whatsapp', function (Blueprint $table) {
            $table->id('id_plantilla_whatsapp');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->tinyInteger('numero_plantilla'); // 1..3
            $table->string('nombre')->nullable();
            $table->text('mensaje');
            $table->string('imagen_url', 500)->nullable();
            $table->string('imagen_public_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users','id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users','id')->nullOnDelete();
            $table->timestamps();
            $table->unique(['id_producto','numero_plantilla']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_whatsapp');
    }
};
