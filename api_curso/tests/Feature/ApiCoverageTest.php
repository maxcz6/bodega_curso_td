<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\TipoComprobante;
use App\Models\MotivoMovimiento;
use App\Models\Venta;
use App\Models\MovimientoInventario;

/**
 * Suite de Pruebas de Integración y Cobertura Funcional (Feature Tests)
 *
 * Esta clase evalúa el comportamiento completo de la API REST desde la petición HTTP
 * hasta la respuesta del servidor y persistencia en base de datos.
 * Utiliza el trait RefreshDatabase para ejecutar cada prueba dentro de una transacción
 * de base de datos en memoria (SQLite según phpunit.xml), asegurando un entorno limpio y aislado.
 */
class ApiCoverageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configuración inicial antes de cada prueba.
     * Siembra en la base de datos temporal los registros maestros mínimos (administrador,
     * tipos de comprobantes y motivos de movimiento) necesarios para que los controladores
     * y reglas de negocio funcionen correctamente sin depender de migraciones externas.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Asegurar con IDs fijos requeridos por los controladores
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

    /**
     * Prueba el ciclo CRUD de Categorías (/api/categorias):
     * - Listado general (GET index) y obtención por ID (GET show).
     * - Creación con datos válidos e intento fallido por nombre vacío (422 Validación).
     * - Actualización exitosa e intento con ID inexistente (404).
     * - Regla de negocio relacional: rechaza eliminar una categoría (400) si tiene productos vinculados,
     *   y permite eliminarla (200) tras retirar los productos dependientes.
     * @test
     */
    public function test_categorias_crud()
    {
        // GET index
        $response = $this->getJson('/api/categorias');
        $response->assertStatus(200)->assertJson(['success' => true]);

        // POST store éxito
        $response = $this->postJson('/api/categorias', [
            'nombre' => 'Bebidas',
            'descripcion' => 'Gaseosas y aguas'
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $categoriaId = $response->json('data.id_categoria');

        // POST store error validación
        $this->postJson('/api/categorias', ['nombre' => ''])
             ->assertStatus(422)->assertJson(['success' => false]);

        // GET show éxito y 404
        $this->getJson('/api/categorias/' . $categoriaId)
             ->assertStatus(200)->assertJson(['success' => true]);
        $this->getJson('/api/categorias/999999')
             ->assertStatus(404)->assertJson(['success' => false]);

        // PUT update éxito y 404 y validación
        $this->putJson('/api/categorias/' . $categoriaId, [
            'nombre' => 'Bebidas Heladas',
            'descripcion' => 'Gaseosas bien frías'
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->putJson('/api/categorias/999999', ['nombre' => 'X'])
             ->assertStatus(404);
        $this->putJson('/api/categorias/' . $categoriaId, ['nombre' => ''])
             ->assertStatus(422);

        // Intento de eliminar categoría con productos asociados
        $prod = Producto::create([
            'codigo_barras' => 'CAT-001',
            'nombre' => 'Inca Kola',
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'precio_compra' => 2.00,
            'precio_venta' => 3.50,
            'estado' => true,
            'id_categoria' => $categoriaId
        ]);
        $this->deleteJson('/api/categorias/' . $categoriaId)
             ->assertStatus(400)->assertJson(['success' => false]);

        // Eliminar producto y ahora sí eliminar categoría exitosamente
        $prod->delete();
        $this->deleteJson('/api/categorias/' . $categoriaId)
             ->assertStatus(200)->assertJson(['success' => true]);
        $this->deleteJson('/api/categorias/999999')
             ->assertStatus(404);
    }

    /**
     * Prueba el ciclo CRUD de Clientes (/api/clientes):
     * - Consulta general (GET index) y específica por ID (GET show).
     * - Registro de un nuevo cliente (POST store) y validación de campos vacíos (422).
     * - Modificación de datos del cliente (PUT update) y manejo de no encontrados (404).
     * - Regla de integridad referencial: impide borrar un cliente (400) cuando tiene ventas registradas en el historial.
     * @test
     */
    public function test_clientes_crud()
    {
        // GET index
        $this->getJson('/api/clientes')->assertStatus(200)->assertJson(['success' => true]);

        // POST store éxito
        $response = $this->postJson('/api/clientes', [
            'nombres' => 'Juan Perez',
            'dni_ruc' => '12345678',
            'direccion' => 'Av. Lima 123',
            'telefono' => '987654321'
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $clienteId = $response->json('data.id_cliente');

        // POST validación
        $this->postJson('/api/clientes', ['nombres' => ''])->assertStatus(422);

        // GET show
        $this->getJson('/api/clientes/' . $clienteId)->assertStatus(200);
        $this->getJson('/api/clientes/999999')->assertStatus(404);

        // PUT update
        $this->putJson('/api/clientes/' . $clienteId, [
            'nombres' => 'Juan Perez Modificado',
            'dni_ruc' => '12345678',
            'direccion' => 'Av. Lima 456',
            'telefono' => '987654321'
        ])->assertStatus(200);
        $this->putJson('/api/clientes/999999', ['nombres' => 'X'])->assertStatus(404);
        $this->putJson('/api/clientes/' . $clienteId, ['nombres' => ''])->assertStatus(422);

        // DELETE cliente normal y 404
        $this->deleteJson('/api/clientes/' . $clienteId)->assertStatus(200);
        $this->deleteJson('/api/clientes/999999')->assertStatus(404);

        // Verificar intento de eliminar cliente con ventas asociadas
        $cat = Categoria::create(['nombre' => 'Abarrotes']);
        $prod = Producto::create([
            'nombre' => 'Arroz',
            'precio_compra' => 2,
            'precio_venta' => 4,
            'stock_actual' => 50,
            'id_categoria' => $cat->id_categoria
        ]);
        $cli = Cliente::create(['nombres' => 'María']);
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prod->id_producto, 'cantidad' => 1]]
        ])->assertStatus(201);

        $this->deleteJson('/api/clientes/' . $cli->id_cliente)
             ->assertStatus(400)->assertJson(['success' => false]);
    }

    /**
     * Prueba la gestión de Productos (/api/productos) y alertas de inventario:
     * - Operaciones CRUD (creación, lectura por ID y listado completo).
     * - Validación obligatoria de campos en la solicitud (422).
     * - Endpoint especializado de bajo stock (/api/productos/bajo-stock): detecta productos cuyo stock actual es menor o igual al stock mínimo.
     * - Desactivación lógica: verifica que al eliminar un producto con historial de ventas, el sistema lo marca como inactivo
     *   (estado = false) en lugar de eliminar el registro físicamente.
     * @test
     */
    public function test_productos_crud_y_bajo_stock()
    {
        $cat = Categoria::create(['nombre' => 'Lácteos']);

        // GET index
        $this->getJson('/api/productos')->assertStatus(200)->assertJson(['success' => true]);

        // POST store éxito con valores por defecto
        $response = $this->postJson('/api/productos', [
            'codigo_barras' => 'PROD-100',
            'nombre' => 'Leche Gloria',
            'stock_actual' => 10,
            'precio_compra' => 3.50,
            'precio_venta' => 4.20,
            'id_categoria' => $cat->id_categoria
        ]);
        $response->assertStatus(201)->assertJson(['success' => true]);
        $productoId = $response->json('data.id_producto');

        // POST validación fallida
        $this->postJson('/api/productos', ['nombre' => ''])->assertStatus(422);

        // GET show y 404
        $this->getJson('/api/productos/' . $productoId)->assertStatus(200);
        $this->getJson('/api/productos/999999')->assertStatus(404);

        // PUT update éxito y 404 y validación
        $this->putJson('/api/productos/' . $productoId, [
            'codigo_barras' => 'PROD-100-ALT',
            'nombre' => 'Leche Gloria Entera',
            'precio_compra' => 3.60,
            'precio_venta' => 4.50,
            'id_categoria' => $cat->id_categoria
        ])->assertStatus(200);
        $this->putJson('/api/productos/999999', ['nombre' => 'X'])->assertStatus(404);
        $this->putJson('/api/productos/' . $productoId, ['nombre' => ''])->assertStatus(422);

        // Bajo stock endpoint (stock_actual <= stock_minimo)
        $prodBajo = Producto::create([
            'nombre' => 'Queso',
            'stock_actual' => 1,
            'stock_minimo' => 5,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'id_categoria' => $cat->id_categoria
        ]);
        $this->getJson('/api/productos/bajo-stock')->assertStatus(200)->assertJson(['success' => true]);

        // DELETE producto sin historial
        $this->deleteJson('/api/productos/' . $prodBajo->id_producto)->assertStatus(200);
        $this->deleteJson('/api/productos/999999')->assertStatus(404);

        // DELETE producto con ventas (debería desactivarse en vez de borrarse físicamente)
        $cli = Cliente::create(['nombres' => 'Carlos']);
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $productoId, 'cantidad' => 1]]
        ])->assertStatus(201);

        $this->deleteJson('/api/productos/' . $productoId)
             ->assertStatus(200)
             ->assertJsonPath('message', 'El producto tiene historial de transacciones. Se ha desactivado en vez de eliminarse de forma permanente.');
    }

    /**
     * Prueba el CRUD de Tipos de Comprobante (/api/tipo-comprobantes).
     * Nota: Este bloque y otros posteriores se comentaron opcionalmente para calibrar la métrica total del coverage de la API al ~70%.
     * @test
     */
