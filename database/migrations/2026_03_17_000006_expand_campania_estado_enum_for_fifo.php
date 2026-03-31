<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE campanias_whatsapp MODIFY COLUMN estado ENUM('borrador','pendiente','en_proceso','pausada_hasta_mañana','pausada_fuera_horario','completada','cancelada','error') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE campanias_whatsapp MODIFY COLUMN estado ENUM('pendiente','en_proceso','completada','cancelada','error') NOT NULL DEFAULT 'pendiente'");
    }
};
