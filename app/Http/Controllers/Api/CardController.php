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


class CardController extends Controller
{

    private string $url_api;
    private const localURL = 'http://localhost:8000';

    public function __construct()
    {
        $this->url_api = config("app.url");
    }

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
                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" 
                . "{$card->id_blog}/head";
                $fileName = "imagenPrincipal.webp";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(1900, 800);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());

                $basePath = '/storage/';
               
                $fullUrl = $this->url_api . $basePath . $relativePath . '/' . $fileName;
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


     public function deleteCarpetaImages(Card $card)
    {
        try {
            $relativePath = "images/templates/plantilla{$card->id_plantilla}/" 
            . "{$card->id_blog}";

            Storage::disk('public')->deleteDirectory($relativePath);

            return response()->json([
                "status" => 200,
                "message" => "Carpeta eliminada correctamente"
            ], 200);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }

    }

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

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" 
                // . Str::slug($blog_header->titulo) 
                . "{$card->id_blog}/body";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(600, 350);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());

               
                $basePath = '/storage/';
                
                $fullUrl = $this->url_api . $basePath . $relativePath . '/' . $fileName;
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

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/" 
                // . Str::slug($blog_header->titulo) 
                . "{$card->id_blog}/footer";
                $filePath = $relativePath . "/" . $fileName;

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $image = Image::read($file)->cover(250, 200);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string) $image->toWebp());
              
                $basePath = '/storage/';
               
                $fullUrl = $this->url_api . $basePath . $relativePath . '/' . $fileName;
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

            $this->deleteCarpetaImages($card);

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
