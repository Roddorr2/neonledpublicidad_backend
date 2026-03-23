<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            
            $table->index('titulo');
            $table->index('descripcion', 255); // Limitar a 255 caracteres 
            $table->index(['estado_publicacion', 'titulo']);
        });

        // MySQL/MariaDB requiere prefijo de longitud para indexar columnas TEXT.
        if (! $this->indexExists('cards', 'cards_descripcion_index')) {
            DB::statement('ALTER TABLE `cards` ADD INDEX `cards_descripcion_index` (`descripcion`(255))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            
            $table->dropIndex('cards_titulo_index');
            $table->dropIndex('cards_descripcion_index');
            $table->dropIndex('cards_estado_publicacion_titulo_index');
        });

        if ($this->indexExists('cards', 'cards_descripcion_index')) {
            DB::statement('ALTER TABLE `cards` DROP INDEX `cards_descripcion_index`');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        );

        return $result !== null;
    }
};
