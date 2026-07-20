<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TipoComprobanteController;

// ==========================================
// RUTAS DE LA API (Sin Autenticación)
// ==========================================

// Rutas de Autenticación (Login / Logout / Perfil)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// -- Dashboard --
// Obtiene estadísticas generales para la pantalla principal (ventas, stock, etc.)
Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

// -- Mantenimientos Básicos (CRUD completo) --
// Categorías (GET, POST, PUT, DELETE /categorias)
Route::apiResource('categorias', CategoriaController::class);

// Clientes (GET, POST, PUT, DELETE /clientes)
Route::apiResource('clientes', ClienteController::class);

// Productos
// Obtiene solo los productos que están con stock por debajo del mínimo permitido
Route::get('productos/bajo-stock', [ProductoController::class, 'lowStock']);
// CRUD completo de productos
Route::apiResource('productos', ProductoController::class);


// -- Operaciones de Negocio --

// Ventas
// Tipo de Comprobantes
Route::apiResource('tipo-comprobantes', TipoComprobanteController::class);
// Permite listar, ver el detalle y crear (store) una nueva venta. No se permite editar/borrar por consistencia.
Route::apiResource('ventas', VentaController::class)->only(['index', 'show', 'store']);

// Movimientos de Inventario (Entradas y Salidas)
// Lista los motivos por los que se puede mover el stock (Ej: Compra, Merma, Ajuste)
Route::get('motivos-movimiento', [MovimientoController::class, 'getMotivos']);
// Permite listar y registrar nuevos movimientos de stock manualmente
Route::apiResource('inventario/movimientos', MovimientoController::class)->only(['index', 'store']);

