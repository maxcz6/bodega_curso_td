<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — CategoriaController + TipoComprobanteController
 * ============================================================
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=CategoriaYComprobanteTest
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaYComprobanteTest extends TestCase
{
    use RefreshDatabase;

    // ════════════════════════════════════════════════════════════
    // ── CATEGORÍAS ──────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_categorias(): void
    {
        // El endpoint debe responder 200 aunque no haya categorías
        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function test_puede_crear_categoria_valida(): void
    {
        // Caso feliz: nombre requerido presente
        $response = $this->postJson('/api/categorias', [
            'nombre' => 'Lácteos',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Lácteos']);
    }

    /** @test */
    public function test_crear_categoria_sin_nombre_devuelve_422(): void
    {
        // nombre es requerido → sin él la validación debe fallar
        $response = $this->postJson('/api/categorias', []);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_crear_categoria_nombre_duplicado_es_permitido(): void
    {
        // El controller NO impone unique en nombre de categoría → dos con mismo nombre son válidos.
        // Si en el futuro se agrega esa validación, este test deberá cambiar a assertStatus(422).
        $this->postJson('/api/categorias', ['nombre' => 'Bebidas']);
        $response = $this->postJson('/api/categorias', ['nombre' => 'Bebidas']);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_crear_categoria_nombre_exactamente_100_caracteres(): void
    {
        // Límite exacto: 100 chars → válido
        $response = $this->postJson('/api/categorias', [
            'nombre' => str_repeat('C', 100),
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_crear_categoria_nombre_supera_100_caracteres(): void
    {
        // 101 chars → inválido
        $response = $this->postJson('/api/categorias', [
            'nombre' => str_repeat('C', 101),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_puede_actualizar_categoria(): void
    {
        $id = \DB::table('categorias')->insertGetId(['nombre' => 'Antes']);

        $response = $this->putJson("/api/categorias/{$id}", [
            'nombre' => 'Después',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Después']);
    }

    /** @test */
    public function test_actualizar_categoria_inexistente_devuelve_404(): void
    {
        $response = $this->putJson('/api/categorias/99999', [
            'nombre' => 'X',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /** @test */
    public function test_puede_eliminar_categoria_sin_productos(): void
    {
        // Categoría sin productos asociados puede eliminarse
        $id = \DB::table('categorias')->insertGetId(['nombre' => 'Sin Prods']);

        $response = $this->deleteJson("/api/categorias/{$id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('categorias', ['id_categoria' => $id]);
    }

    /** @test */
    public function test_eliminar_categoria_inexistente_devuelve_404(): void
    {
        $response = $this->deleteJson('/api/categorias/99999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    /** @test */
    public function test_ver_detalle_categoria(): void
    {
        $id = \DB::table('categorias')->insertGetId(['nombre' => 'Detalle Test']);

        $response = $this->getJson("/api/categorias/{$id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.nombre', 'Detalle Test');
    }

    /** @test */
    public function test_ver_categoria_inexistente_devuelve_404(): void
    {
        $response = $this->getJson('/api/categorias/99999');

        $response->assertStatus(404);
    }

    // ════════════════════════════════════════════════════════════
    // ── TIPOS DE COMPROBANTE ────────────────────────────────────
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_tipos_comprobante(): void
    {
        $response = $this->getJson('/api/tipo-comprobantes');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function test_puede_crear_tipo_comprobante(): void
    {
        // Crear un nuevo tipo como "Ticket" además de Boleta y Factura
        $response = $this->postJson('/api/tipo-comprobantes', [
            'nombre' => 'Ticket',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tipo_comprobante', ['nombre' => 'Ticket']);
    }

    /** @test */
    public function test_crear_tipo_comprobante_sin_nombre_devuelve_422(): void
    {
        $response = $this->postJson('/api/tipo-comprobantes', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_tipo_comprobante_nombre_supera_50_caracteres(): void
    {
        // El nombre tiene límite de 50 caracteres
        $response = $this->postJson('/api/tipo-comprobantes', [
            'nombre' => str_repeat('T', 51),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_puede_actualizar_tipo_comprobante(): void
    {
        \DB::table('tipo_comprobante')->insert([
            'id_tipo_comprobante' => 10,
            'nombre'              => 'Provisional',
        ]);

        $response = $this->putJson('/api/tipo-comprobantes/10', [
            'nombre' => 'Definitivo',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function test_actualizar_tipo_comprobante_inexistente_devuelve_404(): void
    {
        $response = $this->putJson('/api/tipo-comprobantes/99999', [
            'nombre' => 'X',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_puede_eliminar_tipo_comprobante_sin_ventas(): void
    {
        \DB::table('tipo_comprobante')->insert([
            'id_tipo_comprobante' => 99,
            'nombre'              => 'Para Borrar',
        ]);

        $response = $this->deleteJson('/api/tipo-comprobantes/99');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('tipo_comprobante', ['id_tipo_comprobante' => 99]);
    }

    /** @test */
    public function test_eliminar_tipo_comprobante_inexistente_devuelve_404(): void
    {
        $response = $this->deleteJson('/api/tipo-comprobantes/99999');

        $response->assertStatus(404);
    }
}
