<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CampaignRoutesSecurityTest extends TestCase
{
    public function test_campaign_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/whatsapp/campaigns');

        $response->assertStatus(401);
    }

    public function test_campaign_preview_rejects_invalid_service_before_controller(): void
    {
        $response = $this->getJson('/api/whatsapp/campaign/preview/invalid');

        $response->assertStatus(404);
    }

    public function test_webhook_route_is_public_but_requires_api_key_in_controller(): void
    {
        $response = $this->postJson('/api/whatsapp/webhook/status', [
            'status' => 'sent',
            'campaign_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_route_has_rate_limit_headers(): void
    {
        $response = $this->postJson('/api/whatsapp/webhook/status', [
            'status' => 'sent',
            'campaign_id' => 1,
        ]);

        $response->assertStatus(401);
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    public function test_service_template_routes_are_public_and_guarded_by_api_key(): void
    {
        $whatsapp = $this->getJson('/api/plantillas/whatsapp/1/1');
        $email = $this->getJson('/api/plantillas/email/1/1');

        $whatsapp->assertStatus(401);
        $email->assertStatus(401);
    }
}
