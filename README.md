# Bodega api 

Este proyecto se levanta completamente con Docker. No requiere ejecución local de PHP, Composer, Node.js ni MySQL fuera de los contenedores.

La arquitectura usada es:

- 1 contenedor para MySQL
- 1 contenedor para la API Laravel
- 1 contenedor para el frontend React/Vite

En total hay 3 contenedores.

---

## Requisitos

Debes tener instalado:

- Docker Desktop
- Docker Compose

Además, Docker Desktop debe estar abierto y funcionando antes de iniciar el sistema.

---

## Pasos para iniciar el sistema con Docker

### 1. Abrir Docker Desktop

Antes de ejecutar cualquier comando, asegúrate de que Docker Desktop esté iniciado.

### 2. Entrar a la carpeta del proyecto

Ejemplo:

```bash
cd c:\Users\SAM\Documents\max\bodega_curso
```

### 3. Construir y levantar los contenedores

```bash
docker compose up -d --build
```

Este comando hará lo siguiente:

- crear la base de datos MySQL
- instalar dependencias de Laravel
- generar la clave de la aplicación
- ejecutar migraciones
- levantar la API en el puerto 8000
- levantar el frontend en el puerto 5173

### 4. Verificar que todo está corriendo

```bash
docker compose ps
```

Debes ver los servicios `db`, `api` y `frontend` activos.

### 5. Abrir la aplicación

- Frontend: http://localhost:5173
- API: http://localhost:8000

---

## Qué hace cada contenedor

### Contenedor de base de datos MySQL

Se encarga de guardar los datos del sistema.

### Contenedor de la API Laravel

Se encarga de ejecutar Laravel y exponer la API.

### Contenedor del frontend React/Vite

Se encarga de mostrar la interfaz del sistema.

---

## Comandos útiles de Docker

### Levantar el sistema

```bash
docker compose up -d --build
```

### Ver logs

```bash
docker compose logs -f
```

### Detener los contenedores

```bash
docker compose down
```

### Detener y borrar también la base de datos

```bash
docker compose down -v
```

---

## Comandos de Laravel dentro del contenedor API

Si necesitas ejecutar comandos de Laravel, puedes hacerlo desde el contenedor de la API.

### Generar la clave de la aplicación

```bash
docker compose exec api php artisan key:generate
```

### Ejecutar migraciones

```bash
docker compose exec api php artisan migrate
```

### Reiniciar las migraciones desde cero

```bash
docker compose exec api php artisan migrate:fresh
```

### Ver estado de migraciones

```bash
docker compose exec api php artisan migrate:status
```

### Iniciar el servidor de Laravel manualmente

```bash
docker compose exec api php artisan serve --host=0.0.0.0 --port=8000
```

> En este proyecto el servidor ya se inicia automáticamente con Docker.

---

## Rutas de ejemplo de la API

Las rutas de la API se acceden con el prefijo `/api`.

Ejemplos:

- `GET /api/dashboard/stats`
- `GET /api/categorias`
- `POST /api/categorias`
- `GET /api/productos`
- `GET /api/ventas`
- `GET /api/inventario/movimientos`

Estas rutas son solo ejemplos de uso para probar la API desde el navegador o desde Postman/Insomnia.

---

## Problemas comunes

### Docker Desktop no está abierto

Si aparece un error de conexión con Docker, abre Docker Desktop y vuelve a ejecutar:

```bash
docker compose up -d --build
```

### El puerto ya está ocupado

Si alguno de estos puertos ya está siendo usado:

- 3306
- 8000
- 5173

Debes detener el proceso que lo está ocupando o cambiar los puertos en el archivo `docker-compose.yml`.

### Error de migraciones

Si falla la base de datos al iniciar, puedes ejecutar:

```bash
docker compose exec api php artisan migrate:fresh
```

---

## Resumen rápido

Para iniciar todo el sistema:

```bash
cd c:\Users\SAM\Documents\max\bodega_curso
docker compose up -d --build
```

Luego abre:

- http://localhost:5173 para el frontend
- http://localhost:8000 para la API

```bash
docker compose down
```

Si además quieres borrar la base de datos persistida:

```bash
docker compose down -v
```

### Sin Docker

Cierra las terminales donde están corriendo `php artisan serve` y `npm run dev`.

---

## Problemas comunes

### Error de conexión a la base de datos

