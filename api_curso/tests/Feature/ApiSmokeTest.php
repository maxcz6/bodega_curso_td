<?php

/**
 * ============================================================
 * GUÍA COMPLETA DE EJECUCIÓN DE PRUEBAS
 * ============================================================
 *
 * Este proyecto usa PHPUnit (integrado en Laravel) con base de
 * datos SQLite en memoria para las pruebas. Cada test es aislado
 * y comienza con una DB limpia gracias a RefreshDatabase.
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │                  COMANDOS PRINCIPALES                        │
 * ├─────────────────────────────────────────────────────────────┤
 * │ Ejecutar TODAS las pruebas:                                  │
 * │   docker exec bodega_api php artisan test                    │
 * │                                                             │
 * │ Ejecutar solo Suite Feature (todas las pruebas de API):      │
 * │   docker exec bodega_api php artisan test --testsuite=Feature│
 * │                                                             │
 * │ Ejecutar solo Suite Unit:                                    │
 * │   docker exec bodega_api php artisan test --testsuite=Unit   │
 * │                                                             │
 * │ Ejecutar un archivo específico:                              │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=ClienteControllerTest                           │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=VentaControllerTest                             │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=ProductoControllerTest                          │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=MovimientoControllerTest                        │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=CategoriaYComprobanteTest                       │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter=DashboardControllerTest                         │
 * │                                                             │
 * │ Ejecutar una prueba individual por nombre de método:         │
 * │   docker exec bodega_api php artisan test \                  │
 * │     --filter="test_venta_con_stock_insuficiente"             │
 * │                                                             │
 * │ Ver listado de todas las pruebas sin ejecutarlas:            │
 * │   docker exec bodega_api php artisan test --list-tests       │
 * │                                                             │
 * │ Detener al primer fallo:                                     │
 * │   docker exec bodega_api php artisan test --stop-on-failure  │
 * │                                                             │
 * │ Modo verbose (muestra nombre de cada prueba):                │
 * │   docker exec bodega_api php artisan test --verbose          │
 * │                                                             │
 * │ Ver cobertura de código (requiere Xdebug o PCOV):            │
 * │   docker exec bodega_api php artisan test --coverage         │
 * │                                                             │
 * │ Ejecutar en paralelo (más rápido en máquinas con +CPU):      │
 * │   docker exec bodega_api php artisan test --parallel         │
 * └─────────────────────────────────────────────────────────────┘
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │              MAPA DE PRUEBAS POR ARCHIVO                     │
 * ├─────────────────────────────────────────────────────────────┤
 * │ ClienteControllerTest.php    → 15 pruebas                    │
 * │   - CRUD completo                                           │
 * │   - Límites de campos (nombres 150 chars, dni 20 chars)     │
 * │   - Errores de validación (422) y not found (404)           │
 * │                                                             │
 * │ ProductoControllerTest.php   → 17 pruebas                    │
 * │   - CRUD completo con categoría                             │
 * │   - Precios negativos, stock negativo                       │
 * │   - Código de barras duplicado                              │
 * │   - Bajo stock                                              │
 * │                                                             │
 * │ VentaControllerTest.php      → 15 pruebas                    │
 * │   - Boleta vs Factura (con/sin IGV)                         │
 * │   - Descuento de stock automático                           │
 * │   - Número correlativo de comprobante                       │
 * │   - Stock insuficiente, producto inactivo                   │
 * │   - Validaciones de campos requeridos                       │
 * │                                                             │
 * │ MovimientoControllerTest.php → 13 pruebas                    │
 * │   - ENTRADA y SALIDA de inventario                          │
 * │   - Stock_anterior y stock_nuevo correctos                  │
 * │   - Caso límite: salida exactamente igual al stock           │
 * │   - Tipos de movimiento inválidos                           │
 * │                                                             │
 * │ CategoriaYComprobanteTest.php → 12 pruebas                   │
 * │   - CRUD de categorías con nombre único                     │
 * │   - Límite de 100 chars en nombre de categoría              │
 * │   - CRUD de tipos de comprobante                            │
 * │                                                             │
 * │ DashboardControllerTest.php   → 8 pruebas                    │
 * │   - Estructura de respuesta correcta                        │
 * │   - Contadores de clientes y productos                      │
 * │   - Detección de bajo stock                                 │
 * │   - Ingresos y ventas en cero sin datos                     │
 * └─────────────────────────────────────────────────────────────┘
 *
 * Total: 80 pruebas
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │              CONFIGURACIÓN                                   │
 * ├─────────────────────────────────────────────────────────────┤
 * │ phpunit.xml → DB: SQLite en memoria (:memory:)              │
 * │ Cada clase usa RefreshDatabase → DB limpia por test         │
 * │ APP_ENV=testing → sin efectos secundarios                   │
 * └─────────────────────────────────────────────────────────────┘
 * ============================================================
 */

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Suite de pruebas de integración de la API: verifica que
 * las rutas principales de la aplicación responden correctamente.
 *
 * Este test es solo un smoke test rápido para asegurarse de que
 * el servidor está operativo y la API registra sus rutas.
 */
class ApiSmokeTest extends TestCase
{
    // ════════════════════════════════════════════════════════════
    // SMOKE TESTS — verifican que los endpoints base existen
    // (responden algo, aunque sea 200 o 422, no 404 ni 500)
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function test_endpoint_clientes_existe(): void
    {
        // Verifica que la ruta GET /api/clientes está registrada
        $response = $this->getJson('/api/clientes');
        $this->assertNotEquals(404, $response->status(), 'La ruta /api/clientes no existe');
    }

    /** @test */
    public function test_endpoint_productos_existe(): void
    {
        $response = $this->getJson('/api/productos');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_categorias_existe(): void
    {
        $response = $this->getJson('/api/categorias');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_ventas_existe(): void
    {
        $response = $this->getJson('/api/ventas');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_tipo_comprobantes_existe(): void
    {
        $response = $this->getJson('/api/tipo-comprobantes');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_movimientos_existe(): void
    {
        $response = $this->getJson('/api/inventario/movimientos');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_motivos_movimiento_existe(): void
    {
        $response = $this->getJson('/api/motivos-movimiento');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_endpoint_dashboard_stats_existe(): void
    {
        $response = $this->getJson('/api/dashboard/stats');
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function test_ruta_inexistente_devuelve_404(): void
    {
        // Rutas que no existen deben devolver 404 (no 500 ni 200)
        $response = $this->getJson('/api/ruta-que-no-existe');
        $response->assertStatus(404);
    }

    /** @test */
    public function test_todas_las_respuestas_son_json(): void
    {
        // Todos los endpoints GET deben responder con Content-Type application/json
        $endpoints = [
            '/api/clientes',
            '/api/productos',
            '/api/categorias',
            '/api/ventas',
            '/api/tipo-comprobantes',
            '/api/inventario/movimientos',
            '/api/motivos-movimiento',
            '/api/dashboard/stats',
        ];

        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $response->assertHeader('Content-Type', 'application/json');
        }
    }
}
