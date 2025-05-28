<?php

namespace App\Http\Controllers\Api;

use App\Models\Blog;
use App\Models\Card;
use App\Models\BlogBody;
use App\Models\BlogHead;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BlogFooter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * @OA\Tag(
 *     name="Cards",
 *     description="Operaciones relacionadas con las tarjetas de presentación enlazadas a Blogs"
 * )
 */
class CardController extends Controller
{

    private const url_api = "http://localhost:8000";
    //private const url_api = "http://back.digimediamkt.com";


     /**
     * @OA\Get(
     *     path="/api/cards",
     *     tags={"Cards"},
     *     summary="Listar todas las cards",
     *     @OA\Response(
     *         response=200,
     *         description="Listado completo de cards"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function index()
    {
        try {
            $cards = Card::orderBy('id_card', 'asc')->get();
            return response()->json($cards, 200);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
     /**
     * @OA\Get(
     *     path="/api/cards/{id}",
     *     tags={"Cards"},
     *     summary="Obtener cards por ID de empleado",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del empleado",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Cards del empleado o todas"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function get($id = null)
    {
        try {
            if (!$id) {
                $cards = Card::with('empleado')->get();
            } else {
                $cards = Card::with('empleado')->where('id_empleado', $id)->get();
            }
            return response()->json($cards, 200);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => "Error interno del servidor",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/cards",
     *     tags={"Cards"},
     *     summary="Crear nueva card",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","descripcion","public_image","id_plantilla","id_blog","id_empleado"},
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="public_image", type="string"),
     *             @OA\Property(property="url_image", type="string"),
     *             @OA\Property(property="id_plantilla", type="integer"),
     *             @OA\Property(property="id_blog", type="integer"),
     *             @OA\Property(property="id_empleado", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Card creada correctamente"),
     *     @OA\Response(response=400, description="Errores de validación"),
     *     @OA\Response(response=500, description="Error interno al crear")
     * )
     */
    public function create(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'public_image' => 'required|string',
                'url_image' => 'nullable|string',
                'id_plantilla' => 'required|integer|min:1|max:3',
                'id_blog' => 'required|integer|exists:blogs,id_blog',
                'id_empleado' => 'required|integer|exists:empleados,id_empleado',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            DB::beginTransaction();

            $card = Card::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Card creada correctamente",
                "id" => $card->id_card
            ], 200);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollback();
            return response()->json([
                "status" => 500,
                "message" => "Error al crear la card",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
     /**
     * @OA\Put(
     *     path="/api/cards/{id}",
     *     tags={"Cards"},
     *     summary="Actualizar una card existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","descripcion","public_image","id_plantilla","id_blog","id_empleado"},
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="public_image", type="string"),
     *             @OA\Property(property="url_image", type="string"),
     *             @OA\Property(property="id_plantilla", type="integer"),
     *             @OA\Property(property="id_blog", type="integer"),
     *             @OA\Property(property="id_empleado", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Card actualizada correctamente"),
     *     @OA\Response(response=400, description="Errores de validación"),
     *     @OA\Response(response=404, description="Card no encontrada"),
     *     @OA\Response(response=500, description="Error interno al actualizar")
     * )
     */
    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'public_image' => 'required|string',
                'url_image' => 'nullable|string',
                'id_plantilla' => 'required|integer|min:1|max:3',
                'id_blog' => 'required|integer|exists:blogs,id_blog',
                'id_empleado' => 'required|integer|exists:empleados,id_empleado',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $card = Card::findOrFail($id);

            if (!$card){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'Card no encontrada'
                ], 404);
            }

            $card->update($request->all());

            DB::commit();

            return response()->json([
                'status'=> 200,
                'message'=> 'Card actualizado',
                'id'=> $card->id_card
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "status"=> 500,
                "error"=> $ex->getMessage()
            ],500);
        }
    }
      /**
     * @OA\Post(
     *     path="/api/cards/{id}/image-header",
     *     tags={"Cards"},
     *     summary="Subir imagen de encabezado",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="file", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Imagen subida correctamente"),
     *     @OA\Response(response=404, description="Card o Blog no encontrado"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function imageHeader(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);

            if (!$request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    "status" => 201,
                    'data' => "Guardar ruta imagen local",
                    "message" => "No se ha enviado la imagen"
                ], 201);
            } else {
                $card = Card::find($id);

                if (!$card) {
                    return response()->json([
                        "status" => 404,
                        "message" => "Blog no encontrado"
                    ], 404);
                }

                $blog = Blog::find($card->id_blog);

                $blog_header = BlogHead::find($blog->id_blog_head);

                $file = $request->file('file');
                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" . Str::slug($blog_header->titulo) . "{$card->id_blog}/head";
                $fileName = "imagenPrincipal.webp";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(1900, 800);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());

                $basePath = '/storage/';
                $fullUrl = self::url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                $card->public_image = $fullUrl;
                $card->url_image = $relativeUrl;
                $card->save();

                $blog_header->public_image = $fullUrl;
                $blog_header->url_image = $relativeUrl;
                $blog_header->save();

                return response()->json([
                    "status" => 200,
                    "message" => "Success, imagen subida correctamente",
                    "url_image" => $fullUrl
                ], 200);
            }
        } catch (\Exception $ex) {

            Log::info($ex->getMessage());

            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }
    }

     /**
     * @OA\Post(
     *     path="/api/cards/{id}/image-body",
     *     tags={"Cards"},
     *     summary="Subir imagen para el cuerpo del blog",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file", "name"},
     *                 @OA\Property(property="file", type="file", format="binary"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Imagen subida correctamente"),
     *     @OA\Response(response=404, description="Card o Blog no encontrado"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function imagesBody(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
                'name' => 'required|string|max:255',
            ]);

            if (!$request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());

                return response()->json([
                    "status" => 201,
                    'data' => "Guardar ruta imagen local",
                    "message" => "No se ha enviado la imagen"
                ], 201);
            } else {
                $card = Card::find($id);

                if (!$card) {
                    return response()->json([
                        "status" => 404,
                        "message" => "Blog no encontrado"
                    ], 404);
                }

                $blog = Blog::find($card->id_blog);
                $blog_header = BlogHead::find($blog->id_blog_head);
                $blog_body = BlogBody::find($blog->id_blog_body);

                $file = $request->file('file');
                $fileName = $request->name . ".webp";

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" . Str::slug($blog_header->titulo) . "{$card->id_blog}/body";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(600, 350);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());

                $basePath = '/storage/';
                $fullUrl = self::url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                switch ($request->name) {
                    case "image1":
                        $blog_body->public_image1 = $fullUrl;
                        $blog_body->url_image1 = $relativeUrl;
                        break;
                    case "image2":
                        $blog_body->public_image2 = $fullUrl;
                        $blog_body->url_image2 = $relativeUrl;
                        break;
                    default:
                        $blog_body->public_image3 = $fullUrl;
                        $blog_body->url_image3 = $relativeUrl;
                        break;
                }

                $blog_body->save();

                return response()->json([
                    "status" => 200,
                    "message" => "Success, imagen subida correctamente",
                    "url" => $fullUrl
                ], 200);
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/cards/{id}/image-footer",
     *     tags={"Cards"},
     *     summary="Subir imagen para el footer del blog",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file", "name"},
     *                 @OA\Property(property="file", type="file", format="binary"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Imagen subida correctamente"),
     *     @OA\Response(response=404, description="Card o Blog no encontrado"),
     *     @OA\Response(response=500, description="Error interno")
     * )
     */
    public function imagesFooter(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
                'name' => 'required|string|max:255',
            ]);

            if (!$request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());

                return response()->json([
                    "status" => 201,
                    'data' => "Guardar ruta imagen local",
                    "message" => "No se ha enviado la imagen"
                ], 201);
            } else {
                $card = Card::find($id);

                if (!$card) {
                    return response()->json([
                        "status" => 404,
                        "message" => "Blog no encontrado"
                    ], 404);
                }

                $blog = Blog::find($card->id_blog);
                $blog_header = BlogHead::find($blog->id_blog_head);
                $blog_footer = BlogFooter::find($blog->id_blog_footer);

                $file = $request->file('file');
                $fileName = $request->name . ".webp";

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" . Str::slug($blog_header->titulo) . "{$card->id_blog}/footer";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(250, 200);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());

                $basePath = '/storage/';
                $fullUrl = self::url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                switch ($request->name) {
                    case "image1":
                        $blog_footer->public_image1 = $fullUrl;
                        $blog_footer->url_image1 = $relativeUrl;
                        break;
                    case "image2":
                        $blog_footer->public_image2 = $fullUrl;
                        $blog_footer->url_image2 = $relativeUrl;
                        break;
                    default:
                        $blog_footer->public_image3 = $fullUrl;
                        $blog_footer->url_image3 = $relativeUrl;
                        break;
                }

                $blog_footer->save();

                return response()->json([
                    "status" => 200,
                    "message" => "Success, imagen subida correctamente",
                    "url" => $fullUrl // Agregado URL en la respuesta
                ], 200);
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/cards/{id}",
     *     tags={"Cards"},
     *     summary="Eliminar una card por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Card eliminada correctamente"),
     *     @OA\Response(response=404, description="Card no encontrada"),
     *     @OA\Response(response=500, description="Error interno al eliminar")
     * )
     */
    public function destroy(int $id)
    {
        try {
            $card = Card::find($id);

            if (!$card) {
                return response()->json([
                    "status" => 404,
                    "message" => "Card no encontrada"
                ]);
            }

            $card->delete();
            return response()->json([
                "status" => 200,
                "message" => "Card eliminada correctamente"
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => "Error al eliminar la card",
                "error" => $ex->getMessage()
            ], 500);
        }
    }
}