Verifica que:

- MySQL esté corriendo
- Las credenciales en `.env` sean correctas
- El puerto 3306 esté libre

### Error de clave de aplicación

Si Laravel muestra un error relacionado con la app key:

```bash
php artisan key:generate
```

### Error de migraciones

Si hay un problema con las tablas existentes:

```bash
php artisan migrate:fresh
```

### Docker no inicia

Verifica que:

- Docker Desktop esté abierto
- Tu computadora tenga recursos disponibles para contenedores
- No haya otro proceso usando los puertos 3306, 8000 o 5173

---

## Resumen rápido

### Con Docker

```bash
cd c:\Users\SAM\Documents\max\bodega_curso
docker compose up -d --build
```

Abrir:

- http://localhost:5173
- http://localhost:8000

### Sin Docker

```bash
cd api_curso
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Luego, en otra terminal:

```bash
cd ..\frontend_curso
npm install
npm run dev
```

---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

## Rutas de la API (Endpoints)

> Base URL: `http://localhost:8000`  
> Todas las rutas llevan el prefijo `/api`  
> Formato de datos: `Content-Type: application/json`

---

###  Dashboard
**Archivo:** `app/Http/Controllers/DashboardController.php`

Muestra estadísticas generales del sistema: ventas del día, productos activos, productos con bajo stock, gráfico de ventas de los últimos 7 días y top 5 productos más vendidos.

---

#### `GET /api/dashboard/stats`
Retorna un resumen completo del estado actual de la bodega.

**No requiere cuerpo (body)**

**Respuesta exitosa `200`:**
```json
{
  "success": true,
  "data": {
    "ventas_hoy": 250.00,
    "total_productos": 48,
    "bajo_stock_count": 3,
    "ventas_semanales": [
      { "fecha": "14/07", "total": 120.00 },
      { "fecha": "15/07", "total": 300.50 }
    ],
    "top_productos": [
      { "nombre": "Arroz Extra", "cantidad": 50, "total_monto": 175.00 }
    ],
    "bajo_stock_productos": [
      { "id_producto": 5, "nombre": "Azúcar", "stock_actual": 2, "stock_minimo": 5 }
    ]
  }
}
```

---

###  Categorías
**Archivo:** `app/Http/Controllers/CategoriaController.php`

Gestiona las categorías a las que pertenecen los productos (ej. Lácteos, Bebidas, Abarrotes).

---

#### `GET /api/categorias`
Lista todas las categorías registradas.

**No requiere cuerpo**

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    { "id_categoria": 1, "nombre": "Abarrotes", "descripcion": "Productos secos" },
    { "id_categoria": 2, "nombre": "Bebidas",   "descripcion": null }
  ]
}
```

---

#### `GET /api/categorias/{id}`
Obtiene una categoría por su ID.

**No requiere cuerpo**

**Respuesta `200`:**
```json
{
  "success": true,
  "data": { "id_categoria": 1, "nombre": "Abarrotes", "descripcion": "Productos secos" }
}
```

**Respuesta `404` — no existe:**
```json
{ "success": false, "message": "Categoría no encontrada" }
```

---

#### `POST /api/categorias`
Crea una nueva categoría.

**Body (JSON):**
```json
{
  "nombre": "Lácteos",
  "descripcion": "Leches, quesos y yogures"
}
```

| Campo         | Tipo   | Obligatorio | Reglas             |
|---------------|--------|-------------|--------------------|
| `nombre`      | string | Sí          | máx. 100 caracteres|
| `descripcion` | string | No          | máx. 255 caracteres|

**Respuesta `201`:**
```json
{
  "success": true,
  "message": "Categoría creada con éxito",
  "data": { "id_categoria": 3, "nombre": "Lácteos", "descripcion": "Leches, quesos y yogures" }
}
```

---

#### `PUT /api/categorias/{id}`
Actualiza una categoría existente.

**Body (JSON):**
```json
{
  "nombre": "Lácteos y Derivados",
  "descripcion": "Actualizado"
}
```

**Respuesta `200`:**
```json
{
  "success": true,
  "message": "Categoría actualizada con éxito",
  "data": { "id_categoria": 3, "nombre": "Lácteos y Derivados", "descripcion": "Actualizado" }
}
```

---

#### `DELETE /api/categorias/{id}`
Elimina una categoría. **Falla si tiene productos asociados.**

**No requiere cuerpo**

**Respuesta `200`:**
```json
{ "success": true, "message": "Categoría eliminada con éxito" }
```

**Respuesta `400` — tiene productos:**
```json
{ "success": false, "message": "No se puede eliminar la categoría porque contiene productos asociados" }
```

---

###  Clientes
**Archivo:** `app/Http/Controllers/ClienteController.php`

Gestiona el registro de clientes que realizan compras en la bodega.

---

#### `GET /api/clientes`
Lista todos los clientes.

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    { "id_cliente": 1, "nombres": "Juan Pérez", "dni_ruc": "12345678", "telefono": "999888777", "direccion": "Av. Lima 123" }
  ]
}
```

