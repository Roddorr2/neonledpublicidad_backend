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
    public function GetAll(Request $request)
    {
        try {
            //VALIDANDO
            $validator = Validator::make($request->all(), [
                "id_cliente" => "required|string"
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    "status" => 422,
                    "message" => $validator->errors()
                ], 422);
            }

            //BUSCANDO PROPUESTAS
            $propuestas = Propuesta::where("id_cliente", $request->id_cliente)
                ->select("id", "titulo", "descripcion1", "created_at")
                ->get();
            if ($propuestas->isEmpty()) {
                return response()->json([
                    "status" => 404,
                    "message" => "No se encontraron propuestas",
                ], 404);
            }
            //BUSCANDO LA IMAGEN
           $propuestasConPortada = $propuestas->map(function ($propuesta) use ($request) {
            $folderPath = "cliente/{$request->id_cliente}/propuestas/{$propuesta->id}/";
            
            // Obtener todos los archivos dentro de esa carpeta
            $files = Storage::disk('public')->files($folderPath);

            // Convertir las rutas a URLs accesibles públicamente
            $urls = collect($files)->map(function ($filePath) {
                return Storage::url($filePath);
            })->toArray();

            // Guardamos todas las URLs en el atributo "images"
            $propuesta->images = $urls;

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
            ],500);
        }
    }

    public function Create(Request $request)
    {
        try {
            //VALIDANDO
            $validator = Validator::make($request->all(), [
                'id_cliente' => 'required|string', //Tipo de dato incorrecto?
                'titulo' => 'required|string',
                'descripcion1' => 'required|string',
                'descripcion2' => 'nullable|string',
                'descripcion3' => 'nullable|string',
                'descripcion4' => 'nullable|string',
                'descripcion5' => 'nullable|string',
                'descripcion6' => 'nullable|string',
                'descripcion7' => 'nullable|string',
                'descripcion8' => 'nullable|string',
                'descripcion9' => 'nullable|string',
                'descripcion10' => 'nullable|string',
                'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            //GUARDANDO LOS DATOS
            DB::beginTransaction();
            $propuesta = new Propuesta();
            $propuesta->fill($request->except(['file']));
            $propuesta->save();
            $file = $request->file('file');
            if ($file) {
                $image = Image::read($file)->cover(1900, 800);
                $relativePath = "cliente/{$propuesta->id_user}/propuestas/{$propuesta->id}";
                Storage::disk('public')->put("{$relativePath}/portada.webp", (string) $image->toWebp());
            }
            DB::commit();
            return response()->json([
                "status" => 200,
                "message" => "Propuesta registrada y imagen subida correctamente",
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
    
    public function Load(Request $request, int $id)
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
                if (Str::is("cliente/*/propuestas/{$request->id}", $dir)) {
                    $files = Storage::disk('public')->files($dir);
                    $imagenes = collect($files)->filter(function ($file) {
                        return preg_match('/\.(webp)$/i', $file);
                    })->map(function ($file) {
                        return Storage::url($file);
                    })->values();

                    break;
                }
            }
            
            return response()->json([
                'status' => 200,
                'message' => $propuesta,
                'images' => $imagenes
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Error al cargar imágenes: ' . $ex->getMessage());
            return response()->json([
                'status' => 500,
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function Update(Request $request, int $id)//busca por id de propuesta
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'nullable|string',
                'descripcion1' => 'nullable|string',
                'descripcion2' => 'nullable|string',
                'descripcion3' => 'nullable|string',
                'descripcion4' => 'nullable|string',
                'descripcion5' => 'nullable|string',
                'descripcion6' => 'nullable|string',
                'descripcion7' => 'nullable|string',
                'descripcion8' => 'nullable|string',
                'descripcion9' => 'nullable|string',
                'descripcion10' => 'nullable|string',
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
                'titulo',
                'descripcion1',
                'descripcion2',
                'descripcion3',
                'descripcion4',
                'descripcion5',
                'descripcion6',
                'descripcion7',
                'descripcion8',
                'descripcion9',
                'descripcion10'
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


    public function UploadImage(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_user' => 'required|string',
                'filename' => 'required|string',
                'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'status' => 422,
                    "message" => $validator->errors()
                ],422);
            };

            // $image = Image::read($request->file)->cover(1900, 800);
            $image = Image::read($request->file('file'))->cover(1900, 800);
            $relativePath = "cliente/{$request->id_user}/propuestas/{$id}";
            Storage::disk('public')->put("{$relativePath}/{$request->filename}.webp", (string) $image->toWebp());

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
                'id_user' => 'required|string',
                'filename' => 'required|string',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'status' => 422,
                    "message" => $validator->errors()
                ],422);
            }
            $path = "cliente/{$request->id_user}/propuestas/{$id}/{$request->filename}.webp";

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'status' => 200,
                    'message' => 'Imagen eliminada correctamente',
                    'path' => Storage::url($path),
                ]);
            }else{
                return response()->json([
                    'status'=>422,
                    'message'=>"No se encontró la imagen"
                ],422);
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
                ],404);
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
}