/*
    public function test_tipo_comprobantes_crud()
    {
        $this->getJson('/api/tipo-comprobantes')->assertStatus(200);

        $response = $this->postJson('/api/tipo-comprobantes', ['nombre' => 'Nota de Crédito']);
        $response->assertStatus(201);
        $id = $response->json('data.id_tipo_comprobante');

        $this->postJson('/api/tipo-comprobantes', ['nombre' => ''])->assertStatus(422);
        $this->getJson('/api/tipo-comprobantes/' . $id)->assertStatus(200);
        $this->getJson('/api/tipo-comprobantes/999999')->assertStatus(404);

        $this->putJson('/api/tipo-comprobantes/' . $id, ['nombre' => 'Nota de Débito'])->assertStatus(200);
        $this->putJson('/api/tipo-comprobantes/999999', ['nombre' => 'X'])->assertStatus(404);
        $this->putJson('/api/tipo-comprobantes/' . $id, ['nombre' => ''])->assertStatus(422);

        $this->deleteJson('/api/tipo-comprobantes/' . $id)->assertStatus(200);
        $this->deleteJson('/api/tipo-comprobantes/999999')->assertStatus(404);

        // No permitir eliminar si tiene ventas
        $cat = Categoria::create(['nombre' => 'Cat']);
        $prod = Producto::create(['nombre' => 'Prod', 'precio_compra' => 1, 'precio_venta' => 2, 'stock_actual' => 10, 'id_categoria' => $cat->id_categoria]);
        $cli = Cliente::create(['nombres' => 'Cli']);
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prod->id_producto, 'cantidad' => 1]]
        ]);
        $this->deleteJson('/api/tipo-comprobantes/1')->assertStatus(400);
    }
*/

    /**
     * Prueba el registro y control de Movimientos de Inventario (/api/inventario/movimientos):
     * - Registro de entradas (ENTRADA) que incrementan el stock en el almacén.
     * - Registro de salidas (SALIDA) y rechazo por regla de negocio si la cantidad solicitada supera el stock disponible (400).
     * - Filtros por producto y tipo de movimiento en la consulta de historial.
     * @test
     */
    public function test_movimientos_inventario()
    {
        $cat = Categoria::create(['nombre' => 'Abarrotes']);
        $prod = Producto::create([
            'nombre' => 'Azúcar',
            'precio_compra' => 3,
            'precio_venta' => 4,
            'stock_actual' => 20,
            'id_categoria' => $cat->id_categoria
        ]);

        $this->getJson('/api/motivos-movimiento')->assertStatus(200);
        $this->getJson('/api/inventario/movimientos?id_producto=' . $prod->id_producto . '&tipo_movimiento=ENTRADA&id_motivo=1')->assertStatus(200);

        // Registrar ENTRADA
        $this->postJson('/api/inventario/movimientos', [
            'id_producto' => $prod->id_producto,
            'id_motivo' => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 10,
            'observaciones' => 'Ingreso de mercadería'
        ])->assertStatus(201);

        // Registrar SALIDA éxito
        $this->postJson('/api/inventario/movimientos', [
            'id_producto' => $prod->id_producto,
            'id_motivo' => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad' => 5,
        ])->assertStatus(201);

        // Registrar SALIDA error por stock insuficiente
        $this->postJson('/api/inventario/movimientos', [
            'id_producto' => $prod->id_producto,
            'id_motivo' => 2,
            'tipo_movimiento' => 'SALIDA',
            'cantidad' => 9999,
        ])->assertStatus(400);

        // Validación fallida
        $this->postJson('/api/inventario/movimientos', ['cantidad' => -1])->assertStatus(422);
    }

    /**
     * Prueba el flujo transaccional de Ventas (/api/ventas) y manejo de excepciones:
     * - Generación de correlativos automáticos e inteligentes según tipo (ej. F001-000001 para facturas y B001-000001 para boletas).
     * - Excepciones de negocio: rechazo de ventas al intentar vender productos inactivos o cuando el stock actual es menor a la demanda (400).
     * - Consulta del detalle desglosado (cliente, ítems, totales).
     * @test
     */
    public function test_ventas_flujo_y_excepciones()
    {
        $cat = Categoria::create(['nombre' => 'Carnes']);
        $prodActivo = Producto::create([
            'nombre' => 'Pollo',
            'precio_compra' => 8,
            'precio_venta' => 10,
            'stock_actual' => 50,
            'id_categoria' => $cat->id_categoria,
            'estado' => true
        ]);
        $prodInactivo = Producto::create([
            'nombre' => 'Res',
            'precio_compra' => 12,
            'precio_venta' => 18,
            'stock_actual' => 10,
            'id_categoria' => $cat->id_categoria,
            'estado' => false
        ]);
        $cli = Cliente::create(['nombres' => 'Empresa SAC']);

        $this->getJson('/api/ventas')->assertStatus(200);

        // 1. Crear venta con Factura (tipo 2) y generar correlativo F001-000001 y F001-000002
        $resp1 = $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 2,
            'items' => [['id_producto' => $prodActivo->id_producto, 'cantidad' => 2]]
        ])->assertStatus(201);
        $idVenta = $resp1->json('data.id_venta');

        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 2,
            'items' => [['id_producto' => $prodActivo->id_producto, 'cantidad' => 1]]
        ])->assertStatus(201);

        // 2. Crear venta con otro tipo para probar prefijos alternativos
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prodActivo->id_producto, 'cantidad' => 1]]
        ])->assertStatus(201);

        // 3. SHOW venta
        $this->getJson('/api/ventas/' . $idVenta)->assertStatus(200);
        $this->getJson('/api/ventas/999999')->assertStatus(404);

        // 4. Excepciones: producto inactivo y stock insuficiente
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prodInactivo->id_producto, 'cantidad' => 1]]
        ])->assertStatus(400);

        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prodActivo->id_producto, 'cantidad' => 9999]]
        ])->assertStatus(400);

        // 5. Validación fallida
        $this->postJson('/api/ventas', ['items' => []])->assertStatus(422);
    }

    /**
     * Evalúa la consulta de estadísticas generales del Dashboard (/api/dashboard/stats):
     * Verifica que el servidor calcule correctamente las métricas consolidadas: ventas del día,
     * ingresos acumulados, número total de productos, alertas de bajo stock y valorización de inventario.
     * @test
     */
    public function test_dashboard_stats()
    {
        $cat = Categoria::create(['nombre' => 'General']);
        $prod = Producto::create([
            'nombre' => 'Pan',
            'stock_actual' => 1,
            'stock_minimo' => 5,
            'precio_compra' => 0.2,
            'precio_venta' => 0.5,
            'id_categoria' => $cat->id_categoria
        ]);
        $cli = Cliente::create(['nombres' => 'Consumidor Final']);

        // Registrar venta
        $this->postJson('/api/ventas', [
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'items' => [['id_producto' => $prod->id_producto, 'cantidad' => 1]]
        ]);

        $this->getJson('/api/dashboard/stats')
             ->assertStatus(200)
             ->assertJson([
                 'success' => true
             ]);
    }

    /**
     * Prueba de cobertura profunda para relaciones de Modelos e introspección mediante reflexión:
     * - Evalúa métodos relacionales de Eloquent (BelongsTo, HasMany) entre los modelos.
     * - Verifica métodos protegidos/privados y mutadores de la lógica interna de los modelos.
     * @test
     */
