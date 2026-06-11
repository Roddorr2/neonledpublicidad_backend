<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Blog;
use App\Models\BlogBody;
use App\Models\BlogFooter;
use App\Models\BlogHead;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class CardController extends Controller
{
    private string $url_api;

    private const localURL = 'http://localhost:8000';

    public function __construct()
    {
        $this->url_api = config('app.url');
    }

    public function index_public()
    {
        try {
            $cards = Card::with(['blog.head'])
                ->where('estado_publicacion', true)
                ->orderBy('id_card', 'asc')
                ->get();

            return response()->json($cards, 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            // $cards = Card::with('blog')->orderBy('id_card', 'asc')->get();
            $cards = Card::with(['blog.head'])->orderBy('id_card', 'asc')->get();

            return response()->json($cards, 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function get($id = null)
    {
        try {
            if (! $id) {
                $cards = Card::with('empleado', 'blog')->get();
            } else {
                $cards = Card::with('empleado', 'blog')->where('id_empleado', $id)->get();
            }

            return response()->json($cards, 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error interno del servidor',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Búsqueda optimizada de cards con cache y límite de resultados
     * Utiliza índices en la base de datos para búsquedas rápidas
     *
     * @param  Request                       $request - Parámetros: q (query), type (public|dashboard)
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $type  = $request->input('type', 'public');

            // Validar mínimo 3 caracteres
            if (strlen(trim($query)) < 3) {
                return response()->json([], 200);
            }

            // Crear clave de cache única basada en la búsqueda normalizada
            $cacheKey = "card_search_{$type}_" . md5(strtolower(trim($query)));

            // Cache de 60 segundos para evitar consultas repetidas
            $results = Cache::remember($cacheKey, 60, function () use ($query, $type) {
                $baseQuery = Card::with(['blog.head', 'empleado'])
                    ->select('id_card', 'titulo', 'descripcion', 'public_image',
                        'url_image', 'id_plantilla', 'id_blog', 'id_empleado',
                        'estado_publicacion');

                // Si es búsqueda pública, solo retornar cards publicados
                if ($type === 'public') {
                    $baseQuery->where('estado_publicacion', true);
                }

                // Búsqueda por título O descripción (ambas con índices)
                // LIKE con % al inicio es lento, pero con índices es aceptable
                $searchTerm = "%{$query}%";
                $baseQuery->where(function ($q) use ($searchTerm) {
                    $q->where('titulo', 'like', $searchTerm)
                        ->orWhere('descripcion', 'like', $searchTerm);
                });

                return $baseQuery->orderBy('id_card', 'asc')
                    ->limit(10)  // Máximo 10 resultados
                    ->get();
            });

            return response()->json($results, 200);
        } catch (\Exception $ex) {
            Log::error('Error en búsqueda de cards: ' . $ex->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => 'Error en la búsqueda',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function create(StoreCardRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($data['id_blog'] == 1) {
                $data['estado_publicacion'] = true;
            }

            $card = Card::create($data);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Card creada correctamente',
                'id'      => $card->id_card,
            ], 200);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollback();

            return response()->json([
                'status'  => 500,
                'message' => 'Error al crear la card',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCardRequest $request, $id)
    {
        try {

            DB::beginTransaction();

            $card = Card::findOrFail($id);

            if (! $card) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Card no encontrada',
                ], 404);
            }

            $card->update($request->validated());

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Card actualizado',
                'id'      => $card->id_card,
            ], 200);

        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json([
                'status' => 500,
                'error'  => $ex->getMessage(),
            ], 500);
        }
    }

    public function imageHeader(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);

            if (! $request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());

                return response()->json([
                    'status'  => 201,
                    'data'    => 'Guardar ruta imagen local',
                    'message' => 'No se ha enviado la imagen',
                ], 201);
            } else {
                $card = Card::find($id);

                if (! $card) {
                    return response()->json([
                        'status'  => 404,
                        'message' => 'Blog no encontrado',
                    ], 404);
                }

                $blog = Blog::find($card->id_blog);

                $blog_header = BlogHead::find($blog->id_blog_head);

                $oldRelativeUrl = $card->url_image;

                if ($oldRelativeUrl) {
                    $oldFilePath = str_replace('/storage/', '', $oldRelativeUrl);

                    if (Storage::disk('public')->exists($oldFilePath)) {
                        Storage::disk('public')->delete($oldFilePath);
                        Log::info('Archivo antiguo eliminado: ' . $oldFilePath);
                    }
                }

                $file         = $request->file('file');
                $relativePath = "images/templates/plantilla{$card->id_plantilla}/"
                . "{$card->id_blog}/head";

                $baseName  = 'imagenPrincipal';
                $timestamp = Carbon::now()->format('Ymd_His');

                $fileName = "{$baseName}_{$timestamp}.webp";
                $filePath = $relativePath . '/' . $fileName;

                // if (Storage::disk('public')->exists($filePath)) {
                //     Storage::disk('public')->delete($filePath);
                // }

                $image = Image::read($file)->cover(1900, 800);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string)$image->toWebp());

                $basePath = '/storage/';

                $fullUrl     = $this->url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                $card->public_image = $fullUrl;
                $card->url_image    = $relativeUrl;
                $card->save();

                $blog_header->public_image = $fullUrl;
                $blog_header->url_image    = $relativeUrl;
                $blog_header->save();

                return response()->json([
                    'status'    => 200,
                    'message'   => 'Success, imagen subida correctamente',
                    'url_image' => $fullUrl,
                ], 200);
            }
        } catch (\Exception $ex) {

            Log::info($ex->getMessage());

            return response()->json([
                'status' => 500,
                'error'  => $ex->getMessage(),
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
                'status'  => 200,
                'message' => 'Carpeta eliminada correctamente',
            ], 200);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());

            return response()->json([
                'status' => 500,
                'error'  => $ex->getMessage(),
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

            if (! $request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());

                return response()->json([
                    'status'  => 201,
                    'data'    => 'Guardar ruta imagen local',
                    'message' => 'No se ha enviado la imagen',
                ], 201);
            } else {
                $card = Card::find($id);

                if (! $card) {
                    return response()->json([
                        'status'  => 404,
                        'message' => 'Blog no encontrado',
                    ], 404);
                }

                $blog        = Blog::find($card->id_blog);
                $blog_header = BlogHead::find($blog->id_blog_head);
                $blog_body   = BlogBody::find($blog->id_blog_body);

                $oldRelativeUrl = $blog_body->{'url_' . $request->name} ?? null;

                if ($oldRelativeUrl) {
                    $oldFilePath = str_replace('/storage/', '', $oldRelativeUrl);

                    if (Storage::disk('public')->exists($oldFilePath)) {
                        Storage::disk('public')->delete($oldFilePath);
                        Log::info('Archivo antiguo del Body eliminado: ' . $oldFilePath);
                    }
                }

                $file = $request->file('file');

                $baseName  = $request->name;
                $timestamp = Carbon::now()->format('Ymd_His');

                $fileName = "{$baseName}_{$timestamp}.webp";

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/"
                // . Str::slug($blog_header->titulo)
                . "{$card->id_blog}/body";
                $filePath = $relativePath . '/' . $fileName;

                // if (Storage::disk('public')->exists($filePath)) {
                //     Storage::disk('public')->delete($filePath);
                // }

                $image = Image::read($file)->cover(600, 350);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string)$image->toWebp());

                $basePath = '/storage/';

                $fullUrl     = $this->url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                switch ($request->name) {
                    case 'image1':
                        $blog_body->public_image1 = $fullUrl;
                        $blog_body->url_image1    = $relativeUrl;
                        break;
                    case 'image2':
                        $blog_body->public_image2 = $fullUrl;
                        $blog_body->url_image2    = $relativeUrl;
                        break;
                    default:
                        $blog_body->public_image3 = $fullUrl;
                        $blog_body->url_image3    = $relativeUrl;
                        break;
                }

                $blog_body->save();

                return response()->json([
                    'status'  => 200,
                    'message' => 'Success, imagen subida correctamente',
                    'url'     => $fullUrl,
                ], 200);
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());

            return response()->json([
                'status' => 500,
                'error'  => $ex->getMessage(),
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

            if (! $request->hasFile('file') || $validator->fails()) {
                Log::info($validator->errors());

                return response()->json([
                    'status'  => 201,
                    'data'    => 'Guardar ruta imagen local',
                    'message' => 'No se ha enviado la imagen',
                ], 201);
            } else {
                $card = Card::find($id);

                if (! $card) {
                    return response()->json([
                        'status'  => 404,
                        'message' => 'Blog no encontrado',
                    ], 404);
                }

                $blog        = Blog::find($card->id_blog);
                $blog_header = BlogHead::find($blog->id_blog_head);
                $blog_footer = BlogFooter::find($blog->id_blog_footer);

                $oldRelativeUrl = $blog_footer->{'url_' . $request->name} ?? null;

                if ($oldRelativeUrl) {
                    $oldFilePath = str_replace('/storage/', '', $oldRelativeUrl);

                    if (Storage::disk('public')->exists($oldFilePath)) {
                        Storage::disk('public')->delete($oldFilePath);
                        Log::info('Archivo antiguo del Footer eliminado: ' . $oldFilePath);
                    }
                }

                $file = $request->file('file');

                $baseName  = $request->name;
                $timestamp = Carbon::now()->format('Ymd_His');

                $fileName = "{$baseName}_{$timestamp}.webp";

                $relativePath = "images/templates/plantilla{$card->id_plantilla}/"
                // . Str::slug($blog_header->titulo)
                . "{$card->id_blog}/footer";
                $filePath = $relativePath . '/' . $fileName;

                // if (Storage::disk('public')->exists($filePath)) {
                //     Storage::disk('public')->delete($filePath);
                // }

                $image = Image::read($file)->cover(250, 200);
                Storage::disk('public')->put("{$relativePath}/{$fileName}", (string)$image->toWebp());

                $basePath = '/storage/';

                $fullUrl     = $this->url_api . $basePath . $relativePath . '/' . $fileName;
                $relativeUrl = $basePath . $relativePath . '/' . $fileName;

                switch ($request->name) {
                    case 'image1':
                        $blog_footer->public_image1 = $fullUrl;
                        $blog_footer->url_image1    = $relativeUrl;
                        break;
                    case 'image2':
                        $blog_footer->public_image2 = $fullUrl;
                        $blog_footer->url_image2    = $relativeUrl;
                        break;
                    default:
                        $blog_footer->public_image3 = $fullUrl;
                        $blog_footer->url_image3    = $relativeUrl;
                        break;
                }

                $blog_footer->save();

                return response()->json([
                    'status'  => 200,
                    'message' => 'Success, imagen subida correctamente',
                    'url'     => $fullUrl, // Agregado URL en la respuesta
                ], 200);
            }
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());

            return response()->json([
                'status' => 500,
                'error'  => $ex->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $card = Card::find($id);

            if (! $card) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Card no encontrada',
                ]);
            }

            $this->deleteCarpetaImages($card);

            $card->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Card eliminada correctamente',
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status'  => 500,
                'message' => 'Error al eliminar la card',
                'error'   => $ex->getMessage(),
            ], 500);
        }
    }
}
