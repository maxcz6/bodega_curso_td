<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\MotivoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'motivo', 'usuario'])
            ->orderBy('id_movimiento', 'desc');

        if ($request->has('id_producto')) {
            $query->where('id_producto', $request->id_producto);
        }

        if ($request->has('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }

        if ($request->has('id_motivo')) {
            $query->where('id_motivo', $request->id_motivo);
        }

        $movimientos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_producto' => 'required|exists:productos,id_producto',
            'id_motivo' => 'required|exists:motivos_movimiento,id_motivo',
            'tipo_movimiento' => 'required|in:ENTRADA,SALIDA',
            'cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación de movimiento fallida',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = 1; // Admin

        try {
            $movimiento = DB::transaction(function () use ($request, $userId) {
                $producto = Producto::find($request->id_producto);
                $stockAnterior = $producto->stock_actual;
                
                if ($request->tipo_movimiento === 'ENTRADA') {
                    $stockNuevo = $stockAnterior + $request->cantidad;
                } else {
                    if ($stockAnterior < $request->cantidad) {
                        throw new \Exception("Stock insuficiente para realizar la salida. Disponible: {$stockAnterior}, Solicitado: {$request->cantidad}");
                    }
                    $stockNuevo = $stockAnterior - $request->cantidad;
                }

                // Actualizar stock del producto
                $producto->update([
                    'stock_actual' => $stockNuevo
                ]);

                // Registrar movimiento
                $nuevoMovimiento = MovimientoInventario::create([
                    'id_producto' => $request->id_producto,
                    'id_motivo' => $request->id_motivo,
                    'id_usuario' => $userId,
                    'tipo_movimiento' => $request->tipo_movimiento,
                    'cantidad' => $request->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'fecha_movimiento' => now(),
                    'observaciones' => $request->observaciones,
                ]);

                return $nuevoMovimiento;
            });

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de inventario registrado con éxito',
                'data' => MovimientoInventario::with(['producto', 'motivo', 'usuario'])->find($movimiento->id_movimiento)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el movimiento',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getMotivos()
    {
        $motivos = MotivoMovimiento::all();
        return response()->json([
            'success' => true,
            'data' => $motivos
        ]);
    }
}
