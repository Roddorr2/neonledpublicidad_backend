<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Services\PlannerService;

class WhatsappChunksReservationTest extends TestCase
{
    /**
     * Ensure planner reserves only up to daily limit and creates chunks accordingly.
     */
    public function test_planner_respects_daily_limit_and_creates_chunks(): void
    {
        // Ensure migrations are applied (non-destructive)
        Artisan::call('migrate');

        // Set a small daily limit for the test
        config(['whatsapp.daily_limit' => 25]);

        // Simulate controller flow: create many modalservicios rows for product id 1
        $productId = 1;
        $phones = [];
        for ($i = 0; $i < 60; $i++) {
            // modalservicios.telefono column is length 9; use 9-digit numbers
            $phones[] = str_pad((string)($i + 900000000), 9, '0', STR_PAD_LEFT);
        }

        // Insert modalservicios rows (some duplicates to test unique filter)
        foreach ($phones as $idx => $phone) {
            DB::table('modalservicios')->insert([
                'nombre' => 'User ' . ($idx + 1),
                'telefono' => $phone,
                'correo' => 'user' . ($idx + 1) . '@example.test',
                'id_producto' => $productId,
                'estado' => 1,
            ]);
        }

        // Now, simulate the controller: query destinatarios for the product
        $destinatarios = DB::table('modalservicios')
            ->where('id_producto', $productId)
            ->where('estado', 1)
            ->select('id_modalservicio', 'nombre', 'telefono', 'id_producto')
            ->orderByDesc('id_modalservicio')
            ->get()
            ->unique('telefono')
            ->values();

        $totalDestinatarios = $destinatarios->count();

        // Create campaign via controller-like data (image url provided)
        $campaignId = DB::table('campanias_whatsapp')->insertGetId([
            'id_servicio' => $productId,
            'user_id' => null,
            'parrafo' => 'Test campaign',
            'imagen_url' => 'https://example.com/test.jpg',
            'estado' => 'pendiente',
            'total_destinatarios' => $totalDestinatarios,
            'envios_pendientes' => $totalDestinatarios,
            'fecha_inicio' => now(),
            'fecha_fin' => null,
        ]);

        $recipients = $destinatarios->toArray();

        // Use small chunk size to exercise batching
        $chunks = PlannerService::planCampaign($campaignId, $recipients, 10, 1);

        // Sum total recipients created in chunks
        $total = 0;
        foreach ($chunks as $c) {
            $total += $c->recipients_count;
        }

        // Should not exceed daily limit (25)
        $this->assertLessThanOrEqual(25, $total);
        $this->assertEquals(25, $total);

        // Campaign pending counter should remain equal to totalDestinatarios (controller set it)
        $camp = DB::table('campanias_whatsapp')->where('id_campania', $campaignId)->first();
        $this->assertNotNull($camp);
        $this->assertEquals($totalDestinatarios, $camp->envios_pendientes);

        // Reservation row exists and has reserved_slots = 25
        $res = DB::table('whatsapp_campaign_reservations')->where('campaign_id', $campaignId)->where('date', now()->startOfDay()->toDateString())->first();
        $this->assertNotNull($res);
        $this->assertEquals(25, $res->reserved_slots);

        // Additional chunk-level assertions: campaign_id, sequential chunk_index, meta.recipients shape
        $this->assertNotEmpty($chunks);

        $expectedChunkIndex = 1;
        $sumRecipients = 0;
        foreach ($chunks as $chunk) {
            $this->assertEquals($campaignId, $chunk->campaign_id);
            $this->assertEquals($expectedChunkIndex, $chunk->chunk_index);
            $this->assertIsArray($chunk->meta);
            $recips = data_get($chunk->meta, 'recipients', []);
            $this->assertNotEmpty($recips);
            foreach ($recips as $r) {
                $this->assertArrayHasKey('id_modalservicio', (array)$r);
                $this->assertArrayHasKey('telefono', (array)$r);
            }
            $sumRecipients += $chunk->recipients_count;
            $expectedChunkIndex++;
        }

        $this->assertEquals($total, $sumRecipients);
    }
}
