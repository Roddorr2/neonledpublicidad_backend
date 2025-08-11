<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Propuesta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Laravel\Facades\Image;


class PropuestaController extends Controller
{
    public function GetAll_Cliente($id_cliente) //retorna todas las propuestas de un cliente ,necesita de id_cliente
    {
        try {
            //VALIDANDO
            $validator = Validator::make(["id_cliente" => $id_cliente], [
                "id_cliente" => "required|numeric|exists:clientes,id" //verifica que el id_cliente sea un numero y exista en la tabla clientes
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    "status" => 422,
                    "message" => $validator->errors()
                ], 422);
            }

            //BUSCANDO PROPUESTAS
            $propuestas = Propuesta::where("id_cliente", $id_cliente)
                ->select("id", "nombre", "descripcion", "created_at", "id_cliente")
                ->get();
            if ($propuestas->isEmpty()) {
                return response()->json([
                    "status" => 404,
                    "message" => "No se encontraron propuestas",
                ], 404);
            }
            //BUSCANDO LA IMAGEN
            $propuestasConPortada = $propuestas->map(function ($propuesta) use ($id_cliente) {
                $folderPath = "cliente/{$id_cliente}/propuestas/{$propuesta->id}/imagenes/";

                // Obtener todos los archivos dentro de esa carpeta
                $files = Storage::disk('public')->files($folderPath);

                // Convertir las rutas a URLs accesibles públicamente
                $urls = collect($files)->map(function ($filePath) {
                    return Storage::url($filePath);
                })->toArray();

                // Guardamos todas las URLs en el atributo "images"
                $propuesta->images = $urls;

                $folderVideos = "cliente/{$id_cliente}/propuestas/{$propuesta->id}/videos/";
                $videoFiles = Storage::disk('public')->files($folderVideos);
                $propuesta->videos = collect($videoFiles)->map(function ($filePath) {
                    return Storage::url($filePath);
                })->toArray();

                return $propuesta;
            });

            return response()->json([
                "status" => 200,
                "message" => $propuestasConPortada
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => $ex->getMessage()
            ], 500);
        }
    }
    public function GetAll()
    {
        try {
            //obtiene todas las propuestas
            $propuestas = Propuesta::select("id", "id_cliente", "nombre", "descripcion", "created_at")->get();
            if ($propuestas->isEmpty()) {
                return response()->json([
                    "status" => 404,
                    "message" => "No se encontraron propuestas",
                ], 404);
            }
            //BUSCANDO LA IMAGEN PARA CADA PROPUESTA
            $propuestasConPortada = $propuestas->map(function ($propuesta) {
                $folderPath = "cliente/{$propuesta->id_cliente}/propuestas/{$propuesta->id}/imagenes/";
                $files = Storage::disk('public')->files($folderPath);

                $urls = collect($files)->map(function ($filePath) {
                    return Storage::url($filePath);
                })->toArray();

                $propuesta->images = $urls;

                $folderVideos = "cliente/{$propuesta->id_cliente}/propuestas/{$propuesta->id}/videos/";
                $videoFiles = Storage::disk('public')->files($folderVideos);
                $propuesta->videos = collect($videoFiles)->map(function ($filePath) {
                    return Storage::url($filePath);
                })->toArray();
                return $propuesta;
            });

            return response()->json([
                "status" => 200,
                "message" => $propuestasConPortada
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => $ex->getMessage()
            ], 500);
        }
    }
    public function Create(Request $request)
    {
        try {
            // VALIDACIÓN
            $validator = Validator::make($request->all(), [
                'id_cliente' => 'required|string',
                'nombre' => 'required|string',
                'descripcion' => 'required|string',
                'files' => 'nullable|array',
                'files.*' => 'image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
                'videos' => 'nullable|array',

                'videos.*' => 'file|max:51200|mimetypes:video/mp4,video/webm,video/ogg,application/octet-stream,video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi,video/qt'

            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // GUARDAR DATOS PRINCIPALES
            DB::beginTransaction();
            $propuesta = new Propuesta();
            // $propuesta->fill($request->except(['files']));
            $propuesta->fill($request->except(['files', 'videos']));
            $propuesta->save();

            // GUARDAR IMÁGENES
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $image = Image::read($file)->cover(1900, 800);

                    // Nombre original sin extensión
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanName = Str::slug($originalName);
                    $relativePath = "cliente/{$propuesta->id_cliente}/propuestas/{$propuesta->id}/imagenes";
                    $baseFilename = "{$relativePath}/{$cleanName}.webp";

                    // Verificar si ya existe y agregar sufijo incremental
                    $finalFilename = $baseFilename;
                    $counter = 2;
                    while (Storage::disk('public')->exists($finalFilename)) {
                        $finalFilename = "{$relativePath}/{$cleanName}-{$counter}.webp";
                        $counter++;
                    }

                    Storage::disk('public')->put($finalFilename, (string) $image->toWebp());
                }
            }
            // 🎥 GUARDAR VIDEOS
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $originalName = pathinfo($video->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanName = Str::slug($originalName);
                    $extension = $video->getClientOriginalExtension();

                    $relativePath = "cliente/{$propuesta->id_cliente}/propuestas/{$propuesta->id}/videos";
                    $baseFilename = "{$relativePath}/{$cleanName}.{$extension}";

                    $finalFilename = $baseFilename;
                    $counter = 2;
                    while (Storage::disk('public')->exists($finalFilename)) {
                        $finalFilename = "{$relativePath}/{$cleanName}-{$counter}.{$extension}";
                        $counter++;
                    }

                    Storage::disk('public')->putFileAs($relativePath, $video, basename($finalFilename));
                }
            }

            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "Propuesta registrada y todas las imágenes guardadas correctamente",
                "id" => $propuesta->id,
            ], 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::info($ex->getMessage());
            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }
    }


    public function Load(int $id)
    {
        try {
            $propuesta = Propuesta::find($id);
            if (!$propuesta) {
                return response()->json([
                    "status" => 422,
                    "message" => "Propuesta no encontrada"
                ], 422);
            }

            $imagenes = collect();
            $allDirs = Storage::disk('public')->allDirectories('cliente');
            foreach ($allDirs as $dir) {
                if (Str::is("cliente/*/propuestas/{$id}/imagenes", $dir)) {
                    $files = Storage::disk('public')->files($dir);
                    $imagenes = collect($files)->filter(function ($file) {
                        return preg_match('/\.(webp)$/i', $file);
                    })->map(function ($file) {
                        return Storage::url($file);
                    })->values();

                    break;
                }
            }
            $videos = collect();
            foreach ($allDirs as $dir) {
                if (Str::is("cliente/*/propuestas/{$id}/videos", $dir)) {
                    $files = Storage::disk('public')->files($dir);
                    $videos = collect($files)->filter(function ($file) {
                        return preg_match('/\.(mp4|webm|ogg|avi|mov)$/i', $file);
                    })->map(function ($file) {
                        return Storage::url($file);
                    })->values();
                    break;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => $propuesta,
                'images' => $imagenes,
                'videos' => $videos
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Error al cargar imágenes: ' . $ex->getMessage());
            return response()->json([
                'status' => 500,
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    public function load_cliente(int $id_cliente, int $id_propuesta)
    {
        try {
            $propuesta = Propuesta::find($id_propuesta);

            if (!$propuesta) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Propuesta no encontrada'
                ], 422);
            }

            if ($propuesta->id_cliente !== $id_cliente) {
                return response()->json([
                    'status' => 403,
                    'message' => 'No tienes permiso para acceder a esta propuesta'
                ], 403);
            }

            $imagenes = collect();
            $allDirs = Storage::disk('public')->allDirectories('cliente');

            foreach ($allDirs as $dir) {
                if (Str::is("cliente/*/propuestas/{$id_propuesta}/imagenes", $dir)) {
                    $files = Storage::disk('public')->files($dir);
                    $imagenes = collect($files)->filter(function ($file) {
                        return preg_match('/\.(webp)$/i', $file);
                    })->map(function ($file) {
                        return Storage::url($file);
                    })->values();

                    break;
                }
            }

            $videos = collect();
            foreach ($allDirs as $dir) {
                if (Str::is("cliente/{$id_cliente}/propuestas/{$id_propuesta}/videos", $dir)) {
                    $files = Storage::disk('public')->files($dir);
                    $videos = collect($files)->filter(function ($file) {
                        return preg_match('/\.(mp4|webm|ogg|avi|mov)$/i', $file);
                    })->map(function ($file) {
                        return Storage::url($file);
                    })->values();
                    break;
                }
            }

            return response()->json([
                'status' => 200,
                'message' => $propuesta,
                'images' => $imagenes,
                'videos' => $videos
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Error al cargar propuesta: ' . $ex->getMessage());

            return response()->json([
                'status' => 500,
                'error' => $ex->getMessage()
            ], 500);
        }
    }


    public function Update(Request $request, int $id) //busca por id de propuesta
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'nullable|string',
                'descipcion' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    "status" => 422,
                    "message" => $validator->errors()
                ], 422);
            }
            $propuesta = Propuesta::find($id);

            if (!$propuesta) {
                return response()->json([
                    "status" => 404,
                    "message" => "Propuesta no encontrada"
                ], 404);
            }
            DB::beginTransaction();
            $campos = [
                'nombre',
                'descripcion'
            ];
            foreach ($campos as $campo) {
                if ($request->filled($campo)) { // verifica que existe y no es null
                    $propuesta->$campo = $request->$campo;
                }
            }
            $propuesta->save();
            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "Propuesta actualizada correctamente",
                "data" => $propuesta
            ], 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "error" => $ex->getMessage()
            ], 500);
        }
    }


    public function UploadImage(Request $request, int $id) //envio de datos de imagen y id de propuesta
    {
        try {
            $validator = Validator::make($request->all(), [ //se valida que esten los datos de la imagen
                'filename' => 'required|string',
                'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);
            if ($validator->fails()) { //retorna error si no estan los datos
                Log::info($validator->errors());
                return response()->json([
                    'status' => 422,
                    "message" => $validator->errors()
                ], 422);
            };
            $propuesta = Propuesta::find($id); //busca la propuesta por id de la propuesta
            if (!$propuesta) { //retorna error si no se encuentra la propuesta
                return response()->json([
                    "status" => 404,
                    "message" => "Propuesta no encontrada"
                ], 404);
            }
            // $image = Image::read($request->file)->cover(1900, 800);
            $image = Image::read($request->file('file'))->cover(1900, 800);
            $filename = Str::slug($request->filename); // elimina espacios y caracteres raros
            $relativePath = "cliente/{$propuesta->id_cliente}/propuestas/{$id}/imagenes";
            Storage::disk('public')->put("{$relativePath}/{$filename}.webp", (string) $image->toWebp());

            return response()->json([
                'status' => 200,
                'message' => 'Imagen guardada correctamente',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => $ex->getMessage()
            ], 500);
        }
    }
    public function EraseImage(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_cliente' => 'required|string',
                'filename' => 'required|string',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'status' => 422,
                    "message" => $validator->errors()
                ], 422);
            }
            $path = "cliente/{$request->id_cliente}/propuestas/{$id}/imagenes/{$request->filename}.webp";

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'status' => 200,
                    'message' => 'Imagen eliminada correctamente',
                    'path' => Storage::url($path),
                ]);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => "No se encontró la imagen"
                ], 422);
            }
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => $ex->getMessage()
            ], 500);
        }
    }

    public function UploadVideo(Request $request, int $id) // id = id de la propuesta
{
    try {
        // Validación
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'file' => 'required|file|max:51200|mimetypes:
                video/mp4,
                video/webm,
                video/ogg,
                video/x-msvideo,
                video/x-flv,
                video/quicktime,
                video/x-ms-wmv,
                video/avi,
                application/octet-stream
            ',
        ]);

        if ($validator->fails()) {
            Log::info($validator->errors());
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()
            ], 422);
        }

        // Buscar propuesta
        $propuesta = Propuesta::find($id);
        if (!$propuesta) {
            return response()->json([
                "status" => 404,
                "message" => "Propuesta no encontrada"
            ], 404);
        }

        // Preparar nombre y ruta
        $filename = Str::slug($request->filename);
        $extension = $request->file('file')->getClientOriginalExtension();
        $relativePath = "cliente/{$propuesta->id_cliente}/propuestas/{$id}/videos";

        // Evitar sobrescribir: si existe, agregar sufijo incremental
        $finalFilename = "{$filename}.{$extension}";
        $counter = 2;
        while (Storage::disk('public')->exists("{$relativePath}/{$finalFilename}")) {
            $finalFilename = "{$filename}-{$counter}.{$extension}";
            $counter++;
        }

        // Guardar archivo
        Storage::disk('public')->putFileAs($relativePath, $request->file('file'), $finalFilename);

        return response()->json([
            'status' => 200,
            'message' => 'Video guardado correctamente',
            'filename' => $finalFilename
        ], 200);

    } catch (\Exception $ex) {
        return response()->json([
            "status" => 500,
            "message" => $ex->getMessage()
        ], 500);
    }
}
    public function EraseVideo(Request $request, int $id)
{
    try {
        // Validar datos
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|string',
            'filename' => 'required|string', // nombre con extensión incluida
        ]);

        if ($validator->fails()) {
            Log::info($validator->errors());
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()
            ], 422);
        }

        // Ruta del video
        $path = "cliente/{$request->id_cliente}/propuestas/{$id}/videos/{$request->filename}";

        // Eliminar si existe
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            return response()->json([
                'status' => 200,
                'message' => 'Video eliminado correctamente',
                'path' => Storage::url($path),
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => "No se encontró el video"
            ], 422);
        }

    } catch (\Exception $ex) {
        return response()->json([
            "status" => 500,
            "message" => $ex->getMessage()
        ], 500);
    }
}

    public function Delete(int $id)
    {
        try {

            DB::beginTransaction();
            $propuesta = Propuesta::find($id);
            if (!$propuesta) {
                return response()->json([
                    "status" => 404,
                    "message" => "Propuesta no encontrada"
                ], 404);
            }
            $allDirs = Storage::disk('public')->allDirectories('cliente');
            foreach ($allDirs as $dir) {
                if (Str::is("cliente/*/propuestas/$id", $dir)) {
                    Storage::disk('public')->deleteDirectory($dir);
                    break;
                }
            }

            $propuesta->delete();
            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Propuesta eliminada"
            ], 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    public function descargarImagenes($id_cliente, $id_propuesta)
    {
        try {
            $folderPath = "cliente/{$id_cliente}/propuestas/{$id_propuesta}/imagenes/";

            $files = Storage::disk('public')->files($folderPath);

            if (empty($files)) {
                return response()->json([
                    "status" => 404,
                    "message" => "No se encontraron imágenes"
                ], 404);
            }

            $zipFileName = "imagenes_propuesta_{$id_propuesta}.zip";
            $zipFilePath = storage_path("app/public/{$zipFileName}");

            $zip = new \ZipArchive;
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($files as $filePath) {
                    $absolutePath = Storage::disk('public')->path($filePath);
                    $zip->addFile($absolutePath, basename($absolutePath));
                }
                $zip->close();
            } else {
                return response()->json([
                    "status" => 500,
                    "message" => "No se pudo crear el archivo ZIP"
                ], 500);
            }

            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => 500,
                "message" => $ex->getMessage()
            ], 500);
        }
    }
}
