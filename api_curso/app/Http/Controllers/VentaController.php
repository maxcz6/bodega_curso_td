<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'usuario', 'tipoComprobante'])
            ->orderBy('id_venta', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ventas
        ]);
    }

    public function show($id)
    {
        $venta = Venta::with([
            'cliente',
            'usuario',
            'tipoComprobante',
            'detalles.producto.categoria'
        ])->find($id);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $venta
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'id_tipo_comprobante' => 'required|exists:tipo_comprobante,id_tipo_comprobante',
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación de datos de venta fallida',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = 1; // Admin

        try {
            $venta = DB::transaction(function () use ($request, $userId) {
                $id_tipo_comprobante = intval($request->id_tipo_comprobante);
                
                // 1. Generar número de comprobante único y correlativo
                $prefijo = ($id_tipo_comprobante === 1) ? 'B001' : 'F001';
                
                $ultimaVenta = Venta::where('id_tipo_comprobante', $id_tipo_comprobante)
                    ->where('numero_comprobante', 'like', $prefijo . '-%')
                    ->orderBy('id_venta', 'desc')
                    ->first();

                if ($ultimaVenta) {
                    $parts = explode('-', $ultimaVenta->numero_comprobante);
                    $num = intval($parts[1]) + 1;
                    $correlativo = str_pad($num, 6, '0', STR_PAD_LEFT);
                } else {
                    $correlativo = '000001';
                }
                
                $numero_comprobante = $prefijo . '-' . $correlativo;

                // 2. Procesar ítems y calcular totales
                $totalGeneral = 0.0;
                $detallesAInsertar = [];
                $productosAActualizar = [];

                foreach ($request->items as $item) {
                    $producto = Producto::find($item['id_producto']);
                    
                    if (!$producto->estado) {
                        throw new \Exception("El producto '{$producto->nombre}' está inactivo y no se puede vender.");
                    }

                    if ($producto->stock_actual < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para el producto '{$producto->nombre}'. Disponible: {$producto->stock_actual}, Solicitado: {$item['cantidad']}");
                    }

                    $subtotalItem = round($producto->precio_venta * $item['cantidad'], 2);
                    $totalGeneral += $subtotalItem;

                    $detallesAInsertar[] = [
                        'id_producto' => $producto->id_producto,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $producto->precio_venta,
                        'subtotal' => $subtotalItem,
                        'stock_actual_prod' => $producto->stock_actual, // para guardar stock anterior
                    ];

                    $productosAActualizar[] = [
                        'model' => $producto,
                        'cantidad' => $item['cantidad'],
                    ];
                }

                // Calcular Subtotal e IGV según comprobante
                if ($id_tipo_comprobante === 2) {
                    // Factura (desglosa el 18% del IGV)
                    // Total = Subtotal + IGV -> Subtotal = Total / 1.18
                    $subtotalVenta = round($totalGeneral / 1.18, 2);
                    $igvVenta = round($totalGeneral - $subtotalVenta, 2);
                } else {
                    // Boleta (no desglosa, total neto)
                    $subtotalVenta = round($totalGeneral, 2);
                    $igvVenta = 0.00;
                }

                // 3. Registrar cabecera de Venta
                $nuevaVenta = Venta::create([
                    'numero_comprobante' => $numero_comprobante,
                    'fecha_venta' => now(),
                    'subtotal' => $subtotalVenta,
                    'igv' => $igvVenta,
                    'total' => $totalGeneral,
                    'id_cliente' => $request->id_cliente,
                    'id_tipo_comprobante' => $id_tipo_comprobante,
                    'id_usuario' => $userId,
                ]);

                // 4. Registrar Detalles y disminuir stocks
                foreach ($detallesAInsertar as $det) {
                    DetalleVenta::create([
                        'id_venta' => $nuevaVenta->id_venta,
                        'id_producto' => $det['id_producto'],
                        'cantidad' => $det['cantidad'],
                        'precio_unitario' => $det['precio_unitario'],
                        'subtotal' => $det['subtotal'],
                    ]);

                    // Buscar el producto para actualizar su stock
                    $prodModel = Producto::find($det['id_producto']);
                    $stockAnterior = $prodModel->stock_actual;
                    $stockNuevo = $stockAnterior - $det['cantidad'];

                    $prodModel->update([
                        'stock_actual' => $stockNuevo
                    ]);

                    // Crear movimiento de inventario (tipo SALIDA, motivo 2 = Venta)
                    MovimientoInventario::create([
                        'id_producto' => $det['id_producto'],
                        'id_motivo' => 2, // Venta
                        'id_usuario' => $userId,
                        'tipo_movimiento' => 'SALIDA',
                        'cantidad' => $det['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'fecha_movimiento' => now(),
                        'observaciones' => "Salida por Venta - Comprobante " . $numero_comprobante,
                    ]);
                }

                return $nuevaVenta;
            });

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada con éxito',
                'data' => Venta::with(['cliente', 'usuario', 'tipoComprobante', 'detalles.producto'])->find($venta->id_venta)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getTipoComprobantes()
    {
        $tipos = TipoComprobante::all();
        return response()->json([
            'success' => true,
            'data' => $tipos
        ]);
    }
}
