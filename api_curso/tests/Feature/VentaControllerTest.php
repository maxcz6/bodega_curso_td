<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — VentaController
 * ============================================================
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=VentaControllerTest
 *
 * Detener al primer fallo:
 *   docker exec bodega_api php artisan test --filter=VentaControllerTest --stop-on-failure
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // HELPERS: construye toda la estructura de datos necesaria
    // para registrar una venta (cliente + comprobante + producto)
    // ─────────────────────────────────────────────────────────────

    private function seedBase(): array
    {
        // insertOrIgnore: previene crash por clave duplicada si se llama varias veces
        \DB::table('usuarios')->insertOrIgnore([
            'id_usuario' => 1,
            'nombres'    => 'Admin',
            'username'   => 'admin',
            'password'   => bcrypt('secret'),
            'estado'     => 1,
        ]);

        \DB::table('tipo_comprobante')->insertOrIgnore([
            ['id_tipo_comprobante' => 1, 'nombre' => 'Boleta'],
            ['id_tipo_comprobante' => 2, 'nombre' => 'Factura'],
        ]);

        $idCat = \DB::table('categorias')->insertGetId(['nombre' => 'General-' . uniqid()]);

        \DB::table('motivos_movimiento')->insertOrIgnore([
            ['id_motivo' => 2, 'nombre' => 'Venta'],
        ]);

        // Producto con stock disponible
        $producto = Producto::create([
            'nombre'        => 'Producto Test',
            'precio_compra' => 5.00,
            'precio_venta'  => 10.00,
            'stock_actual'  => 100,
            'stock_minimo'  => 5,
            'estado'        => true,
            'id_categoria'  => $idCat,
        ]);

        // Cliente
        $cliente = Cliente::create([
            'nombres' => 'Cliente Test',
        ]);

        return [
            'id_cliente'          => $cliente->id_cliente,
            'id_tipo_comprobante' => 1,
            'id_producto'         => $producto->id_producto,
            'precio_venta'        => $producto->precio_venta,
            'stock_actual'        => $producto->stock_actual,
            'producto'            => $producto,
        ];
    }

    // ════════════════════════════════════════════════════════════
    // 1. LISTADO (GET /api/ventas)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_ventas(): void
    {
        // Responde 200 con estructura correcta incluso sin ventas
        $response = $this->getJson('/api/ventas');

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function test_listado_ventas_vacio(): void
    {
        $response = $this->getJson('/api/ventas');

        $response->assertStatus(200)
                 ->assertJson(['data' => []]);
    }

    // ════════════════════════════════════════════════════════════
    // 2. CREAR VENTA (POST /api/ventas)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_registrar_venta_boleta(): void
    {
        // Caso feliz: boleta con 1 ítem, stock suficiente
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [
                ['id_producto' => $datos['id_producto'], 'cantidad' => 2],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        // Verifica que la venta se guardó en la DB
        $this->assertDatabaseHas('ventas', ['id_cliente' => $datos['id_cliente']]);
    }

    /** @test */
    public function test_puede_registrar_venta_factura(): void
    {
        // Factura debe calcular IGV y desglozarlo correctamente
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 2,
            'items'               => [
                ['id_producto' => $datos['id_producto'], 'cantidad' => 1],
            ],
        ]);

        $response->assertStatus(201);
        $venta = Venta::first();
        // Factura: igv > 0
        $this->assertGreaterThan(0, (float) $venta->igv);
    }

    /** @test */
    public function test_venta_descuenta_stock_del_producto(): void
    {
        // Después de registrar una venta, el stock del producto debe disminuir
        $datos = $this->seedBase();
        $stockInicial = $datos['stock_actual'];

        $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [
                ['id_producto' => $datos['id_producto'], 'cantidad' => 3],
            ],
        ]);

        $productoActualizado = Producto::find($datos['id_producto']);
        $this->assertEquals($stockInicial - 3, $productoActualizado->stock_actual);
    }

    /** @test */
    public function test_venta_genera_numero_comprobante_correlativo(): void
    {
        // El número de comprobante debe generarse automáticamente con formato B001-XXXXXX
        $datos = $this->seedBase();

        $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 1]],
        ]);

        $venta = Venta::first();
        $this->assertStringStartsWith('B001-', $venta->numero_comprobante);
    }

    /** @test */
    public function test_segunda_venta_tiene_correlativo_mayor(): void
    {
        // Dos ventas consecutivas deben tener números de comprobante distintos y consecutivos
        $datos = $this->seedBase();
        $payload = [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 1]],
        ];

        $this->postJson('/api/ventas', $payload);
        $this->postJson('/api/ventas', $payload);

        $ventas = Venta::orderBy('id_venta')->get();
        $this->assertNotEquals($ventas[0]->numero_comprobante, $ventas[1]->numero_comprobante);
    }

    /** @test */
    public function test_venta_con_stock_insuficiente_devuelve_error(): void
    {
        // Si la cantidad solicitada supera el stock disponible, debe rechazarse
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [
                // Pedir 1000 unidades cuando solo hay 100
                ['id_producto' => $datos['id_producto'], 'cantidad' => 1000],
            ],
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);

        // El stock no debe haber cambiado
        $this->assertDatabaseHas('productos', [
            'id_producto'  => $datos['id_producto'],
            'stock_actual' => 100,
        ]);
    }

    /** @test */
    public function test_venta_sin_cliente_devuelve_422(): void
    {
        // id_cliente es requerido
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 1]],
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_venta_sin_comprobante_devuelve_422(): void
    {
        // id_tipo_comprobante es requerido
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente' => $datos['id_cliente'],
            'items'      => [['id_producto' => $datos['id_producto'], 'cantidad' => 1]],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_venta_sin_items_devuelve_422(): void
    {
        // items es requerido y debe tener al menos 1 elemento (min:1)
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_venta_con_cliente_inexistente_devuelve_422(): void
    {
        // El cliente referenciado debe existir (exists rule)
        $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => 99999,
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => 1, 'cantidad' => 1]],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_venta_con_producto_inexistente_devuelve_422(): void
    {
        // El producto referenciado en items debe existir (exists rule)
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => 99999, 'cantidad' => 1]],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_venta_cantidad_cero_devuelve_422(): void
    {
        // Cantidad debe ser al menos 1 (min:1 en validación)
        $datos = $this->seedBase();

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 0]],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_venta_con_producto_inactivo_devuelve_error(): void
    {
        // Productos con estado=false no pueden venderse (lógica de negocio)
        $datos = $this->seedBase();
        Producto::find($datos['id_producto'])->update(['estado' => false]);

        $response = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 1]],
        ]);

        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    // ════════════════════════════════════════════════════════════
    // 3. VER DETALLE (GET /api/ventas/{id})
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_ver_detalle_de_venta(): void
    {
        // Registra una venta y luego consulta su detalle
        $datos = $this->seedBase();

        $res = $this->postJson('/api/ventas', [
            'id_cliente'          => $datos['id_cliente'],
            'id_tipo_comprobante' => 1,
            'items'               => [['id_producto' => $datos['id_producto'], 'cantidad' => 2]],
        ]);

        $idVenta = $res->json('data.id_venta');
        $response = $this->getJson("/api/ventas/{$idVenta}");

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['id_venta', 'total', 'cliente', 'detalles']]);
    }

    /** @test */
    public function test_ver_venta_inexistente_devuelve_404(): void
    {
        $response = $this->getJson('/api/ventas/99999');

        $response->assertStatus(404)
                 ->assertJson(['success' => false]);
    }

    // ════════════════════════════════════════════════════════════
    // 4. TIPOS DE COMPROBANTE (GET /api/tipo-comprobantes)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_puede_listar_tipos_de_comprobante(): void
    {
        $datos = $this->seedBase();

        $response = $this->getJson('/api/tipo-comprobantes');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }
}