---

#### `GET /api/clientes/{id}`
Obtiene un cliente por su ID.

**Respuesta `404`:**
```json
{ "success": false, "message": "Cliente no encontrado" }
```

---

#### `POST /api/clientes`
Registra un nuevo cliente.

**Body (JSON):**
```json
{
  "nombres": "María García",
  "dni_ruc": "87654321",
  "telefono": "987654321",
  "direccion": "Jr. Cusco 456"
}
```

| Campo      | Tipo   | Obligatorio | Reglas             |
|------------|--------|-------------|--------------------|
| `nombres`  | string | Sí          | máx. 150 caracteres|
| `dni_ruc`  | string | No          | máx. 20 caracteres |
| `telefono` | string | No          | máx. 20 caracteres |
| `direccion`| string | No          | máx. 255 caracteres|

**Respuesta `201`:**
```json
{
  "success": true,
  "message": "Cliente creado con éxito",
  "data": { "id_cliente": 5, "nombres": "María García", "dni_ruc": "87654321", "telefono": "987654321", "direccion": "Jr. Cusco 456" }
}
```

---

#### `PUT /api/clientes/{id}`
Actualiza los datos de un cliente.

**Body (JSON):** *(mismos campos que POST)*

---

#### `DELETE /api/clientes/{id}`
Elimina un cliente. **Falla si tiene ventas registradas.**

**Respuesta `400`:**
```json
{ "success": false, "message": "No se puede eliminar el cliente porque tiene ventas registradas a su nombre" }
```

---

### Productos
**Archivo:** `app/Http/Controllers/ProductoController.php`

Gestiona el catálogo de productos de la bodega con sus precios y stock.

---

#### `GET /api/productos`
Lista todos los productos con su categoría.

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id_producto": 1,
      "nombre": "Arroz Extra",
      "codigo_barras": "7750000001",
      "precio_venta": "3.50",
      "precio_compra": "2.00",
      "stock_actual": 80,
      "stock_minimo": 10,
      "estado": true,
      "id_categoria": 1,
      "categoria": { "id_categoria": 1, "nombre": "Abarrotes" }
    }
  ]
}
```

---

#### `GET /api/productos/{id}`
Obtiene un producto con su categoría.

**Respuesta `404`:**
```json
{ "success": false, "message": "Producto no encontrado" }
```

---

#### `POST /api/productos`
Registra un nuevo producto.

**Body (JSON):**
```json
{
  "nombre": "Leche Gloria",
  "codigo_barras": "7751234000001",
  "descripcion": "Leche entera evaporada 400g",
  "precio_compra": 2.80,
  "precio_venta": 4.00,
  "stock_actual": 50,
  "stock_minimo": 10,
  "estado": true,
  "id_categoria": 2
}
```

| Campo           | Tipo    | Obligatorio | Reglas                                 |
|-----------------|---------|-------------|----------------------------------------|
| `nombre`        | string  |  Sí         | máx. 150 caracteres                    |
| `precio_compra` | decimal |  Sí         | mayor o igual a 0                      |
| `precio_venta`  | decimal |  Sí         | mayor o igual a 0                      |
| `id_categoria`  | integer |  Sí         | debe existir en la tabla `categorias`  |
| `codigo_barras` | string  |  No         | único, máx. 50 caracteres              |
| `descripcion`   | string  |  No         | texto libre                            |
| `stock_actual`  | integer |  No         | por defecto: `0`                       |
| `stock_minimo`  | integer |  No         | por defecto: `5`                       |
| `estado`        | boolean |  No         | por defecto: `true`                    |

**Respuesta `201`:**
```json
{
  "success": true,
  "message": "Producto creado con éxito",
  "data": { "id_producto": 10, "nombre": "Leche Gloria", "precio_venta": "4.00", ... }
}
```

---

#### `PUT /api/productos/{id}`
Actualiza un producto existente.

**Body (JSON):** *(mismos campos que POST)*

---

#### `DELETE /api/productos/{id}`
Elimina un producto. Si tiene historial (ventas o movimientos), **solo lo desactiva** en lugar de borrarlo.

**Respuesta `200` — eliminado:**
```json
{ "success": true, "message": "Producto eliminado de forma permanente con éxito" }
```

**Respuesta `200` — desactivado por historial:**
```json
{ "success": true, "message": "El producto tiene historial de transacciones. Se ha desactivado en vez de eliminarse de forma permanente." }
```

---

#### `GET /api/productos/bajo-stock`
Lista los productos cuyo `stock_actual` es menor o igual a su `stock_minimo`.

**No requiere cuerpo**

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    { "id_producto": 5, "nombre": "Azúcar", "stock_actual": 2, "stock_minimo": 5, "categoria": { "nombre": "Abarrotes" } }
  ]
}
```

