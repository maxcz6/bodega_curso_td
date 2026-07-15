<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — ClienteController
 * ============================================================
 *
 * Cómo ejecutar TODAS las pruebas del proyecto:
 *   docker exec bodega_api php artisan test
 *
 * Cómo ejecutar solo las pruebas de Feature:
 *   docker exec bodega_api php artisan test --testsuite=Feature
 *
 * Cómo ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=ClienteControllerTest
 *
 * Cómo ejecutar una prueba específica por nombre:
 *   docker exec bodega_api php artisan test --filter="test_puede_listar_clientes"
 *
 * Cómo ver cobertura de código (requiere Xdebug o PCOV):
 *   docker exec bodega_api php artisan test --coverage
 *
 * Flags útiles:
 *   --stop-on-failure   Detiene en el primer fallo
 *   --parallel          Ejecuta en paralelo (más rápido)
 *   --verbose           Muestra nombre de cada prueba
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;

class ClienteControllerTest extends TestCase
{
    // RefreshDatabase: recrea la base de datos en memoria (SQLite) antes de cada test.
    // Esto garantiza que cada prueba parte de un estado limpio y no depende de otras.
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // HELPER: crea un cliente válido en la DB para usar en los tests
    // ─────────────────────────────────────────────────────────────
    private function crearCliente(array $override = []): Cliente
    {
        return Cliente::create(array_merge([
            'nombres'  => 'Juan Pérez',
            'dni_ruc'  => '12345678',
            'telefono' => '999000111',
            'direccion' => 'Av. Lima 100',
        ], $override));
    }

    // ════════════════════════════════════════════════════════════
    // 1. LISTADO (GET /api/clientes)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_clientes(): void
    {
        // Verifica que el endpoint devuelve 200 y la estructura JSON correcta
        $this->crearCliente();

        $response = $this->getJson('/api/clientes');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data'])
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function test_listado_vacio_devuelve_array_vacio(): void
    {
        // Sin clientes en la DB, data debe ser un arreglo vacío, no null ni error
        $response = $this->getJson('/api/clientes');

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'data' => []]);
    }

    /** @test */
    public function test_listado_retorna_multiples_clientes(): void
    {
        // Verifica que todos los clientes creados aparecen en el listado
        $this->crearCliente(['nombres' => 'Ana López']);
        $this->crearCliente(['nombres' => 'Carlos Ríos']);

        $response = $this->getJson('/api/clientes');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    // ════════════════════════════════════════════════════════════
    // 2. CREACIÓN (POST /api/clientes)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_crear_cliente_valido(): void
    {
        // Caso feliz: todos los campos requeridos y opcionales correctos
        $response = $this->postJson('/api/clientes', [
            'nombres'  => 'María García',
            'dni_ruc'  => '87654321',
            'telefono' => '987654321',
            'direccion' => 'Jr. Cusco 200',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        // Verifica que el registro quedó guardado en la base de datos
        $this->assertDatabaseHas('clientes', ['nombres' => 'María García']);
    }

    /** @test */
    public function test_crear_cliente_sin_nombre_devuelve_422(): void
    {
        // Error de validación: el campo 'nombres' es requerido
        $response = $this->postJson('/api/clientes', [
            'dni_ruc' => '12345678',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['success', 'errors']);
    }

    /** @test */
    public function test_crear_cliente_nombre_supera_limite_150_caracteres(): void
    {
        // Prueba de límite: nombres mayor a 150 caracteres debe ser rechazado
        $response = $this->postJson('/api/clientes', [
            'nombres' => str_repeat('A', 151),
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_crear_cliente_exactamente_150_caracteres_es_valido(): void
    {
        // Caso límite exacto: 150 caracteres debe pasar la validación
        $response = $this->postJson('/api/clientes', [
            'nombres' => str_repeat('B', 150),
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_crear_cliente_sin_campos_opcionales(): void
    {
        // Los campos dni_ruc, telefono y direccion son nullable → debe crear bien
        $response = $this->postJson('/api/clientes', [
            'nombres' => 'Solo Nombre',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clientes', ['nombres' => 'Solo Nombre']);
    }

    /** @test */
    public function test_crear_cliente_dni_supera_20_caracteres(): void
    {
        // Límite del campo dni_ruc es 20 caracteres → debe rechazar si supera
        $response = $this->postJson('/api/clientes', [
            'nombres' => 'Test',
            'dni_ruc' => str_repeat('9', 21),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_cliente_telefono_supera_20_caracteres(): void
    {
        // Límite del campo telefono es 20 caracteres
        $response = $this->postJson('/api/clientes', [
            'nombres'  => 'Test',
            'telefono' => str_repeat('1', 21),
        ]);

        $response->assertStatus(422);
    }

    // ════════════════════════════════════════════════════════════
    // 3. ACTUALIZACIÓN (PUT /api/clientes/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_actualizar_cliente_existente(): void
    {
        // Actualizar un cliente válido debe devolver 200 y reflejar los cambios
        $cliente = $this->crearCliente();

        $response = $this->putJson("/api/clientes/{$cliente->id_cliente}", [
            'nombres'  => 'Nombre Actualizado',
            'telefono' => '111000222',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('clientes', ['nombres' => 'Nombre Actualizado']);
    }

    /** @test */
    public function test_actualizar_cliente_inexistente_devuelve_404(): void
    {
        // Un ID que no existe debe retornar 404
        $response = $this->putJson('/api/clientes/99999', [
            'nombres' => 'Fantasma',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /** @test */
    public function test_actualizar_cliente_nombre_vacio_devuelve_422(): void
    {
        // No se puede actualizar un cliente dejando el nombre en blanco
        $cliente = $this->crearCliente();

        $response = $this->putJson("/api/clientes/{$cliente->id_cliente}", [
            'nombres' => '',
        ]);

        $response->assertStatus(422);
    }

    // ════════════════════════════════════════════════════════════
    // 4. ELIMINACIÓN (DELETE /api/clientes/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_eliminar_cliente_sin_ventas(): void
    {
        // Un cliente sin ventas asociadas puede eliminarse
        $cliente = $this->crearCliente();

        $response = $this->deleteJson("/api/clientes/{$cliente->id_cliente}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('clientes', ['id_cliente' => $cliente->id_cliente]);
    }

    /** @test */
    public function test_eliminar_cliente_inexistente_devuelve_404(): void
    {
        // Intentar eliminar un ID inexistente debe retornar 404
        $response = $this->deleteJson('/api/clientes/99999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    // ════════════════════════════════════════════════════════════
    // 5. VER DETALLE (GET /api/clientes/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_ver_detalle_de_cliente(): void
    {
        // Ver un cliente existente debe devolver sus datos
        $cliente = $this->crearCliente(['nombres' => 'Pedro Ruiz']);

        $response = $this->getJson("/api/clientes/{$cliente->id_cliente}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.nombres', 'Pedro Ruiz');
    }

    /** @test */
    public function test_ver_cliente_inexistente_devuelve_404(): void
    {
        // Ver un ID que no existe debe retornar 404
        $response = $this->getJson('/api/clientes/99999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }
}