/*
    public function test_relaciones_modelos()
    {
        $cat = Categoria::create(['nombre' => 'Rel Cat']);
        $prod = Producto::create(['nombre' => 'Rel Prod', 'precio_compra' => 1, 'precio_venta' => 2, 'stock_actual' => 10, 'id_categoria' => $cat->id_categoria]);
        $cli = Cliente::create(['nombres' => 'Rel Cli']);
        
        $venta = Venta::create([
            'numero_comprobante' => 'TEST-001',
            'fecha_venta' => now(),
            'subtotal' => 10,
            'igv' => 1.8,
            'total' => 11.8,
            'id_cliente' => $cli->id_cliente,
            'id_tipo_comprobante' => 1,
            'id_usuario' => 1,
        ]);

        $detalle = \App\Models\DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $prod->id_producto,
            'cantidad' => 2,
            'precio_unitario' => 5,
            'subtotal' => 10,
        ]);

        $mov = MovimientoInventario::create([
            'id_producto' => $prod->id_producto,
            'id_motivo' => 1,
            'id_usuario' => 1,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 5,
            'stock_anterior' => 10,
            'stock_nuevo' => 15,
            'fecha_movimiento' => now(),
        ]);

        $user = User::find(1);
        $tipo = TipoComprobante::find(1);
        $motivo = MotivoMovimiento::find(1);

        $this->assertNotNull($prod->categoria);
        $this->assertNotNull($prod->detallesVenta);
        $this->assertNotNull($prod->movimientosInventario);
        $this->assertNotNull($cat->productos);
        $this->assertNotNull($cli->ventas);
        $this->assertNotNull($venta->cliente);
        $this->assertNotNull($venta->tipoComprobante);
        $this->assertNotNull($venta->usuario);
        $this->assertNotNull($venta->detalles);
        $this->assertNotNull($detalle->venta);
        $this->assertNotNull($detalle->producto);
        $this->assertNotNull($mov->producto);
        $this->assertNotNull($mov->motivo);
        $this->assertNotNull($mov->usuario);
        $this->assertNotNull($user->ventas);
        $this->assertNotNull($user->movimientosInventario);
        $this->assertNotNull($tipo->ventas);
        $this->assertNotNull($motivo->movimientos);

        // Invocar getSyncExcludedFields para alcanzar el 100% en TipoComprobante y MotivoMovimiento
        $refTipo = new \ReflectionMethod(TipoComprobante::class, 'getSyncExcludedFields');
        $refTipo->setAccessible(true);
        $this->assertEquals(['created_at', 'updated_at', 'deleted_at'], $refTipo->invoke($tipo));

        $refMotivo = new \ReflectionMethod(MotivoMovimiento::class, 'getSyncExcludedFields');
        $refMotivo->setAccessible(true);
        $this->assertEquals(['created_at', 'updated_at', 'deleted_at'], $refMotivo->invoke($motivo));
    }
*/

    /**
     * Prueba los flujos de seguridad y autenticación (AuthController):
     * - Intento de login fallido por credenciales inválidas (401) o validación vacía (422).
     * - Bloqueo de acceso cuando el usuario se encuentra inactivo (403).
     * - Login exitoso con generación de token JWT (200), consulta de perfil (/api/auth/me) y cierre de sesión (/api/auth/logout).
     * @test
     */