---

###  Ventas
**Archivo:** `app/Http/Controllers/VentaController.php`

Registra y consulta ventas. Al crear una venta, automáticamente descuenta el stock de cada producto vendido y genera un movimiento de inventario (tipo SALIDA).

---

#### `GET /api/ventas`
Lista las últimas 200 ventas ordenadas de más reciente a más antigua.

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id_venta": 15,
      "numero_comprobante": "B001-000015",
      "fecha_venta": "2026-07-20T13:00:00",
      "total": "35.00",
      "id_cliente": 2,
      "id_tipo_comprobante": 1,
      "cliente": { "id_cliente": 2, "nombres": "Juan Pérez" },
      "tipoComprobante": { "id_tipo_comprobante": 1, "nombre": "Boleta" }
    }
  ]
}
```

---

#### `GET /api/ventas/{id}`
Obtiene el detalle completo de una venta, incluyendo cada producto vendido.

**Respuesta `200`:**
```json
{
  "success": true,
  "data": {
    "id_venta": 15,
    "numero_comprobante": "B001-000015",
    "subtotal": "35.00",
    "igv": "0.00",
    "total": "35.00",
    "cliente": { "nombres": "Juan Pérez" },
    "tipoComprobante": { "nombre": "Boleta" },
    "detalles": [
      {
        "id_detalle": 20,
        "cantidad": 3,
        "precio_unitario": "3.50",
        "subtotal": "10.50",
        "producto": { "nombre": "Arroz Extra", "codigo_barras": "7750000001" }
      }
    ]
  }
}
```

---

#### `POST /api/ventas`
Registra una nueva venta. Genera el número de comprobante automáticamente y descuenta stock.

**Body (JSON):**
```json
{
  "id_cliente": 2,
  "id_tipo_comprobante": 1,
  "items": [
    { "id_producto": 1, "cantidad": 3 },
    { "id_producto": 4, "cantidad": 1 }
  ]
}
```

| Campo                 | Tipo    | Obligatorio | Descripción                                      |
|-----------------------|---------|-------------|--------------------------------------------------|
| `id_cliente`          | integer |  Sí         | debe existir en `clientes`                       |
| `id_tipo_comprobante` | integer |  Sí         | `1`=Boleta, `2`=Factura                          |
| `items`               | array   |  Sí         | mínimo 1 ítem                                    |
| `items[].id_producto` | integer |  Sí         | debe existir y estar activo                      |
| `items[].cantidad`    | integer |  Sí         | mínimo `1`, no puede superar el stock disponible |

> Si `id_tipo_comprobante = 2` (Factura), el IGV del 18% se desglosa automáticamente.

**Respuesta `201`:**
```json
{
  "success": true,
  "message": "Venta registrada con éxito",
  "data": { "id_venta": 16, "numero_comprobante": "B001-000016", "total": "20.50", ... }
}
```

**Respuesta `400` — stock insuficiente o producto inactivo:**
```json
{
  "success": false,
  "message": "Error al registrar la venta",
  "error": "Stock insuficiente para el producto 'Arroz Extra'. Disponible: 2, Solicitado: 10"
}
```

---

### Inventario / Movimientos
**Archivo:** `app/Http/Controllers/MovimientoController.php`

Registra entradas y salidas manuales de stock. Cada movimiento actualiza el `stock_actual` del producto.

---

#### `GET /api/motivos-movimiento`
Lista todos los motivos disponibles (ej. Compra, Venta, Ajuste, Devolución).

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    { "id_motivo": 1, "nombre": "Compra" },
    { "id_motivo": 2, "nombre": "Venta" },
    { "id_motivo": 3, "nombre": "Ajuste de inventario" }
  ]
}
```

