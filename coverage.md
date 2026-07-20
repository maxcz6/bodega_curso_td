# Guía de Cobertura de Código (Code Coverage) - API Bodega

Esta documentación describe el proceso de generación, medición y visualización de la cobertura de código para el backend de Laravel (api_curso) ejecutándose dentro del contenedor Docker bodega_api.

Las pruebas de integración y funcionales se encuentran en [tests/Feature/ApiCoverageTest.php](api_curso/tests/Feature/ApiCoverageTest.php). Este archivo evalúa los endpoints HTTP, controladores, validaciones de requests, reglas de negocio y relaciones de Eloquent de la API. Con estas pruebas se supera la meta del 70%, alcanzando un 91.59% de cobertura total en la aplicación, el 100% en los controladores y el 100% en todos los modelos.

---

## Resumen de Resultados

| Métrica de la API | Cobertura | Estado | Detalle |
| :--- | :---: | :---: | :--- |
| **Controladores (Controllers)** | **100.00%** | Completado | 7 / 7 controladores con el 100% de líneas evaluadas |
| **Modelos (Models)** | **100.00%** | Completado | 9 / 9 modelos con el 100% de métodos y líneas evaluadas |
| **Líneas de Código (Lines)** | **91.59%** | Completado | 577 / 630 líneas evaluadas en total |
| **Métodos (Methods)** | **94.34%** | Completado | 50 / 53 métodos evaluados |
| **Pruebas Funcionales API** | **64 / 64** | Aprobado | 172 aserciones HTTP ejecutadas sin errores |

### Desglose por Controlador (100% de Cobertura)
* App\Http\Controllers\CategoriaController - 100.00% (67/67 líneas)
* App\Http\Controllers\ClienteController - 100.00% (71/71 líneas)
* App\Http\Controllers\DashboardController - 100.00% (48/48 líneas)
* App\Http\Controllers\MovimientoController - 100.00% (67/67 líneas)
* App\Http\Controllers\ProductoController - 100.00% (97/97 líneas)
* App\Http\Controllers\TipoComprobanteController - 100.00% (69/69 líneas)
* App\Http\Controllers\VentaController - 100.00% (128/128 líneas)

### Desglose por Modelo (100% de Cobertura)
* App\Models\Categoria - 100.00%
* App\Models\Cliente - 100.00%
* App\Models\DetalleVenta - 100.00%
* App\Models\MotivoMovimiento - 100.00% (6/6 líneas)
* App\Models\MovimientoInventario - 100.00%
* App\Models\Producto - 100.00%
* App\Models\TipoComprobante - 100.00% (6/6 líneas)
* App\Models\User - 100.00%
* App\Models\Venta - 100.00%

---

## 1. Comandos para medir la cobertura en Docker

El contenedor bodega_api tiene instalada la extensión Xdebug 3. Para activar la recopilación de métricas de cobertura al ejecutar PHPUnit, se debe pasar la variable de entorno XDEBUG_MODE=coverage en el comando de Docker.

### Comando principal (Generar reporte HTML y mostrar tabla en consola)
Abre tu terminal en la carpeta principal del proyecto (bodega_curso) y ejecuta el siguiente comando:

```bash
docker exec -e XDEBUG_MODE=coverage bodega_api vendor/bin/phpunit --filter=ApiCoverageTest --coverage-text --coverage-html public/coverage
```

Explicación de cada argumento:
1. `docker exec -e XDEBUG_MODE=coverage bodega_api`: Ejecuta el comando dentro del contenedor bodega_api activando Xdebug en modo coverage temporalmente.
2. `vendor/bin/phpunit --filter=ApiCoverageTest`: Corre únicamente las pruebas funcionales de la API que miden los endpoints y controladores.
3. `--coverage-text`: Imprime en la consola un resumen en texto plano con los porcentajes de cobertura por archivo una vez que terminan los tests.
4. `--coverage-html public/coverage`: Genera un sitio web estático interactivo en la carpeta public/coverage con el detalle línea por línea.

### Comando rápido para validar pruebas
Si realizas un cambio rápido en el código y solo quieres verificar que las pruebas sigan pasando sin demorar en la generación del HTML:

```bash
docker exec bodega_api php artisan test --filter=ApiCoverageTest
```

---

## 2. Cómo visualizar el reporte HTML en tu equipo

Al terminar el comando de coverage, PHPUnit compila el reporte gráfico en formato HTML dentro de la carpeta pública de Laravel. Hay dos formas prácticas de revisarlo:

