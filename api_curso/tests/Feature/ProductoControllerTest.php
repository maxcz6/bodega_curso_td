<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — ProductoController
 * ============================================================
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=ProductoControllerTest
 *
 * Ejecutar con detalle de cada prueba:
 *   docker exec bodega_api php artisan test --filter=ProductoControllerTest --verbose
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Producto;

class ProductoControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // HELPERS: crea registros base requeridos por Producto
    // ─────────────────────────────────────────────────────────────

    private function crearCategoria(): int
    {
        return \DB::table('categorias')->insertGetId([
            'nombre'      => 'Categoría Test',
            'descripcion' => null,
        ]);
    }

    private function crearProducto(array $override = []): Producto
    {
        $idCat = $this->crearCategoria();
        return Producto::create(array_merge([
            'nombre'        => 'Producto Demo',
            'precio_compra' => 5.00,
            'precio_venta'  => 10.00,
            'stock_actual'  => 50,
            'stock_minimo'  => 5,
            'estado'        => true,
            'id_categoria'  => $idCat,
        ], $override));
    }

    // ════════════════════════════════════════════════════════════
    // 1. LISTADO (GET /api/productos)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_productos(): void
    {
        // El endpoint debe responder 200 con estructura correcta
        $this->crearProducto();

        $response = $this->getJson('/api/productos');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function test_listado_productos_incluye_categoria(): void
    {
        // Cada producto en el listado debe traer la relación categoria cargada
        $this->crearProducto(['nombre' => 'Leche']);

        $response = $this->getJson('/api/productos');

        $data = $response->json('data.0');
        $this->assertArrayHasKey('categoria', $data);
    }

    /** @test */
    public function test_listado_productos_vacio(): void
    {
        // Sin productos, data debe ser arreglo vacío
        $response = $this->getJson('/api/productos');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    // ════════════════════════════════════════════════════════════
    // 2. CREACIÓN (POST /api/productos)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_crear_producto_valido(): void
    {
        // Caso feliz: todos los campos requeridos presentes y correctos
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => 'Arroz Extra',
            'precio_compra' => 2.50,
            'precio_venta'  => 3.50,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('productos', ['nombre' => 'Arroz Extra']);
    }

    /** @test */
    public function test_crear_producto_sin_nombre_devuelve_422(): void
    {
        // Validación: nombre es campo requerido
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'precio_compra' => 2.00,
            'precio_venta'  => 4.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_sin_precio_venta_devuelve_422(): void
    {
        // precio_venta es requerido; omitirlo debe fallar
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => 'Sin Precio',
            'precio_compra' => 2.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_con_categoria_inexistente_devuelve_422(): void
    {
        // id_categoria debe existir en la tabla categorias (exists rule)
        $response = $this->postJson('/api/productos', [
            'nombre'        => 'Test',
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => 99999,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_precio_negativo_devuelve_422(): void
    {
        // Los precios no pueden ser negativos (min:0 en validación)
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => 'Precio Negativo',
            'precio_compra' => -1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_codigo_barras_duplicado_devuelve_422(): void
    {
        // El campo codigo_barras es unique en la tabla → duplicado debe fallar
        $idCat = $this->crearCategoria();
        $this->crearProducto(['codigo_barras' => 'ABC123', 'id_categoria' => $idCat]);

        $response = $this->postJson('/api/productos', [
            'nombre'         => 'Otro Producto',
            'precio_compra'  => 1.00,
            'precio_venta'   => 2.00,
            'id_categoria'   => $idCat,
            'codigo_barras'  => 'ABC123',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_nombre_exactamente_150_caracteres(): void
    {
        // Límite exacto de nombre: 150 caracteres → debe ser válido
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => str_repeat('X', 150),
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_crear_producto_nombre_supera_150_caracteres(): void
    {
        // Límite superado: 151 caracteres → debe rechazar
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => str_repeat('X', 151),
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_stock_negativo_devuelve_422(): void
    {
        // stock_actual no puede ser negativo (min:0)
        $idCat = $this->crearCategoria();

        $response = $this->postJson('/api/productos', [
            'nombre'        => 'Stock Neg',
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => $idCat,
            'stock_actual'  => -5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_crear_producto_por_defecto_esta_activo(): void
    {
        // Si no se envía 'estado', el producto debe crearse activo (true)
        $idCat = $this->crearCategoria();

        $this->postJson('/api/productos', [
            'nombre'        => 'Activo Por Defecto',
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => $idCat,
        ]);

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Activo Por Defecto',
            'estado' => 1,
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // 3. ACTUALIZACIÓN (PUT /api/productos/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_actualizar_producto(): void
    {
        // Actualizar nombre y precio de un producto existente
        $producto = $this->crearProducto();
        $idCat = $producto->id_categoria;

        $response = $this->putJson("/api/productos/{$producto->id_producto}", [
            'nombre'        => 'Nombre Nuevo',
            'precio_compra' => 6.00,
            'precio_venta'  => 12.00,
            'id_categoria'  => $idCat,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('productos', ['nombre' => 'Nombre Nuevo']);
    }

    /** @test */
    public function test_actualizar_producto_inexistente_devuelve_404(): void
    {
        $response = $this->putJson('/api/productos/99999', [
            'nombre'        => 'X',
            'precio_compra' => 1.00,
            'precio_venta'  => 2.00,
            'id_categoria'  => 1,
        ]);

        $response->assertStatus(404);
    }

    // ════════════════════════════════════════════════════════════
    // 4. ELIMINACIÓN (DELETE /api/productos/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_eliminar_producto_sin_historial(): void
    {
        // Producto sin ventas ni movimientos → se elimina de forma permanente
        $producto = $this->crearProducto();

        $response = $this->deleteJson("/api/productos/{$producto->id_producto}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('productos', ['id_producto' => $producto->id_producto]);
    }

    /** @test */
    public function test_eliminar_producto_inexistente_devuelve_404(): void
    {
        $response = $this->deleteJson('/api/productos/99999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    // ════════════════════════════════════════════════════════════
    // 5. BAJO STOCK (GET /api/productos/bajo-stock)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_bajo_stock_solo_devuelve_productos_con_stock_bajo(): void
    {
        // Solo deben aparecer productos cuyo stock_actual <= stock_minimo
        $this->crearProducto(['nombre' => 'Bajo Stock', 'stock_actual' => 3, 'stock_minimo' => 5]);
        $this->crearProducto(['nombre' => 'Stock Normal', 'stock_actual' => 20, 'stock_minimo' => 5]);

        $response = $this->getJson('/api/productos/bajo-stock');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Bajo Stock', $data[0]['nombre']);
    }

    /** @test */
    public function test_bajo_stock_vacio_si_todos_tienen_stock_suficiente(): void
    {
        $this->crearProducto(['stock_actual' => 100, 'stock_minimo' => 5]);

        $response = $this->getJson('/api/productos/bajo-stock');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }
}