---

#### `GET /api/movimientos`
Lista todos los movimientos. Acepta filtros opcionales por query string.

**Filtros opcionales (query params):**

| Parámetro | Descripción | Ejemplo |
|---|---|---|
| `id_producto` | Filtra por producto | `?id_producto=3` |
| `tipo_movimiento` | `ENTRADA` o `SALIDA` | `?tipo_movimiento=SALIDA` |
| `id_motivo` | Filtra por motivo | `?id_motivo=1` |

**Ejemplo:** `GET /api/movimientos?tipo_movimiento=ENTRADA&id_producto=3`

**Respuesta `200`:**
```json
{
  "success": true,
  "data": [
    {
      "id_movimiento": 10,
      "tipo_movimiento": "ENTRADA",
      "cantidad": 20,
      "stock_anterior": 30,
      "stock_nuevo": 50,
      "fecha_movimiento": "2026-07-20T10:00:00",
      "observaciones": "Compra a proveedor",
      "producto": { "nombre": "Arroz Extra" },
      "motivo": { "nombre": "Compra" },
      "usuario": { "nombres": "Admin" }
    }
  ]
}
```

---

#### `POST /api/movimientos`
Registra un movimiento manual de inventario (entrada o salida).

**Body (JSON):**
```json
{
  "id_producto": 1,
  "id_motivo": 1,
  "tipo_movimiento": "ENTRADA",
  "cantidad": 20,
  "observaciones": "Compra a proveedor ABC"
}
```

| Campo             | Tipo    | Obligatorio | Reglas                                |
|-------------------|---------|-------------|---------------------------------------|
| `id_producto`     | integer |  Sí         | debe existir en `productos`           |
| `id_motivo`       | integer |  Sí         | debe existir en `motivos_movimiento`  |
| `tipo_movimiento` | string  |  Sí         | solo `"ENTRADA"` o `"SALIDA"`         |
| `cantidad`        | integer |  Sí         | mínimo `1`                            |
| `observaciones`   | string  |  No         | texto libre                           |

> Una `SALIDA` falla si la cantidad solicitada supera el stock disponible.

**Respuesta `201`:**
```json
{
  "success": true,
  "message": "Movimiento de inventario registrado con éxito",
  "data": {
    "id_movimiento": 11,
    "tipo_movimiento": "ENTRADA",
    "cantidad": 20,
    "stock_anterior": 30,
    "stock_nuevo": 50,
    "producto": { "nombre": "Arroz Extra" },
    "motivo": { "nombre": "Compra" }
  }
}
```

**Respuesta `400` — stock insuficiente:**
```json
{
  "success": false,
  "message": "Error al registrar el movimiento",
  "error": "Stock insuficiente para realizar la salida. Disponible: 5, Solicitado: 20"
}
```

---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------


## Prueba de Cobertura de Código

El proyecto incluye pruebas unitarias y pruebas de integración (Feature) que verifican el funcionamiento de la API.
Se ha generado un reporte de cobertura que muestra el porcentaje de código ejecutado por estas pruebas.

### ¿Cómo ejecutar las pruebas y ver el reporte de cobertura?

Para ejecutar todas las pruebas (incluida la prueba de cobertura) y generar el reporte HTML, utiliza el siguiente comando en tu terminal dentro del contenedor Docker:

```bash
# Ejecutar todas las pruebas con reporte de cobertura
docker exec bodega_api php artisan test --coverage-text --coverage-html=public/coverage
```

Esto mostrará el resultado en texto en la terminal y también generará un reporte HTML detallado en la carpeta `public/coverage`.

### ¿Cómo ver el reporte en el navegador?

Una vez que las pruebas se hayan ejecutado y el reporte se haya generado, puedes acceder al reporte de cobertura en tu navegador a través de la siguiente URL (asegúrate de reemplazar el puerto si usas uno diferente):