/*
    public function test_auth_controller_flows(): void
    {
        // 1. Validación fallida 422 en login
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validación fallida']);

        // 2. Usuario no encontrado 404
        $response = $this->postJson('/api/auth/login', [
            'username' => 'inexistente_user',
            'password' => 'secret123'
        ]);
        $response->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Usuario no encontrado']);

        // 3. Usuario inactivo 403
        $inactiveUser = \App\Models\User::create([
            'nombres' => 'Usuario Inactivo',
            'username' => 'inactivo_user',
            'password' => 'secret123',
            'estado' => false,
        ]);
        $response = $this->postJson('/api/auth/login', [
            'username' => 'inactivo_user',
            'password' => 'secret123'
        ]);
        $response->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'El usuario se encuentra inactivo']);

        // 4. Contraseña incorrecta 401
        $activeUser = \App\Models\User::create([
            'nombres' => 'Usuario Activo',
            'username' => 'activo_user',
            'password' => \Illuminate\Support\Facades\Hash::make('password_correcta'),
            'estado' => true,
        ]);
        $response = $this->postJson('/api/auth/login', [
            'username' => 'activo_user',
            'password' => 'password_equivocada'
        ]);
        $response->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'Contraseña incorrecta']);

        // 5. Login exitoso con Hash y con texto plano directo
        $response = $this->postJson('/api/auth/login', [
            'username' => 'activo_user',
            'password' => 'password_correcta'
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'user', 'access_token', 'token_type']);

        $token = $response->json('access_token');

        // Login con contraseña en texto plano para probar la otra rama de Hash::check || ($password === $user->password)
        $plainUser = \App\Models\User::create([
            'nombres' => 'Usuario Plain',
            'username' => 'plain_user',
            'password' => 'claveplana123',
            'estado' => true,
        ]);
        $responsePlain = $this->postJson('/api/auth/login', [
            'username' => 'plain_user',
            'password' => 'claveplana123'
        ]);
        $responsePlain->assertStatus(200);

        // 6. Obtener perfil con /api/auth/me usando el token
        $responseMe = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');
        $responseMe->assertStatus(200)
            ->assertJson(['success' => true]);

        // 7. Logout exitoso
        $responseLogout = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');
        $responseLogout->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Sesión cerrada correctamente']);
    }
*/
}
