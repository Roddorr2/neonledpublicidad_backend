<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * VUL01: el rol asignado en /api/register nunca debe venir del cliente.
 */
class RegisterRolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase borra todas las tablas: nunca ejecutar contra la BD real.
        if (! str_ends_with((string) config('database.connections.' . config('database.default') . '.database'), '_test')) {
            $this->markTestSkipped('Ejecutar con DB_DATABASE=neonhouseled_back_test');
        }

        $this->seed(\Database\Seeders\PermisosSeeder::class);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'nombre'   => 'Test',
            'apellido' => 'Vul',
            'email'    => 'vul01@test.com',
            'dni'      => '12345678',
            'telefono' => '999999999',
        ], $extra);
    }

    /** @test */
    public function ignora_id_rol_enviado_por_el_cliente()
    {
        $admin = Rol::where('nombre', 'administrador')->firstOrFail();

        $response = $this->postJson('/api/register', $this->payload(['id_rol' => $admin->id_rol]));

        $response->assertStatus(201)->assertJsonPath('rol', 'cliente');

        $cliente  = Rol::where('nombre', 'cliente')->firstOrFail();
        $empleado = Empleado::where('email', 'vul01@test.com')->firstOrFail();

        $this->assertEquals($cliente->id_rol, $empleado->id_rol);
        $this->assertNotEquals($admin->id_rol, $empleado->id_rol);
    }

    /** @test */
    public function el_token_solo_tiene_la_ability_del_rol_cliente()
    {
        $admin = Rol::where('nombre', 'administrador')->firstOrFail();

        $this->postJson('/api/register', $this->payload(['id_rol' => $admin->id_rol]))
            ->assertStatus(201);

        $token = \Laravel\Sanctum\PersonalAccessToken::latest('id')->firstOrFail();

        $this->assertEquals(['cliente'], $token->abilities);
    }

    /** @test */
    public function registra_sin_id_rol_con_rol_cliente()
    {
        $this->postJson('/api/register', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('rol', 'cliente');
    }
}
