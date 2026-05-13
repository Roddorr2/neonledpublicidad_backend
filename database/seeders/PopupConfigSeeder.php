<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopupConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $popupConfigs = [];

        for ($id = 1; $id <= 16; $id++) {
            $popupConfigs[] = [
                'id_producto' => $id,
                'title_text' => '¡SOLO POR HOY: ACCEDE A UNA ASESORÍA GRATIS!',
                'title_color' => '#FFFFFF',
                'button_text' => 'HAZLO YA',
                'button_color' => '#feb549',
                'service_color' => '#6f7cf9',
                'service_color_2' => '#9652f4',
                'gradient_direction' => 'to bottom',
                'trigger_time' => 3,
                'left_image_url' => null,
                'left_public_id' => null,
                'left_opacity' => 100,
                'left_alt' => null,
                'right_image_url' => null,
                'right_public_id' => null,
                'right_opacity' => 100,
                'right_alt' => null,
                'mobile_image_url' => null,
                'mobile_public_id' => null,
                'mobile_opacity' => 100,
                'mobile_alt' => null,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('popup_configs')->insert($popupConfigs);
    }
}