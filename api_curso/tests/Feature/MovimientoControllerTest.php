<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — MovimientoController
 * ============================================================
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=MovimientoControllerTest
 *
 * Ver solo los tests que fallan:
 *   docker exec bodega_api php artisan test --filter=MovimientoControllerTest --fail-on-warning
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Producto;

class MovimientoControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // HELPERS: inserta las entidades base que requiere la tabla
    // movimientos_inventario (producto, usuario, motivo)
    // ─────────────────────────────────────────────────────────────

    private function seedBase(): array
    {
        // insertOrIgnore: evita error de clave duplicada si el registro ya existe.
        // Esto es necesario porque RefreshDatabase recrea la DB por test, pero
        // dentro del mismo test podría llamarse seedBase más de una vez.
        \DB::table('usuarios')->insertOrIgnore([
            'id_usuario' => 1,
            'nombres'    => 'Admin',
            'username'   => 'admin',
            'password'   => bcrypt('secret'),
            'estado'     => 1,
        ]);

        \DB::table('motivos_movimiento')->insertOrIgnore([
            ['id_motivo' => 1, 'nombre' => 'Compra'],
            ['id_motivo' => 2, 'nombre' => 'Venta'],
            ['id_motivo' => 3, 'nombre' => 'Ajuste'],
            ['id_motivo' => 4, 'nombre' => 'Merma'],
        ]);

        $idCat = \DB::table('categorias')->insertGetId(['nombre' => 'General-' . uniqid()]);

        $producto = Producto::create([
            'nombre'        => 'Producto Mov',
            'precio_compra' => 5.00,
            'precio_venta'  => 10.00,
            'stock_actual'  => 50,
            'stock_minimo'  => 5,
            'estado'        => true,
            'id_categoria'  => $idCat,
        ]);

        return ['id_producto' => $producto->id_producto, 'stock_actual' => 50];
    }

    // ════════════════════════════════════════════════════════════
    // 1. LISTADO (GET /api/inventario/movimientos)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_movimientos(): void
    {
        // El endpoint debe responder 200 con estructura correcta
        $response = $this->getJson('/api/inventario/movimientos');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function test_listado_movimientos_vacio(): void
    {
        $response = $this->getJson('/api/inventario/movimientos');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    /** @test */
    public function test_listado_filtra_por_tipo_movimiento(): void
    {
        // El endpoint acepta ?tipo_movimiento=ENTRADA y filtra correctamente
        $datos = $this->seedBase();

        $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => 10,
        ]);

        $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad'       => 5,
        ]);

        $response = $this->getJson('/api/inventario/movimientos?tipo_movimiento=ENTRADA');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    // ════════════════════════════════════════════════════════════
    // 2. REGISTRO ENTRADA (POST /api/inventario/movimientos)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_registrar_entrada_de_stock(): void
    {
        // ENTRADA: aumenta el stock del producto
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => 20,
            'observaciones'  => 'Compra de proveedor',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        // Stock debe haber aumentado de 50 a 70
        $this->assertDatabaseHas('productos', [
            'id_producto'  => $datos['id_producto'],
            'stock_actual' => 70,
        ]);
    }

    /** @test */
    public function test_puede_registrar_salida_de_stock(): void
    {
        // SALIDA: disminuye el stock del producto
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 4,
            'tipo_movimiento' => 'SALIDA',
            'cantidad'       => 10,
        ]);

        $response->assertStatus(201);

        // Stock debe haber bajado de 50 a 40
        $this->assertDatabaseHas('productos', [
            'id_producto'  => $datos['id_producto'],
            'stock_actual' => 40,
        ]);
    }

    /** @test */
    public function test_salida_guarda_stock_anterior_y_nuevo_correctamente(): void
    {
        // La tabla movimientos debe registrar los valores de stock antes y después
        $datos = $this->seedBase();

        $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad'       => 15,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'id_producto'   => $datos['id_producto'],
            'stock_anterior' => 50,
            'stock_nuevo'   => 35,
            'cantidad'      => 15,
        ]);
    }

    /** @test */
    public function test_salida_con_stock_insuficiente_devuelve_error(): void
    {
        // Stock actual: 50 — pedir salida de 100 debe fallar
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad'       => 100,
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);

        // El stock no debe haber cambiado
        $this->assertDatabaseHas('productos', [
            'id_producto'  => $datos['id_producto'],
            'stock_actual' => 50,
        ]);
    }

    /** @test */
    public function test_salida_exactamente_igual_al_stock_disponible(): void
    {
        // Caso límite: pedir exactamente el stock disponible debe ser válido
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad'       => 50, // exactamente el stock disponible
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('productos', [
            'id_producto'  => $datos['id_producto'],
            'stock_actual' => 0,
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // 3. VALIDACIONES DE CAMPOS
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_movimiento_sin_tipo_devuelve_422(): void
    {
        // tipo_movimiento es requerido
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto' => $datos['id_producto'],
            'id_motivo'   => 1,
            'cantidad'    => 5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_movimiento_tipo_invalido_devuelve_422(): void
    {
        // tipo_movimiento solo acepta ENTRADA o SALIDA (in:ENTRADA,SALIDA)
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 1,
            'tipo_movimiento' => 'TRANSFERENCIA', // valor inválido
            'cantidad'       => 5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_movimiento_cantidad_cero_devuelve_422(): void
    {
        // La cantidad debe ser mínimo 1 (min:1)
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => 0,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_movimiento_cantidad_negativa_devuelve_422(): void
    {
        // Cantidades negativas no están permitidas
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => -5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_movimiento_con_producto_inexistente_devuelve_422(): void
    {
        $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => 99999,
            'id_motivo'      => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => 5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_movimiento_con_motivo_inexistente_devuelve_422(): void
    {
        $datos = $this->seedBase();

        $response = $this->postJson('/api/inventario/movimientos', [
            'id_producto'    => $datos['id_producto'],
            'id_motivo'      => 99999,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad'       => 5,
        ]);

        $response->assertStatus(422);
    }

    // ════════════════════════════════════════════════════════════
    // 4. MOTIVOS (GET /api/motivos-movimiento)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_motivos_movimiento(): void
    {
        $this->seedBase();

        $response = $this->getJson('/api/motivos-movimiento');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Debe haber 4 motivos cargados por seedBase
        $this->assertCount(4, $response->json('data'));
    }
}