### Método A: Abrir el archivo desde el Explorador de Windows
Debido a que el volumen de Docker está enlazado directamente con tu disco local en Windows, los archivos creados dentro del contenedor están disponibles al instante en tu carpeta del proyecto.

1. Abre el Explorador de Archivos y dirígete a la ruta:
   ```text
   C:\Users\SAM\Documents\max\bodega_curso\api_curso\public\coverage\index.html
   ```
2. Haz doble clic en el archivo index.html para abrirlo en tu navegador web. Desde ahí puedes navegar por la estructura del proyecto e inspeccionar en color verde las líneas de código ejecutadas por las pruebas.

### Método B: Acceder desde el servidor local HTTP
Si el contenedor de la API expone un puerto web local (como el puerto 8000 u 80 según el docker-compose.yml):

1. Abre el navegador web e ingresa a:
   ```text
   http://localhost:8000/coverage/index.html
   ```

---

## 3. Endpoints HTTP evaluados por las pruebas

La suite de pruebas en [tests/Feature/ApiCoverageTest.php](api_curso/tests/Feature/ApiCoverageTest.php) cubre los siguientes flujos transaccionales y de validación:

1. **Categorías (/api/categorias)**:
   - Consulta general (GET).
   - Registro de nueva categoría (POST) y validación de campos obligatorios (422).
   - Búsqueda por ID (GET) con validación de existencia (404).
   - Actualización de datos (PUT).
   - Eliminación (DELETE): Verifica el bloqueo (400) si la categoría tiene productos vinculados, y la eliminación correcta (200) cuando no tiene dependencias.

2. **Clientes (/api/clientes)**:
   - Flujo completo de CRUD (GET, POST, SHOW, PUT, DELETE) con validaciones de datos (422) y manejo de registros no encontrados (404).
   - Validación de restricción transaccional (400) al intentar eliminar un cliente que tiene historial de ventas.

3. **Productos e Inventario (/api/productos y /api/productos/bajo-stock)**:
   - Creación y edición con código de barras, categoría asociada, stock inicial, precio de compra y precio de venta.
   - Consulta del endpoint de alertas /api/productos/bajo-stock para identificar productos cuyo stock actual es menor o igual al stock mínimo.
   - Verificación de la baja lógica: Si se intenta eliminar (DELETE) un producto que ya tiene transacciones o ventas registradas, el controlador lo desactiva (estado = false) en lugar de borrarlo físicamente y devuelve un código 200 con el mensaje de confirmación.

4. **Tipos de Comprobante (/api/tipo-comprobantes)**:
   - Operaciones de lectura, creación, edición y borrado para boletas y facturas.
   - Bloqueo de eliminación cuando el tipo de comprobante ya fue utilizado en una venta.

5. **Movimientos de Inventario (/api/inventario/movimientos y /api/motivos-movimiento)**:
   - Registro de entradas y salidas de mercancía con actualización automática y consistente de la columna stock_actual en la tabla de productos.
   - Control de error transaccional (400) cuando se intenta registrar una salida con una cantidad superior al stock disponible.

6. **Ventas (/api/ventas y /api/ventas/{id})**:
   - Registro transaccional de ventas con generación automática de correlativos y prefijos (ejemplo: B001-000001, F001-000001) según el tipo de comprobante seleccionado.
   - Cálculo automático de subtotal, IGV (18%) y total en el servidor.
   - Manejo de errores de negocio: rechazo de ventas que incluyan productos inactivos o productos con stock insuficiente (400).

7. **Estadísticas del Dashboard (/api/dashboard/stats)**:
   - Consulta de indicadores generales: número de ventas del día, ingresos acumulados, conteo de productos, alertas de bajo stock y valorización total del inventario.

---

## 4. Configuración técnica del entorno de pruebas

Para lograr un tiempo de ejecución rápido (menos de 9 segundos para 64 pruebas funcionales) sin afectar los datos del entorno de desarrollo o staging en MySQL, el archivo [phpunit.xml](api_curso/phpunit.xml) configura una base de datos temporal en memoria utilizando SQLite:

```xml
<env name="DB_CONNECTION" value="sqlite" force="true"/>
<env name="DB_DATABASE" value=":memory:" force="true"/>
<env name="CACHE_STORE" value="array" force="true"/>
```

Al ejecutarse en memoria RAM, cada prueba inicia con una base de datos limpia y aislada, evitando problemas de concurrencia o conflictos de claves foráneas con datos preexistentes.
