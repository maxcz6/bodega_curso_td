<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Bebidas', 'descripcion' => 'Refrescos, jugos y aguas'],
            ['nombre' => 'Lácteos', 'descripcion' => 'Leches, yogures y quesos'],
            ['nombre' => 'Snacks', 'descripcion' => 'Botanas y bocaditos'],
            ['nombre' => 'Aseo', 'descripcion' => 'Productos de limpieza e higiene'],
            ['nombre' => 'Panadería', 'descripcion' => 'Productos de panadería fresca'],
        ];

        $categoriasCreadas = [];
        foreach ($categorias as $categoriaData) {
            $categoria = Categoria::firstOrCreate(
                ['nombre' => $categoriaData['nombre']],
                ['descripcion' => $categoriaData['descripcion']]
            );
            $categoriasCreadas[] = $categoria;
        }

        $productosBase = [
            [
                'nombre' => 'Coca Cola 2.5L',
                'descripcion' => 'Refresco sabor cola',
                'stock_actual' => 18,
                'stock_minimo' => 5,
                'precio_compra' => 4.50,
                'precio_venta' => 6.20,
                'estado' => true,
            ],
            [
                'nombre' => 'Inca Kola 1.5L',
                'descripcion' => 'Gaseosa peruana clásica',
                'stock_actual' => 12,
                'stock_minimo' => 4,
                'precio_compra' => 3.80,
                'precio_venta' => 5.50,
                'estado' => true,
            ],
            [
                'nombre' => 'Agua San Mateo 1L',
                'descripcion' => 'Agua mineral sin gas',
                'stock_actual' => 25,
                'stock_minimo' => 8,
                'precio_compra' => 1.20,
                'precio_venta' => 2.00,
                'estado' => true,
            ],
            [
                'nombre' => 'Jugo de Naranja 1L',
                'descripcion' => 'Bebida natural de naranja',
                'stock_actual' => 10,
                'stock_minimo' => 3,
                'precio_compra' => 3.20,
                'precio_venta' => 4.80,
                'estado' => true,
            ],
            [
                'nombre' => 'Leche Gloria 1L',
                'descripcion' => 'Leche entera pasteurizada',
                'stock_actual' => 15,
                'stock_minimo' => 5,
                'precio_compra' => 2.90,
                'precio_venta' => 4.10,
                'estado' => true,
            ],
            [
                'nombre' => 'Yogurt Natural 900g',
                'descripcion' => 'Yogur natural sabor a vainilla',
                'stock_actual' => 9,
                'stock_minimo' => 3,
                'precio_compra' => 4.10,
                'precio_venta' => 5.90,
                'estado' => true,
            ],
            [
                'nombre' => 'Queso Fresco 500g',
                'descripcion' => 'Queso fresco artesanal',
                'stock_actual' => 7,
                'stock_minimo' => 2,
                'precio_compra' => 5.60,
                'precio_venta' => 7.80,
                'estado' => true,
            ],
            [
                'nombre' => 'Papas Fritas Lays',
                'descripcion' => 'Snack salado clásico',
                'stock_actual' => 20,
                'stock_minimo' => 6,
                'precio_compra' => 2.10,
                'precio_venta' => 3.40,
                'estado' => true,
            ],
            [
                'nombre' => 'Chizitos',
                'descripcion' => 'Snack de maíz con queso',
                'stock_actual' => 16,
                'stock_minimo' => 4,
                'precio_compra' => 1.80,
                'precio_venta' => 2.90,
                'estado' => true,
            ],
            [
                'nombre' => 'Palomitas Saladas',
                'descripcion' => 'Palomitas de maíz saladas',
                'stock_actual' => 11,
                'stock_minimo' => 3,
                'precio_compra' => 2.30,
                'precio_venta' => 3.70,
                'estado' => true,
            ],
            [
                'nombre' => 'Detergente Ariel',
                'descripcion' => 'Detergente líquido para ropa',
                'stock_actual' => 14,
                'stock_minimo' => 4,
                'precio_compra' => 6.50,
                'precio_venta' => 9.20,
                'estado' => true,
            ],
            [
                'nombre' => 'Papel Higiénico Elite',
                'descripcion' => 'Rollo de papel higiénico',
                'stock_actual' => 22,
                'stock_minimo' => 7,
                'precio_compra' => 2.70,
                'precio_venta' => 4.10,
                'estado' => true,
            ],
            [
                'nombre' => 'Jabón Liquido',
                'descripcion' => 'Jabón líquido para manos',
                'stock_actual' => 13,
                'stock_minimo' => 4,
                'precio_compra' => 3.90,
                'precio_venta' => 5.60,
                'estado' => true,
            ],
            [
                'nombre' => 'Pan Integral',
                'descripcion' => 'Pan fresco integral',
                'stock_actual' => 8,
                'stock_minimo' => 3,
                'precio_compra' => 2.40,
                'precio_venta' => 3.60,
                'estado' => true,
            ],
            [
                'nombre' => 'Bollos de Yema',
                'descripcion' => 'Bollo tradicional de yema',
                'stock_actual' => 6,
                'stock_minimo' => 2,
                'precio_compra' => 1.50,
                'precio_venta' => 2.40,
                'estado' => true,
            ],
            [
                'nombre' => 'Croissant',
                'descripcion' => 'Croissant de mantequilla',
                'stock_actual' => 9,
                'stock_minimo' => 3,
                'precio_compra' => 2.80,
                'precio_venta' => 4.30,
                'estado' => true,
            ],
        ];

        foreach ($productosBase as $index => $productoData) {
            $categoria = $categoriasCreadas[$index % count($categoriasCreadas)];
            Producto::firstOrCreate(
                ['nombre' => $productoData['nombre']],
                [
                    ...$productoData,
                    'id_categoria' => $categoria->id_categoria,
                    'codigo_barras' => 'DEM' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT),
                ]
            );
        }

        $this->command->info('Se crearon 16 productos demo con categorías reales.');
    }
}