```
http://localhost:8000/coverage/index.html
```

Este reporte te permitirá ver qué partes de tu código están siendo cubiertas por las pruebas y cuáles no.

## Pruebas Unitarias (PHPUnit)

El proyecto incluye 40 pruebas unitarias escritas con PHPUnit, agrupadas en un solo archivo.
ruta : api_curso/tests/Unit/PruebasTest.php


### Ubicación del archivo

```
api_curso/
└── tests/
    └── Unit/
        └── PruebasTest.php 
```

### ¿Qué tipo de pruebas son?

 *   [CONFIG]    Configuracion    → verifica tabla, clave primaria del modelo
 *   [CAMPO]     Campo fillable   → verifica que el campo se puede guardar
 *   [SEGURO]    Seguridad        → verifica que datos sensibles estan ocultos
 *   [MINIMO]    Valor minimo     → verifica el valor mas bajo permitido
 *   [MAXIMO]    Valor maximo     → verifica el valor mas alto esperado
 *   [LIMITE]    Limite exacto    → prueba justo en el borde valido/invalido
 *   [NEGATIVO]  Caso negativo    → verifica que un valor no sea negativo
 *   [POSITIVO]  Caso positivo    → verifica que el resultado sea mayor a 0
 *   [MATRIZ]    Data Provider    → una funcion prueba muchos casos distintos
---

### Pasos para ejecutar las pruebas

#### Paso 1 — Asegúrate de que el contenedor de la API esté corriendo

```bash
docker compose up -d
```

Verifica que el contenedor `api` esté activo:

```bash
docker compose ps
```

#### Paso 2 — Ejecutar todas las pruebas del archivo

```bash
docker exec bodega_api php artisan test --filter=PruebasTest
```

#### Paso 3 — Ver el resultado con nombre de cada prueba

```bash
docker exec bodega_api php artisan test --filter=PruebasTest --debug
```

#### Paso 4 — Detener al primer fallo (útil para depurar)

```bash
docker exec bodega_api php artisan test --filter=PruebasTest --stop-on-failure
```

#### Paso 5 — Ejecutar todo el suite Unit (incluye otras pruebas unitarias)

```bash
docker exec bodega_api php artisan test --testsuite=Unit
```

---

### Ejecución con phpunit.standalone.xml

```bash
docker exec bodega_api vendor/bin/phpunit --configuration phpunit.standalone.xml --filter=PruebasTest
```
o
```bash
docker exec bodega_api php artisan test --filter=PruebasTest
```


### ¿Qué verifica cada bloque de pruebas?

#### BLOQUE 1 — Modelo `Producto` (pruebas 1 al 8)

Verifica que el modelo `Producto` esté bien configurado.

| # | Prueba | Qué revisa |
|---|---|---|
| 1 | `test_01_producto_apunta_a_tabla_correcta` | El modelo apunta a la tabla `productos` en la BD |
| 2 | `test_02_producto_tiene_clave_primaria_id_producto` | La clave primaria es `id_producto`, no el genérico `id` |
| 3 | `test_03_producto_no_usa_timestamps` | La tabla no tiene columnas `created_at` / `updated_at` |
| 4 | `test_04_producto_tiene_nombre_en_fillable` | El campo `nombre` puede asignarse masivamente |
| 5 | `test_05_producto_tiene_precio_venta_en_fillable` | El campo `precio_venta` puede guardarse al crear/actualizar |
| 6 | `test_06_producto_tiene_precio_compra_en_fillable` | El campo `precio_compra` puede guardarse |
| 7 | `test_07_producto_tiene_stock_actual_en_fillable` | El stock puede actualizarse con `update()` |
| 8 | `test_08_producto_tiene_id_categoria_en_fillable` | La FK `id_categoria` puede guardarse para relacionar categorías |

---

#### BLOQUE 2 — Modelo `Cliente` (pruebas 9 al 14)

Verifica que el modelo `Cliente` tenga correctamente definidos su tabla, PK y campos.

