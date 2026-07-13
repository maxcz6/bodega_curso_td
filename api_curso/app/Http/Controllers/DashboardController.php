<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats()
    {
        // 1. Total ventas de hoy
        $ventasHoy = Venta::whereDate('fecha_venta', now()->toDateString())->sum('total');

        // 2. Total productos activos
        $totalProductos = Producto::where('estado', true)->count();

        // 3. Productos con bajo stock
        $bajoStock = Producto::where('estado', true)
            ->where('stock_actual', '<=', DB::raw('stock_minimo'))
            ->count();

        // 4. Ventas de los últimos 7 días (procesado en PHP para ser 100% agnóstico de base de datos)
        $ventasUltimos7DiasRaw = Venta::where('fecha_venta', '>=', now()->subDays(6)->startOfDay())->get();

        $ventasPorFecha = $ventasUltimos7DiasRaw->groupBy(function ($venta) {
            return date('Y-m-d', strtotime($venta->fecha_venta));
        });

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $grupo = $ventasPorFecha->get($date);
            $total = $grupo ? $grupo->sum('total') : 0.0;
            
            $chartData[] = [
                'fecha' => now()->subDays($i)->format('d/m'),
                'total' => floatval($total)
            ];
        }

        // 5. Top 5 productos más vendidos
        $topProductosRaw = DetalleVenta::select('id_producto', DB::raw('SUM(cantidad) as total_vendido'))
            ->groupBy('id_producto')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();

        $topProductos = [];
        foreach ($topProductosRaw as $item) {
            $producto = Producto::find($item->id_producto);
            if ($producto) {
                $topProductos[] = [
                    'nombre' => $producto->nombre,
                    'cantidad' => intval($item->total_vendido),
                    'total_monto' => round($item->total_vendido * $producto->precio_venta, 2)
                ];
            }
        }

        // 6. Alertas de bajo stock (productos detallados)
        $bajoStockProductos = Producto::with('categoria')
            ->where('estado', true)
            ->where('stock_actual', '<=', DB::raw('stock_minimo'))
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ventas_hoy' => floatval($ventasHoy),
                'total_productos' => $totalProductos,
                'bajo_stock_count' => $bajoStock,
                'ventas_semanales' => $chartData,
                'top_productos' => $topProductos,
                'bajo_stock_productos' => $bajoStockProductos
            ]
        ]);
    }
}
