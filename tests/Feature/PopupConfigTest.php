<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\PopupConfig;
use App\Models\Subservicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class PopupConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            \Database\Seeders\ProductoSeeder::class,
            \Database\Seeders\PermisosSeeder::class,
        ]);
    }

    private function createUserWithRole($roleName)
    {
        $rol = Rol::where('nombre', $roleName)->first();
        $user = User::factory()->create();
        $user->roles()->attach($rol);
        return $user;
    }

    /** @test */
    public function it_returns_401_without_token()
    {
        $response = $this->getJson('/api/popup-configs');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_403_with_insufficient_permissions()
    {
        $user = $this->createUserWithRole('ventas');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/popup-configs');
        $response->assertStatus(403);
    }

    /** @test */
    public function it_returns_200_for_index_with_proper_permissions()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/popup-configs');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_200_for_show_with_proper_permissions()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $config = PopupConfig::first();

        $response = $this->getJson("/api/popup-configs/{$config->id_popup_config}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_200_for_show_by_producto()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/popup-configs/producto/1');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_404_for_show_by_producto_without_config()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        // Assume producto 999 doesn't have config
        $response = $this->getJson('/api/popup-configs/producto/999');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_creates_config_successfully()
    {
        Storage::fake('public');

        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $data = [
            'id_servicio' => 1,
            'title_text' => 'Test Title',
            'title_color' => '#FFFFFF',
            'button_text' => 'Test Button',
            'button_color' => '#000000',
            'service_color' => '#FF0000',
            'service_color_2' => '#00FF00',
            'gradient_direction' => 'to right',
            'trigger_time' => 5,
            'left_opacity' => 80,
            'right_opacity' => 90,
            'mobile_opacity' => 70,
        ];

        $response = $this->postJson('/api/popup-configs', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('popup_configs', $data);
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/popup-configs', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_servicio', 'title_text', 'button_text', 'service_color', 'title_color', 'button_color', 'gradient_direction', 'trigger_time']);
    }

    /** @test */
    public function it_validates_title_text_length()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $data = [
            'id_servicio' => 1,
            'title_text' => str_repeat('a', 81), // too long
            'title_color' => '#FFFFFF',
            'button_text' => 'Test',
            'button_color' => '#000000',
            'service_color' => '#FF0000',
            'gradient_direction' => 'to right',
            'trigger_time' => 5,
        ];

        $response = $this->postJson('/api/popup-configs', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title_text']);
    }

    /** @test */
    public function it_validates_color_format()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $data = [
            'id_servicio' => 1,
            'title_text' => 'Test',
            'title_color' => 'invalid',
            'button_text' => 'Test',
            'button_color' => '#000000',
            'service_color' => '#FF0000',
            'gradient_direction' => 'to right',
            'trigger_time' => 5,
        ];

        $response = $this->postJson('/api/popup-configs', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title_color']);
    }

    /** @test */
    public function it_validates_trigger_time_values()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $data = [
            'id_servicio' => 1,
            'title_text' => 'Test',
            'title_color' => '#FFFFFF',
            'button_text' => 'Test',
            'button_color' => '#000000',
            'service_color' => '#FF0000',
            'gradient_direction' => 'to right',
            'trigger_time' => 10, // invalid
        ];

        $response = $this->postJson('/api/popup-configs', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['trigger_time']);
    }

    /** @test */
    public function it_updates_config_successfully()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $config = PopupConfig::first();

        $data = [
            'title_text' => 'Updated Title',
            'trigger_time' => 8,
        ];

        $response = $this->postJson("/api/popup-configs/{$config->id_popup_config}/actualizar", $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('popup_configs', array_merge($config->toArray(), $data));
    }

    /** @test */
    public function it_deletes_config_successfully()
    {
        $user = $this->createUserWithRole('marketing');
        Sanctum::actingAs($user);

        $config = PopupConfig::first();

        $response = $this->deleteJson("/api/popup-configs/{$config->id_popup_config}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('popup_configs', ['id_popup_config' => $config->id_popup_config]);
    }

    /** @test */
    public function it_returns_public_config_without_auth()
    {
        $response = $this->getJson('/api/public/popup-configs/producto/1');
        $response->assertStatus(200);
    }
}