| # | Prueba | Qué revisa |
|---|---|---|
| 9  | `test_09_cliente_apunta_a_tabla_correcta` | Tabla correcta: `clientes` |
| 10 | `test_10_cliente_tiene_clave_primaria_id_cliente` | PK es `id_cliente` |
| 11 | `test_11_cliente_no_usa_timestamps` | Sin `created_at` / `updated_at` |
| 12 | `test_12_cliente_tiene_nombres_en_fillable` | Campo `nombres` es asignable |
| 13 | `test_13_cliente_tiene_dni_ruc_en_fillable` | Campo `dni_ruc` es asignable |
| 14 | `test_14_cliente_tiene_telefono_en_fillable` | Campo `telefono` es asignable |

---

#### BLOQUE 3 — Modelo `Venta` (pruebas 15 al 20)

Verifica la configuración del modelo de ventas y sus campos financieros.

| # | Prueba | Qué revisa |
|---|---|---|
| 15 | `test_15_venta_apunta_a_tabla_correcta` | Tabla correcta: `ventas` |
| 16 | `test_16_venta_tiene_clave_primaria_id_venta` | PK es `id_venta` |
| 17 | `test_17_venta_tiene_numero_comprobante_en_fillable` | El nro. de comprobante (ej. `B001-000001`) puede guardarse |
| 18 | `test_18_venta_tiene_total_en_fillable` | El total de la venta puede guardarse |
| 19 | `test_19_venta_tiene_igv_en_fillable` | El IGV calculado puede guardarse |
| 20 | `test_20_venta_tiene_subtotal_en_fillable` | El subtotal puede guardarse |

---

#### BLOQUE 4 — Modelo `Categoria` (pruebas 21 al 24)

| # | Prueba | Qué revisa |
|---|---|---|
| 21 | `test_21_categoria_apunta_a_tabla_correcta` | Tabla correcta: `categorias` |
| 22 | `test_22_categoria_tiene_clave_primaria_id_categoria` | PK es `id_categoria` |
| 23 | `test_23_categoria_tiene_nombre_en_fillable` | Campo `nombre` es asignable |
| 24 | `test_24_categoria_tiene_descripcion_en_fillable` | Campo `descripcion` es asignable |

---

#### BLOQUE 5 — Modelo `MovimientoInventario` (pruebas 25 al 29)

Verifica el modelo que registra cada entrada y salida de stock.

| # | Prueba | Qué revisa |
|---|---|---|
| 25 | `test_25_movimiento_apunta_a_tabla_correcta` | Tabla: `movimientos_inventario` |
| 26 | `test_26_movimiento_tiene_clave_primaria_id_movimiento` | PK es `id_movimiento` |
| 27 | `test_27_movimiento_tiene_tipo_movimiento_en_fillable` | Campo `tipo_movimiento` (ENTRADA/SALIDA) es asignable |
| 28 | `test_28_movimiento_tiene_stock_anterior_en_fillable` | El stock antes del movimiento se puede guardar |
| 29 | `test_29_movimiento_tiene_stock_nuevo_en_fillable` | El stock resultante se puede guardar |

---

#### BLOQUE 6 — Modelo `User` (pruebas 30 al 33)

Verifica la configuración del modelo de usuarios, especialmente la seguridad del password.

| # | Prueba | Qué revisa |
|---|---|---|
| 30 | `test_30_user_apunta_a_tabla_usuarios` | La tabla es `usuarios` (no el genérico `users` de Laravel) |
| 31 | `test_31_user_tiene_clave_primaria_id_usuario` | PK es `id_usuario` |
| 32 | `test_32_user_oculta_el_password_en_respuestas_json` | El campo `password` está en `$hidden` y no aparece en JSON |
| 33 | `test_33_user_tiene_username_en_fillable` | El `username` puede guardarse (necesario para el login) |

---

#### BLOQUE 7 — Modelo `DetalleVenta` (pruebas 34 al 36)

Verifica el modelo que guarda cada línea (ítem) de una venta.

| # | Prueba | Qué revisa |
|---|---|---|
| 34 | `test_34_detalle_venta_apunta_a_tabla_correcta` | Tabla: `detalle_venta` |
| 35 | `test_35_detalle_venta_tiene_precio_unitario_en_fillable` | El precio al momento de la venta puede guardarse |
| 36 | `test_36_detalle_venta_tiene_cantidad_en_fillable` | La cantidad vendida puede guardarse |

---

#### BLOQUE 8 — Modelos `TipoComprobante` y `MotivoMovimiento` (pruebas 37 y 38)

