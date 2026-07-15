<?php

namespace App\Http\Controllers;

use App\Models\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoComprobanteController extends Controller
{
    public function index()
    {
        $tipos = TipoComprobante::all();
        return response()->json([
            'success' => true,
            'data' => $tipos
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $maxId = TipoComprobante::max('id_tipo_comprobante') ?? 0;
        $tipo = TipoComprobante::create([
            'id_tipo_comprobante' => $maxId + 1,
            'nombre' => $request->nombre,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de comprobante creado con éxito',
            'data' => $tipo
        ], 201);
    }

    public function show($id)
    {
        $tipo = TipoComprobante::find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'No encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tipo
        ]);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoComprobante::find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'No encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tipo->update(['nombre' => $request->nombre]);

        return response()->json([
            'success' => true,
            'message' => 'Actualizado con éxito',
            'data' => $tipo
        ]);
    }

    public function destroy($id)
    {
        $tipo = TipoComprobante::find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'No encontrado'
            ], 404);
        }

        if ($tipo->ventas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar porque tiene ventas asociadas'
            ], 400);
        }

        $tipo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Eliminado con éxito'
        ]);
    }
}
