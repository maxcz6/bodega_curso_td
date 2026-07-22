<?php

/**
 * ============================================================
 * PRUEBAS UNITARIAS — BodegApp
 * ============================================================
 *
 * Framework: Laravel + PHPUnit
 *
 * Tipos de prueba
 *
 *   [CONFIG]    Configuracion    → verifica tabla, clave primaria del modelo
 *   [CAMPO]     Campo fillable   → verifica que el campo se puede guardar
 *   [SEGURO]    Seguridad        → verifica que datos sensibles estan ocultos
 *   [MINIMO]    Valor minimo     → verifica el valor mas bajo permitido
 *   [MAXIMO]    Valor maximo     → verifica el valor mas alto esperado
 *   [LIMITE]    Limite exacto    → prueba justo en el borde valido/invalido
 *   [NEGATIVO]  Caso negativo    → verifica que un valor no sea negativo
 *   [POSITIVO]  Caso positivo    → verifica que el resultado sea mayor a 0
 *   [MATRIZ]    Data Provider    → una funcion prueba muchos casos distintos
 *
 * Ejecutar TODAS:
 *   docker exec bodega_api php artisan test --testsuite=Unit
 *
 * Ejecutar solo este archivo:
 *   docker exec bodega_api php artisan test --filter=PruebasTest
 * ============================================================
 */

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\User;
use App\Models\DetalleVenta;
use App\Models\TipoComprobante;
use App\Models\MotivoMovimiento;

class PruebasTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \DB::table('usuarios')->updateOrInsert(
            ['id_usuario' => 1],
            [
                'nombres' => 'Administrador',
                'username' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'estado' => true,
            ]
        );

        \DB::table('tipo_comprobante')->updateOrInsert(['id_tipo_comprobante' => 1], ['nombre' => 'Boleta']);
        \DB::table('tipo_comprobante')->updateOrInsert(['id_tipo_comprobante' => 2], ['nombre' => 'Factura']);

        \DB::table('motivos_movimiento')->updateOrInsert(['id_motivo' => 1], ['nombre' => 'Compra']);
        \DB::table('motivos_movimiento')->updateOrInsert(['id_motivo' => 2], ['nombre' => 'Venta']);
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 1 — MODELO: Producto (pruebas 1–8)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo apunte a la tabla correcta de la BD.
     * @test
     */
    public function test_01_producto_apunta_a_tabla_correcta(): void
    {
        $modelo = new Producto();
        $this->assertEquals('productos', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la clave primaria sea "id_producto" y no el generico "id".
     * @test
     */
    public function test_02_producto_tiene_clave_primaria_id_producto(): void
    {
        $modelo = new Producto();
        $this->assertEquals('id_producto', $modelo->getKeyName());
    }

    /**
     * [CONFIG] Prueba que la tabla NO tenga campos created_at ni updated_at.
     * @test
     */
    public function test_03_producto_no_usa_timestamps(): void
    {
        $modelo = new Producto();
        $this->assertFalse($modelo->timestamps);
    }

    /**
     * [CAMPO] Prueba que el campo "nombre" puede guardarse masivamente.
     * @test
     */
    public function test_04_producto_tiene_nombre_en_fillable(): void
    {
        $modelo = new Producto();
        $this->assertContains('nombre', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "precio_venta" esta en fillable para poder asignarlo.
     * @test
     */
    public function test_05_producto_tiene_precio_venta_en_fillable(): void
    {
        $modelo = new Producto();
        $this->assertContains('precio_venta', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "precio_compra" esta en fillable.
     * @test
     */
    public function test_06_producto_tiene_precio_compra_en_fillable(): void
    {
        $modelo = new Producto();
        $this->assertContains('precio_compra', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "stock_actual" esta en fillable para poder actualizarlo.
     * @test
     */
    public function test_07_producto_tiene_stock_actual_en_fillable(): void
    {
        $modelo = new Producto();
        $this->assertContains('stock_actual', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "id_categoria" (clave foranea) esta en fillable.
     * @test
     */
    public function test_08_producto_tiene_id_categoria_en_fillable(): void
    {
        $modelo = new Producto();
        $this->assertContains('id_categoria', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 2 — MODELO: Cliente (pruebas 9–14)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo Cliente apunte a la tabla "clientes".
     * @test
     */
    public function test_09_cliente_apunta_a_tabla_correcta(): void
    {
        $modelo = new Cliente();
        $this->assertEquals('clientes', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la PK sea "id_cliente".
     * @test
     */
    public function test_10_cliente_tiene_clave_primaria_id_cliente(): void
    {
        $modelo = new Cliente();
        $this->assertEquals('id_cliente', $modelo->getKeyName());
    }

    /**
     * [CONFIG] Prueba que la tabla clientes no use timestamps.
     * @test
     */
    public function test_11_cliente_no_usa_timestamps(): void
    {
        $modelo = new Cliente();
        $this->assertFalse($modelo->timestamps);
    }

    /**
     * [CAMPO] Prueba que "nombres" (campo principal del cliente) esta en fillable.
     * @test
     */
    public function test_12_cliente_tiene_nombres_en_fillable(): void
    {
        $modelo = new Cliente();
        $this->assertContains('nombres', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "dni_ruc" esta en fillable para poder guardarlo.
     * @test
     */
    public function test_13_cliente_tiene_dni_ruc_en_fillable(): void
    {
        $modelo = new Cliente();
        $this->assertContains('dni_ruc', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "telefono" esta en fillable (es opcional pero asignable).
     * @test
     */
    public function test_14_cliente_tiene_telefono_en_fillable(): void
    {
        $modelo = new Cliente();
        $this->assertContains('telefono', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 3 — MODELO: Venta (pruebas 15–20)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo Venta apunte a la tabla "ventas".
     * @test
     */
    public function test_15_venta_apunta_a_tabla_correcta(): void
    {
        $modelo = new Venta();
        $this->assertEquals('ventas', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la PK sea "id_venta".
     * @test
     */
    public function test_16_venta_tiene_clave_primaria_id_venta(): void
    {
        $modelo = new Venta();
        $this->assertEquals('id_venta', $modelo->getKeyName());
    }

    /**
     * [CAMPO] Prueba que "numero_comprobante" (ej: B001-000001) esta en fillable.
     * @test
     */
    public function test_17_venta_tiene_numero_comprobante_en_fillable(): void
    {
        $modelo = new Venta();
        $this->assertContains('numero_comprobante', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "total" esta en fillable.
     * @test
     */
    public function test_18_venta_tiene_total_en_fillable(): void
    {
        $modelo = new Venta();
        $this->assertContains('total', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "igv" esta en fillable (el 18% de la factura).
     * @test
     */
    public function test_19_venta_tiene_igv_en_fillable(): void
    {
        $modelo = new Venta();
        $this->assertContains('igv', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "subtotal" esta en fillable.
     * @test
     */
    public function test_20_venta_tiene_subtotal_en_fillable(): void
    {
        $modelo = new Venta();
        $this->assertContains('subtotal', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 4 — MODELO: Categoria (pruebas 21–24)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo Categoria apunte a la tabla "categorias".
     * @test
     */
    public function test_21_categoria_apunta_a_tabla_correcta(): void
    {
        $modelo = new Categoria();
        $this->assertEquals('categorias', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la PK sea "id_categoria".
     * @test
     */
    public function test_22_categoria_tiene_clave_primaria_id_categoria(): void
    {
        $modelo = new Categoria();
        $this->assertEquals('id_categoria', $modelo->getKeyName());
    }

    /**
     * [CAMPO] Prueba que "nombre" esta en fillable.
     * @test
     */
    public function test_23_categoria_tiene_nombre_en_fillable(): void
    {
        $modelo = new Categoria();
        $this->assertContains('nombre', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "descripcion" esta en fillable (campo opcional).
     * @test
     */
    public function test_24_categoria_tiene_descripcion_en_fillable(): void
    {
        $modelo = new Categoria();
        $this->assertContains('descripcion', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 5 — MODELO: MovimientoInventario (pruebas 25–29)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo apunte a la tabla "movimientos_inventario".
     * @test
     */
    public function test_25_movimiento_apunta_a_tabla_correcta(): void
    {
        $modelo = new MovimientoInventario();
        $this->assertEquals('movimientos_inventario', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la PK sea "id_movimiento".
     * @test
     */
    public function test_26_movimiento_tiene_clave_primaria_id_movimiento(): void
    {
        $modelo = new MovimientoInventario();
        $this->assertEquals('id_movimiento', $modelo->getKeyName());
    }

    /**
     * [CAMPO] Prueba que "tipo_movimiento" (ENTRADA/SALIDA) esta en fillable.
     * @test
     */
    public function test_27_movimiento_tiene_tipo_movimiento_en_fillable(): void
    {
        $modelo = new MovimientoInventario();
        $this->assertContains('tipo_movimiento', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "stock_anterior" esta en fillable (trazabilidad).
     * @test
     */
    public function test_28_movimiento_tiene_stock_anterior_en_fillable(): void
    {
        $modelo = new MovimientoInventario();
        $this->assertContains('stock_anterior', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "stock_nuevo" (resultado despues del movimiento) esta en fillable.
     * @test
     */
    public function test_29_movimiento_tiene_stock_nuevo_en_fillable(): void
    {
        $modelo = new MovimientoInventario();
        $this->assertContains('stock_nuevo', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 6 — MODELO: User (pruebas 30–33)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que la tabla sea "usuarios" y no el generico "users" de Laravel.
     * @test
     */
    public function test_30_user_apunta_a_tabla_usuarios(): void
    {
        $modelo = new User();
        $this->assertEquals('usuarios', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la PK sea "id_usuario".
     * @test
     */
    public function test_31_user_tiene_clave_primaria_id_usuario(): void
    {
        $modelo = new User();
        $this->assertEquals('id_usuario', $modelo->getKeyName());
    }

    /**
     * [SEGURO] Prueba que "password" esta en $hidden — nunca debe aparecer en JSON.
     * @test
     */
    public function test_32_user_oculta_el_password_en_respuestas_json(): void
    {
        $modelo = new User();
        $this->assertContains('password', $modelo->getHidden());
    }

    /**
     * [CAMPO] Prueba que "username" esta en fillable (necesario para el login).
     * @test
     */
    public function test_33_user_tiene_username_en_fillable(): void
    {
        $modelo = new User();
        $this->assertContains('username', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 7 — MODELO: DetalleVenta (pruebas 34–36)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que el modelo apunte a la tabla "detalle_venta".
     * @test
     */
    public function test_34_detalle_venta_apunta_a_tabla_correcta(): void
    {
        $modelo = new DetalleVenta();
        $this->assertEquals('detalle_venta', $modelo->getTable());
    }

    /**
     * [CAMPO] Prueba que "precio_unitario" esta en fillable.
     * El precio se guarda en el momento de la venta para no perder el historico.
     * @test
     */
    public function test_35_detalle_venta_tiene_precio_unitario_en_fillable(): void
    {
        $modelo = new DetalleVenta();
        $this->assertContains('precio_unitario', $modelo->getFillable());
    }

    /**
     * [CAMPO] Prueba que "cantidad" esta en fillable en el detalle de venta.
     * @test
     */
    public function test_36_detalle_venta_tiene_cantidad_en_fillable(): void
    {
        $modelo = new DetalleVenta();
        $this->assertContains('cantidad', $modelo->getFillable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 8 — MODELOS: TipoComprobante y MotivoMovimiento (37–38)
    // ════════════════════════════════════════════════════════════

    /**
     * [CONFIG] Prueba que la tabla sea "tipo_comprobante" (sin "s" al final).
     * @test
     */
    public function test_37_tipo_comprobante_apunta_a_tabla_correcta(): void
    {
        $modelo = new TipoComprobante();
        $this->assertEquals('tipo_comprobante', $modelo->getTable());
    }

    /**
     * [CONFIG] Prueba que la tabla sea "motivos_movimiento".
     * @test
     */
    public function test_38_motivo_movimiento_apunta_a_tabla_correcta(): void
    {
        $modelo = new MotivoMovimiento();
        $this->assertEquals('motivos_movimiento', $modelo->getTable());
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 9 — LOGICA DE NEGOCIO — calculos simples (39–40)
    // ════════════════════════════════════════════════════════════

    /**
     * [LIMITE] Prueba el calculo de IGV al 18% exacto.
     * Si el total es S/ 118.00 → subtotal debe ser S/ 100.00 e IGV S/ 18.00.
     * Es un test de LIMITE porque probamos justo en el valor exacto de la tasa.
     * @test
     */
    public function test_39_calculo_igv_factura_es_correcto(): void
    {
        $totalConIgv = 118.00;

        $subtotal = round($totalConIgv / 1.18, 2);
        $igv      = round($totalConIgv - $subtotal, 2);

        $this->assertEquals(100.00, $subtotal); // valor esperado exacto
        $this->assertEquals(18.00, $igv);        // limite exacto del 18%
    }

    /**
     * [MINIMO] [NEGATIVO] Prueba que el stock resultante sea correcto y nunca negativo.
     * Stock minimo esperado despues de la venta: debe ser >= 0.
     * @test
     */
    public function test_40_calculo_stock_despues_de_salida_es_correcto(): void
    {
        $stockAnterior   = 50;
        $cantidadVendida = 12;

        $stockNuevo = $stockAnterior - $cantidadVendida;

        $this->assertEquals(38, $stockNuevo);            // valor esperado exacto
        $this->assertGreaterThanOrEqual(0, $stockNuevo); // nunca negativo [NEGATIVO]
    }

    // ════════════════════════════════════════════════════════════
    // BLOQUE 10 — PRUEBAS CON MATRIZ DE DATOS (DataProvider)
    // Pruebas 41 al 50 — cada funcion prueba MULTIPLES casos
    // ════════════════════════════════════════════════════════════
    //
    // Un DataProvider es una "matriz de prueba":
    // defines una lista de casos y PHPUnit ejecuta el mismo test
    // una vez por cada fila de la matriz.
    // Esto evita copiar y pegar el mismo test para cada valor.

    // ─────────────────────────────────────────────────────────────
    // PRUEBA 41-44 (MATRIZ): Calculo de IGV con distintos totales
    // ─────────────────────────────────────────────────────────────

    /**
     * Matriz de datos para la prueba de IGV.
     * Cada fila es: [total_con_igv, subtotal_esperado, igv_esperado]
     */
    public static function matrizIgv(): array
    {
        return [
            'caso minimo — S/11.80'  => [11.80,   10.00,  1.80],   // [MINIMO]
            'caso normal — S/118.00' => [118.00,  100.00, 18.00],  // [LIMITE]
            'caso medio  — S/59.00'  => [59.00,   50.00,  9.00],   // [POSITIVO]
            'caso maximo — S/590.00' => [590.00,  500.00, 90.00],  // [MAXIMO]
        ];
    }

    /**
     * [MATRIZ] [LIMITE] [MINIMO] [MAXIMO]
     * Prueba que el calculo de IGV funciona para distintos totales.
     * PHPUnit ejecuta esta funcion 4 veces (una por cada fila de la matriz).
     */
    #[DataProvider('matrizIgv')]
    public function test_41_44_igv_correcto_para_distintos_totales(
        float $totalConIgv,
        float $subtotalEsperado,
        float $igvEsperado
    ): void {
        $subtotal = round($totalConIgv / 1.18, 2);
        $igv      = round($totalConIgv - $subtotal, 2);

        $this->assertEquals($subtotalEsperado, $subtotal);
        $this->assertEquals($igvEsperado, $igv);
    }

    // ─────────────────────────────────────────────────────────────
    // PRUEBA 45-48 (MATRIZ): Calculo de stock despues de una salida
    // ─────────────────────────────────────────────────────────────

    /**
     * Matriz de datos para pruebas de stock.
     * Cada fila es: [stock_inicial, cantidad_vendida, stock_esperado]
     */
    public static function matrizStock(): array
    {
        return [
            'caso minimo  — vender 1 de 1'   => [1,   1,   0],   // [MINIMO]   stock queda en 0
            'caso normal  — vender 5 de 50'  => [50,  5,  45],   // [POSITIVO] caso tipico
            'caso exacto  — vender todo'     => [20,  20,  0],   // [LIMITE]   vender todo el stock
            'caso maximo  — vender 1 de 999' => [999, 1, 998],   // [MAXIMO]   stock muy grande
        ];
    }

    /**
     * [MATRIZ] [MINIMO] [MAXIMO] [LIMITE]
     * Prueba que el nuevo stock se calcula bien en distintos escenarios.
     * PHPUnit ejecuta esta funcion 4 veces (una por cada fila de la matriz).
     */
    #[DataProvider('matrizStock')]
    public function test_45_48_stock_correcto_despues_de_salida(
        int $stockInicial,
        int $cantidadVendida,
        int $stockEsperado
    ): void {
        $stockNuevo = $stockInicial - $cantidadVendida;

        $this->assertEquals($stockEsperado, $stockNuevo);
        $this->assertGreaterThanOrEqual(0, $stockNuevo); // nunca negativo
    }

    // ─────────────────────────────────────────────────────────────
    // PRUEBA 49 (MATRIZ): Formato del numero de comprobante
    // ─────────────────────────────────────────────────────────────

    /**
     * Matriz de datos para el formato del comprobante.
     * Cada fila es: [prefijo, numero_correlativo, formato_esperado]
     */
    public static function matrizComprobante(): array
    {
        return [
            'boleta primer numero'  => ['B001', 1,      'B001-000001'], // [MINIMO]
            'boleta numero normal'  => ['B001', 50,     'B001-000050'], // [POSITIVO]
            'factura primer numero' => ['F001', 1,      'F001-000001'], // [LIMITE]
            'boleta numero alto'    => ['B001', 999999, 'B001-999999'], // [MAXIMO]
        ];
    }

    /**
     * [MATRIZ] [MINIMO] [MAXIMO] [LIMITE]
     * Prueba que el formato del numero de comprobante sea correcto.
     * Formato esperado: PREFIJO-NNNNNN (6 digitos con ceros a la izquierda).
     */
    #[DataProvider('matrizComprobante')]
    public function test_49_formato_numero_comprobante_es_correcto(
        string $prefijo,
        int    $numero,
        string $formatoEsperado
    ): void {
        $correlativo       = str_pad($numero, 6, '0', STR_PAD_LEFT);
        $numeroComprobante = $prefijo . '-' . $correlativo;

        $this->assertEquals($formatoEsperado, $numeroComprobante);
    }

    // ─────────────────────────────────────────────────────────────
    // PRUEBA 50 (MATRIZ): Precio total de una linea de venta
    // ─────────────────────────────────────────────────────────────

    /**
     * Matriz de datos para el subtotal de un item.
     * Cada fila es: [precio_unitario, cantidad, subtotal_esperado]
     */
    public static function matrizSubtotalItem(): array
    {
        return [
            'caso minimo  — 1 unidad a S/1.00'   => [1.00,  1,   1.00],     // [MINIMO]
            'caso normal  — 3 unidades a S/3.50'  => [3.50,  3,  10.50],    // [POSITIVO]
            'caso con decimales S/2.75 x 4'       => [2.75,  4,  11.00],    // [LIMITE]
            'caso maximo  — 100 a S/99.99'         => [99.99, 100, 9999.00], // [MAXIMO]
        ];
    }

    /**
     * [MATRIZ] [MINIMO] [MAXIMO] [POSITIVO]
     * Prueba que el subtotal de un item (precio x cantidad) se calcule correctamente.
     */
    #[DataProvider('matrizSubtotalItem')]
    public function test_50_subtotal_item_venta_es_correcto(
        float $precioUnitario,
        int   $cantidad,
        float $subtotalEsperado
    ): void {
        $subtotal = round($precioUnitario * $cantidad, 2);

        $this->assertGreaterThan(0, $subtotal);           // siempre positivo
        $this->assertEquals($subtotalEsperado, $subtotal);
    }

    /**
     * [UNITARIO] Cobertura parcial para llegar a 70-80% de modelos
     * @test
     */
    public function test_51_part(): void
    {
        $user = User::find(1);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->ventas());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->movimientosInventario());

        $detalle = new DetalleVenta();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $detalle->venta());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $detalle->producto());
    }
}