| # | Prueba | Qué revisa |
|---|---|---|
| 37 | `test_37_tipo_comprobante_apunta_a_tabla_correcta` | Tabla: `tipo_comprobante` (la tabla no termina en `s`) |
| 38 | `test_38_motivo_movimiento_apunta_a_tabla_correcta` | Tabla: `motivos_movimiento` |

---

#### BLOQUE 9 — Lógica de negocio pura (pruebas 39 y 40)

Estas pruebas no tocan modelos. Prueban cálculos matemáticos del sistema directamente en PHP.

| # | Prueba | Qué revisa | Cómo lo hace |
|---|---|---|---|
| 39 | `test_39_calculo_igv_factura_es_correcto` | Que el IGV del 18% se calcule bien en facturas | Aplica la fórmula `subtotal = total / 1.18`, verifica que de S/ 118 → subtotal=100, igv=18 |
| 40 | `test_40_calculo_stock_despues_de_salida_es_correcto` | Que el stock se reste correctamente al vender | Simula stock=50, venta=12, verifica que el nuevo stock sea 38 y no sea negativo |

---

### ¿Cómo funcionan las pruebas internamente?

Cada prueba sigue este patrón de 3 pasos (AAA — Arrange, Act, Assert):

```
1. ARRANGE  → Crear el modelo o los datos de entrada
2. ACT      → Ejecutar la acción que quiero probar
3. ASSERT   → Verificar que el resultado es el esperado
```

Ejemplo de una prueba del archivo:

```php
/** @test */
public function test_01_producto_apunta_a_tabla_correcta(): void
{
    // ARRANGE: creo el modelo vacío (sin BD)
    $modelo = new Producto();

    // ACT + ASSERT: verifico que la tabla sea la correcta
    $this->assertEquals('productos', $modelo->getTable());
}
```

```php
/** @test */
public function test_39_calculo_igv_factura_es_correcto(): void
{
    // ARRANGE: total que ya incluye el 18% de IGV
    $totalConIgv = 118.00;

    // ACT: aplicar la formula del sistema
    $subtotal = round($totalConIgv / 1.18, 2);
    $igv      = round($totalConIgv - $subtotal, 2);

    // ASSERT: verificar los resultados esperados
    $this->assertEquals(100.00, $subtotal);
    $this->assertEquals(18.00, $igv);
}
```

---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------
---------------------------------------------------------------------------------------------------------------------------

# Generar documentación de la API usando Laravel Scribe

## Introducción
El comando `php artisan scribe:generate` pertenece a un paquete llamado **Laravel Scribe**. Este paquete no es una herramienta para pruebas unitarias, sino una herramienta diseñada para **generar documentación de tu API**. 

Su función principal es crear automáticamente una página web interactiva y presentable que detalla todos los endpoints, parámetros y ejemplos de respuesta de tu aplicación.

A continuación, se detallan los pasos para instalar y utilizar Laravel Scribe en un entorno de desarrollo utilizando Docker.

---

## Guía de Instalación y Uso

### 1. Instalar el paquete mediante Composer
Para comenzar, debes instalar Scribe en tu proyecto Laravel. Dado que el entorno está basado en Docker, el comando debe ejecutarse a través del contenedor:

```bash
docker exec bodega_api composer require --dev knuckleswtf/scribe
```
> **Nota:** Se instala con la bandera `--dev` porque la generación de documentación es una tarea que normalmente solo se realiza en entornos de desarrollo local.

### 2. Publicar el archivo de configuración
El siguiente paso es publicar la configuración de Scribe. Esto creará un archivo en `config/scribe.php`, desde el cual podrás personalizar la apariencia de tu documentación y definir qué rutas debe procesar la herramienta:

```bash
docker exec bodega_api php artisan vendor:publish --tag=scribe-config
```

### 3. Generar la documentación
Una vez que tengas tus rutas y controladores definidos (y opcionalmente comentados usando las etiquetas y anotaciones específicas de Scribe), puedes ejecutar el comando principal para construir la documentación:

```bash
docker exec bodega_api php artisan scribe:generate
```

### 4. Ver la documentación
Por defecto, Scribe genera los archivos estáticos y los coloca en la carpeta `public/docs`. 

Para visualizar la documentación generada, simplemente abre tu navegador web y accede a la URL de tu proyecto seguida del path `/docs`. 

**Ejemplo de acceso:**
```text
http://localhost:8000/docs
```
