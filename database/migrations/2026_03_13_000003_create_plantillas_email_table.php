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
        Schema::create('plantillas_email', function (Blueprint $table) {
            $table->id('id_plantilla_email');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->tinyInteger('numero_plantilla'); // 1..3
            $table->string('nombre')->nullable();
            $table->string('asunto');
            $table->string('encabezado');
            $table->string('imagen_url', 500)->nullable();
            $table->text('mensaje');
            $table->string('mensaje_boton')->nullable();
            $table->string('url_boton')->nullable();
            $table->text('footer')->nullable();
            $table->string('red_facebook')->nullable();
            $table->string('red_tiktok')->nullable();
            $table->string('red_instagram')->nullable();
            $table->string('red_linkedin')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();

            $table->unique(['id_producto', 'numero_plantilla']);
            $table->index('id_producto');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_email');
    }
};
