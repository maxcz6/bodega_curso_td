# Bodega Curso

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

----------------------------------------------------------------
                    RUTAS PARA POSTMAN 
----------------------------------------------------------------
Rutas que puedes probar:

Dashboard

GET /api/dashboard/stats
controlador: DashboardController.php
Categorías

GET /api/categorias
GET /api/categorias/{id}
POST /api/categorias
PUT /api/categorias/{id}
DELETE /api/categorias/{id}
controlador: CategoriaController.php
Clientes

GET /api/clientes
GET /api/clientes/{id}
POST /api/clientes
PUT /api/clientes/{id}
DELETE /api/clientes/{id}
controlador: ClienteController.php
Productos

GET /api/productos
GET /api/productos/{id}
POST /api/productos
PUT /api/productos/{id}
DELETE /api/productos/{id}
GET /api/productos/bajo-stock
controlador: ProductoController.php
Ventas

GET /api/ventas
GET /api/ventas/{id}
POST /api/ventas
controlador: VentaController.php
Inventario

GET /api/motivos-movimiento
GET /api/inventario/movimientos
POST /api/inventario/movimientos
controlador: MovimientoController.php