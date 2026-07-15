<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — DashboardController
 * ============================================================
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=DashboardControllerTest
 * ============================================================
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────
    // HELPER: siembra datos mínimos para que el dashboard
    // tenga información que devolver
    // ─────────────────────────────────────────────────────────────
    private function seedDashboard(): void
    {
        \DB::table('usuarios')->insert([
            'id_usuario' => 1, 'nombres' => 'Admin',
            'username' => 'admin', 'password' => bcrypt('s'), 'estado' => 1,
        ]);

        \DB::table('tipo_comprobante')->insert([
            ['id_tipo_comprobante' => 1, 'nombre' => 'Boleta'],
            ['id_tipo_comprobante' => 2, 'nombre' => 'Factura'],
        ]);

        \DB::table('motivos_movimiento')->insert([
            ['id_motivo' => 2, 'nombre' => 'Venta'],
        ]);

        $idCat = \DB::table('categorias')->insertGetId(['nombre' => 'General']);

        Producto::create([
            'nombre' => 'P1', 'precio_compra' => 1, 'precio_venta' => 2,
            'stock_actual' => 3, 'stock_minimo' => 5, 'estado' => true, 'id_categoria' => $idCat,
        ]);
        Producto::create([
            'nombre' => 'P2', 'precio_compra' => 1, 'precio_venta' => 2,
            'stock_actual' => 20, 'stock_minimo' => 5, 'estado' => true, 'id_categoria' => $idCat,
        ]);

        Cliente::create(['nombres' => 'Cliente A']);
    }

    // ════════════════════════════════════════════════════════════
    // 1. ESTADÍSTICAS (GET /api/dashboard/stats)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_dashboard_responde_200(): void
    {
        // El endpoint debe siempre responder 200, incluso sin datos
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** @test */
    public function test_dashboard_tiene_estructura_correcta(): void
    {
        // La respuesta debe incluir las claves esperadas por el frontend
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'total_ventas_hoy',
                         'ingresos_hoy',
                         'total_productos',
                         'total_clientes',
                         'productos_bajo_stock',
                         'ventas_recientes',
                     ],
                 ]);
    }

    /** @test */
    public function test_dashboard_cuenta_clientes_correctamente(): void
    {
        // Después de crear 3 clientes, total_clientes debe ser 3
        $this->seedDashboard();
        Cliente::create(['nombres' => 'Cliente B']);
        Cliente::create(['nombres' => 'Cliente C']);

        $response = $this->getJson('/api/dashboard/stats');

        $totalClientes = $response->json('data.total_clientes');
        // seedDashboard crea 1 + 2 más = 3
        $this->assertEquals(3, $totalClientes);
    }

    /** @test */
    public function test_dashboard_cuenta_productos_correctamente(): void
    {
        // seedDashboard crea 2 productos
        $this->seedDashboard();

        $response = $this->getJson('/api/dashboard/stats');

        $this->assertEquals(2, $response->json('data.total_productos'));
    }

    /** @test */
    public function test_dashboard_detecta_productos_bajo_stock(): void
    {
        // Producto P1 tiene stock 3 < stock_minimo 5 → debe aparecer en bajo stock
        $this->seedDashboard();

        $response = $this->getJson('/api/dashboard/stats');

        $bajosStock = $response->json('data.productos_bajo_stock');
        $this->assertGreaterThanOrEqual(1, $bajosStock);
    }

    /** @test */
    public function test_dashboard_ventas_hoy_cero_sin_ventas(): void
    {
        // Sin ninguna venta registrada hoy, total_ventas_hoy debe ser 0
        $response = $this->getJson('/api/dashboard/stats');

        $this->assertEquals(0, $response->json('data.total_ventas_hoy'));
    }

    /** @test */
    public function test_dashboard_ingresos_hoy_cero_sin_ventas(): void
    {
        // Sin ventas, los ingresos del día deben ser 0
        $response = $this->getJson('/api/dashboard/stats');

        $ingresos = (float) $response->json('data.ingresos_hoy');
        $this->assertEquals(0.0, $ingresos);
    }

    /** @test */
    public function test_dashboard_ventas_recientes_es_arreglo(): void
    {
        // ventas_recientes debe ser un array (puede estar vacío)
        $response = $this->getJson('/api/dashboard/stats');

        $this->assertIsArray($response->json('data.ventas_recientes'));
    }

    /** @test */
    public function test_dashboard_sin_datos_devuelve_ceros(): void
    {
        // Con DB vacía, todos los contadores numéricos deben ser 0 o arreglos vacíos
        $response = $this->getJson('/api/dashboard/stats');

        $data = $response->json('data');

        $this->assertEquals(0, $data['total_ventas_hoy']);
        $this->assertEquals(0, (float) $data['ingresos_hoy']);
        $this->assertEquals(0, $data['total_productos']);
        $this->assertEquals(0, $data['total_clientes']);
    }
}
