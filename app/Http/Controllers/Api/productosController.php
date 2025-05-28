<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Productos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Tag(
 *     name="Productos",
 *     description="Operaciones para gestionar productos"
 * )
 */
class ProductosController extends Controller
{

     /**
     * @OA\Get(
     *     path="/api/productos",
     *     tags={"Productos"},
     *     summary="Listar productos paginados",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos paginada"
     *     )
     * )
     */
    public function get()
    {
        return Productos::orderBy('id_producto', 'desc')->paginate(20);
    }

     /**
     * @OA\Get(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Mostrar un producto por ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del producto"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $producto = Productos::findOrFail($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto);
    }


    /**
     * @OA\Post(
     *     path="/api/productos",
     *     tags={"Productos"},
     *     summary="Crear un nuevo producto",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion"},
     *             @OA\Property(property="nombre", type="string", example="Impresora 3D"),
     *             @OA\Property(property="descripcion", type="string", example="Máquina para impresiones tridimensionales")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function create(Request $request)
    {
        // Validación de datos
        $request->validate([
            'nombre' => 'required|string|max:250',
            'descripcion' => 'required|string|max:250'
        ]);

        // Crear nuevo producto
        $producto = new Productos();
        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->save();

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Actualizar un producto existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "descripcion"},
     *             @OA\Property(property="nombre", type="string", example="Escáner 3D"),
     *             @OA\Property(property="descripcion", type="string", example="Escáner para objetos tridimensionales")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Validación de datos
        $request->validate([
            'nombre' => 'required|string|max:250',
            'descripcion' => 'required|string'
        ]);

        $producto = Productos::findOrFail($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->save();

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Eliminar un producto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     )
     * )
     */
    public function delete($id)
    {
        $producto = Productos::find($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}
