<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')->get();
        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'estado' => 'nullable|boolean',
            'id_categoria' => 'required|exists:categorias,id_categoria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Valores predeterminados
        if (!isset($data['stock_actual'])) $data['stock_actual'] = 0;
        if (!isset($data['stock_minimo'])) $data['stock_minimo'] = 5;
        if (!isset($data['estado'])) $data['estado'] = true;

        $producto = Producto::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado con éxito',
            'data' => $producto->load('categoria')
        ], 201);
    }

    public function show($id)
    {
        $producto = Producto::with('categoria')->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras,' . $id . ',id_producto',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'estado' => 'nullable|boolean',
            'id_categoria' => 'required|exists:categorias,id_categoria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado con éxito',
            'data' => $producto->load('categoria')
        ]);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // Si tiene movimientos de inventario o detalles de venta asociados, no se puede eliminar de raíz.
        // En su lugar, lo desactivamos (deshabilitado lógico).
        $tieneVentas = $producto->detallesVenta()->count() > 0;
        $tieneMovimientos = $producto->movimientosInventario()->count() > 0;

        if ($tieneVentas || $tieneMovimientos) {
            $producto->update(['estado' => false]);
            return response()->json([
                'success' => true,
                'message' => 'El producto tiene historial de transacciones. Se ha desactivado en vez de eliminarse de forma permanente.'
            ]);
        }

        $producto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado de forma permanente con éxito'
        ]);
    }

    public function lowStock()
    {
        // Obtener productos con stock menor o igual al stock mínimo
        $productos = Producto::with('categoria')
            ->where('stock_actual', '<=', \DB::raw('stock_minimo'))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }
}
