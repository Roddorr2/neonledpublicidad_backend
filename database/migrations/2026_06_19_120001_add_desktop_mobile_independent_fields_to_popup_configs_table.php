<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separación completa Desktop / Mobile en popup_configs.
 *
 * Campos nuevos (todos con prefijo mobile_):
 *   - mobile_title_text        : texto principal propio de mobile
 *   - mobile_button_text       : texto del botón propio de mobile
 *   - mobile_trigger_time      : tiempo de aparición de mobile
 *   - mobile_title_color       : color del texto principal de mobile
 *   - mobile_button_color      : color del botón de mobile
 *   - mobile_service_color     : color de fondo 1 de mobile
 *   - mobile_service_color_2   : color de fondo 2 (degradado) de mobile
 *   - mobile_gradient_direction: dirección del degradado de mobile
 *
 * Los campos originales (sin prefijo) quedan como exclusivos de Desktop.
 * Al ejecutar up() se copian los valores actuales desktop → mobile para
 * no perder configuraciones existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_configs', function (Blueprint $table) {

            // ── Texto independiente ─────────────────────────────────────────
            $table->string('mobile_title_text', 80)
                  ->nullable()
                  ->after('button_text');

            $table->string('mobile_button_text', 25)
                  ->nullable()
                  ->after('mobile_title_text');

            // ── Tiempo de aparición ─────────────────────────────────────────
            $table->unsignedTinyInteger('mobile_trigger_time')
                  ->default(8)
                  ->after('trigger_time');

            // ── Colores de texto y botón ────────────────────────────────────
            $table->string('mobile_title_color', 7)
                  ->default('#FFFFFF')
                  ->after('mobile_trigger_time');

            $table->string('mobile_button_color', 7)
                  ->default('#F97316')
                  ->after('mobile_title_color');

            // ── Colores de fondo / degradado ────────────────────────────────
            $table->string('mobile_service_color', 7)
                  ->default('#5966f5')
                  ->after('mobile_alt');

            $table->string('mobile_service_color_2', 7)
                  ->nullable()
                  ->after('mobile_service_color');

            $table->string('mobile_gradient_direction', 20)
                  ->default('to bottom')
                  ->after('mobile_service_color_2');
        });

        // Copiar valores actuales de desktop → mobile para no romper
        // configuraciones ya guardadas.
        \DB::table('popup_configs')->update([
            'mobile_title_text'         => \DB::raw('title_text'),
            'mobile_button_text'        => \DB::raw('button_text'),
            'mobile_trigger_time'       => \DB::raw('trigger_time'),
            'mobile_title_color'        => \DB::raw('title_color'),
            'mobile_button_color'       => \DB::raw('button_color'),
            'mobile_service_color'      => \DB::raw('service_color'),
            'mobile_service_color_2'    => \DB::raw('service_color_2'),
            'mobile_gradient_direction' => \DB::raw('gradient_direction'),
        ]);
    }

    public function down(): void
    {
        Schema::table('popup_configs', function (Blueprint $table) {
            $table->dropColumn([
                'mobile_title_text',
                'mobile_button_text',
                'mobile_trigger_time',
                'mobile_title_color',
                'mobile_button_color',
                'mobile_service_color',
                'mobile_service_color_2',
                'mobile_gradient_direction',
            ]);
        });
    }
};