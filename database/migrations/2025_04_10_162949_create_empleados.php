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
                if (Schema::hasTable('empleados')) {
            return;
        }

Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado'); 
            $table->string('nombre'); 
            $table->string('apellido'); 
            $table->string('email')->unique(); 
            $table->string('dni')->unique();
            $table->string('telefono')->nullable(); 
            $table->string('imagen_perfil')->nullable();
            $table->string('imagen_perfil_url')->nullable();
            $table->foreignId('id_user')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('id_rol')->nullable()->references('id_rol')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
