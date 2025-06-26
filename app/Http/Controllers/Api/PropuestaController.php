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
    public function GetAll(Request $request){
     try {

        //VALIDANDO
        $validator = Validator::make($request->all(), [
            "id" => "required|string"
        ]);
        if ($validator->fails()) {
            Log::info($validator->errors());
            return response()->json([
                "status" => 422,
                "message" => "Usuario no encontrado"
            ], 422);
        }

//BUSCANDO PROPUESTAS
        $propuestas = Propuesta::where("id_user", $request->id)
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
            $relativePath = "cliente/{$request->id}/propuestas/{$propuesta->id}/portada.webp";
            if (Storage::disk('public')->exists($relativePath)) {
                $propuesta->image = Storage::url($relativePath);
            } else {
                $propuesta->image = []; 
            }
            return $propuesta;
        });

        return response()->json([
            "status" => 200,
            "message" => $propuestasConPortada
        ]);       
    }catch(\Exception $ex){
            return response()->json([
                "status"=>500,
                "message"=>$ex->getMessage()
            ]);
        }
    }

    public function Create(Request $request)
    {
        try {                 
            //VALIDANDO
            $validator = Validator::make($request->all(), [
                'id_user'=>'required|string',
                'titulo'=>'required|string',
                'descripcion1'=>'required|string',
                'descripcion2'=>'nullable|string',
                'descripcion3'=>'nullable|string',
                'descripcion4'=>'nullable|string',
                'descripcion5'=>'nullable|string',
                'descripcion6'=>'nullable|string',
                'descripcion7'=>'nullable|string',
                'descripcion8'=>'nullable|string',
                'descripcion9'=>'nullable|string',
                'descripcion10'=>'nullable|string',               
                'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif,jfif|max:20480',
            ]);
             if ($validator->fails()) {
                        Log::info($validator->errors());
                    return response()->json([
                        'errors' => $validator->errors()
                    ], 422);
                           
            } else {        
                //GUARDANDO LOS DATOS
            DB::beginTransaction();
            $propuesta = new Propuesta();
            $propuesta->fill($request->except(['file']));
            $propuesta->save();
            
            $file = $request->file('file');
            if($file){
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
            }
        } catch (\Exception $ex) {

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
            'imagenes' => $imagenes
        ], 200);

    } catch (\Exception $ex) {
        Log::error('Error al cargar imágenes: ' . $ex->getMessage());
        return response()->json([
            'status' => 500,
            'error' => $ex->getMessage()
        ], 500);
    }
}
 public function Update(Request $request,int $id)
    {try {
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
                "message" => "Debes ingresar un id de propuesta válido."
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
            'titulo', 'descripcion1', 'descripcion2', 'descripcion3',
            'descripcion4', 'descripcion5', 'descripcion6', 'descripcion7',
            'descripcion8', 'descripcion9', 'descripcion10'
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
        
        
        }catch(\Exception $ex){
                return response()->json([
                    "status"=>500,"error"=>$ex->getMessage()
                ],500);
            }
    }
  public function Delete(int $id)
    {
        try{
       
        DB::beginTransaction();
        $propuesta = Propuesta::find($id);
        if (!$propuesta) {
                return response()->json([
                    "status" => 404,
                    "message" => "Propuesta no encontrada"
                ]);
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
                "status"=>200,
                "message"=>"Propuesta eliminada"
            ],200);
       
}catch(\Exception $ex){
    return response()->json([
        'status'=>500,
        'error'=>$ex->getMessage()
    ],500);
}

    }

}
