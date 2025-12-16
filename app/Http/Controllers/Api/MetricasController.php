<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Card;
use App\Models\BlogBody;
use App\Models\BlogHead;
use App\Models\Empleado;
use App\Models\BlogAuditoria;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\BlogFooter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Carbon;

class MetricasController extends Controller
{
    // METRICAS DE BLOGS
    //1.1 Cantidad de blogs creados en un mes específico
    //Cuenta el número de blogs creados en un mes y año específicos.
    public function countBlogsByMonth(Request $request) {
    try {
        $month = $request->input('month');//1-12
        $year = $request->input('year');//2025
        $estado = $request->input('estado'); //CREAR, ACTUALIZAR, ELIMINAR
        if (!$month || !$year || $month < 1 || $month > 12) {
            return response()->json([
                "message" => "Mes o año inválido"
            ], 400);
        }
        $blogs = BlogAuditoria::where('accion', 'CREAR')
                     ->whereYear('fecha_hora', $year)
                     ->whereMonth('fecha_hora', $month)
                     ->get();
        $countBlogs = $blogs->count();
        return response()->json([
            "status" => 200,
            'data' => [
                'month' => $month,
                'year' => $year,
                'total_blogs' => $countBlogs
            ]
        ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //1.2 Listar blogs creados alrededor de la fecha actual
    //Lista la cantidad de blogs creados en los últimos 12 meses desde la fecha actual.
    public function listBlogsByMonths12(Request $request) {
    try {
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subMonths(11)->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();
        $blogs = BlogAuditoria::where('accion', 'CREAR')
                     ->whereBetween('fecha_hora', [$startDate, $endDate])
                     ->get();
        $blogsByMonth = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');
            $blogsByMonth[$monthKey] = [
                'month' => $month->format('F Y'),
                'total_blogs' => 0
            ];
        }
        foreach ($blogs as $blog) {
            $monthKey = Carbon::parse($blog->fecha_hora)->format('Y-m');
            if (isset($blogsByMonth[$monthKey])) {
                $blogsByMonth[$monthKey]['total_blogs']++;
            }
        }
        return response()->json([
            "status" => 200,
            'data' => array_values($blogsByMonth)
        ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //1.3 Listar op 5 meses con más blogs creados
    //Lista los 5 meses con la mayor cantidad de blogs creados.
    public function top5MothsWithMoreBlogs(Request $request) {
        try {
            $blogs = BlogAuditoria::where('accion', 'CREAR')
                        ->get();
            $blogsByMonth = [];
            foreach ($blogs as $blog) {
                $monthKey = Carbon::parse($blog->fecha_hora)->format('Y-m');
                if (!isset($blogsByMonth[$monthKey])) {
                    $blogsByMonth[$monthKey] = [
                        'month' => Carbon::parse($blog->fecha_hora)->format('F Y'),
                        'total_blogs' => 0
                    ];
                }
                $blogsByMonth[$monthKey]['total_blogs']++;
            }
            usort($blogsByMonth, function($a, $b) {
                return $b['total_blogs'] <=> $a['total_blogs'];
            });
            $top5Months = array_slice($blogsByMonth, 0, 5);
            return response()->json([
                "status" => 200,
                'data' => $top5Months
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //2.1 Listar cards/blogs por tipo_plantilla
    //Lista las cards/blogs filtrados por el tipo de plantilla (id_plantilla).
    public function listOfCardsByPlantilla(Request $request) {
    try {
        $plantillaFilter = $request->input('id_plantilla');
        $cards = Card::all();
        if ($plantillaFilter > 3 || $plantillaFilter < 1) {
            return response()->json([
                "message" => "Sin Blogs de esta plantilla"
            ], 400);
        }
        $filteredCards = $cards->filter(function($card) use ($plantillaFilter) {
            return $card->id_plantilla == $plantillaFilter;
        });
        if ($filteredCards->isEmpty()) {
            return response()->json([
                "message" => "No se encontraron blogs para esta plantilla"
            ], 404);
        }
        return response()->json([
            "status" => 200,
            'data' => $filteredCards->values()
        ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //2.2 Cantidad de blog por tipo_plantilla
    //Devuelve la cantidad de blogs creados para un tipo de plantilla específico.
    public function countListOfCardsByPlantilla(Request $request) {
    try {
        $response = $this->listOfCardsByPlantilla($request);
        if ($response->status() === 200) {
            $countCards = count($response->original['data']);
            return response()->json([
                "status" => 200,
                'count' => $countCards
            ], 200);
        } else {
            return $response;
        }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //2.3 Total de card/blog por cada tipo_plantilla
    //Devuelve una tabla con el total de cards/blogs para cada tipo de plantilla.
    public function tableCardsByIdPlantilla() {
        try {
            $tableCards = [];
            for ($i = 1; $i <= 3; $i++) {
                $request = new Request(['id_plantilla' => $i]);
                $cardsByPlantilla = $this->listOfCardsByPlantilla($request);
                if ($cardsByPlantilla->status() === 200) {
                    $countCards = count($cardsByPlantilla->original['data']);
                } else {
                    $countCards = 0;
                }
                $tableCards[] = [
                    'id_plantilla' => $i,
                    'count_cards' => $countCards
                ];
            }
            return response()->json([
                "status" => 200,
                'data' => $tableCards
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //3.1 Listar cards/blogs por empleado
    //Lista las cards/blogs creados por un empleado específico.
    public function listEmpleadoWithCards(Request $request) {
        try {
            $id = $request->input('id_empleado');
            $empleado = Empleado::find($id);
            if ($empleado === null || ($empleado->id_rol != 1))
            {
                return response()->json([
                    "status" => 404,
                    "message" => "Empleado no encontrado o no tiene el rol adecuado"
                ], 404);
            }
            $cards = Card::where('id_empleado', $id)->get();
            if ($cards->isEmpty()) {
                return response()->json([
                    "status" => 404,
                    "message" => "No se encontraron blogs/cards para este empleado"
                ], 404);
            }
            return response()->json([
                "status" => 200,
                'data' => $cards
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //3.2 Cantidad de cards/blogs por empleado
    //Devuelve la cantidad de cards/blogs creados por un empleado específico.
    public function countListOfCardsByEmpleado(Request $request) {
        try {
            $response = $this->listEmpleadoWithCards($request);
            if ($response->status() === 200) {
                $countCards = count($response->original['data']);
                return response()->json([
                    "status" => 200,
                    'count' => $countCards
                ], 200);
            } else {
                return $response;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //3.3 Total de cards/blogs por empleado
    //Devuelve una tabla con el total de cards/blogs creados por cada empleado.
    public function tableCardsByEmpleado() {
        try {
            $empleados = Empleado::where('id_rol', 1)->get();
            $tableCards = [];
            foreach ($empleados as $empleado) {
                $request = new Request(['id_empleado' => $empleado->id_empleado
                ]);
                $cardsByEmpleado = $this->listEmpleadoWithCards($request);
                if ($cardsByEmpleado->status() === 200) {
                    $countCards = count($cardsByEmpleado->original['data']);
                } else {
                    $countCards = 0;
                }
                $tableCards[] = [
                    'id_empleado' => $empleado->id_empleado,
                    'nombre_empleado' => $empleado->nombre,
                    'count_cards' => $countCards
                ];
            }
            return response()->json([
                "status" => 200,
                'data' => $tableCards
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //3.4 Frecuencia de publicacion de cards todos los empleados
    //Devuelve la frecuencia de publicación de cards por empleado al mes.
    public function frecuenciaPublicacionCardsTodosEmpleados() {
        try {
            $empleados = Empleado::where('id_rol', 1)->get();
            $frecuenciaPublicacion = [];
            foreach ($empleados as $empleado) {
                $cardsCount = Card::where('id_empleado', $empleado->id_empleado)->count();
                $frecuenciaPublicacion[] = [
                    'id_empleado' => $empleado->id_empleado,
                    'nombre_empleado' => $empleado->nombre,
                    'promedio' => $cardsCount
                ];
            }
            return response()->json([
                "status" => 200,
                'fracuencia x mes' => 'Considerando desde el inicio de la creación de cards',
                'data' => $frecuenciaPublicacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //3.5 Tiempo promedio de creación, edición y publicación de una card
    //Devuelve toda la lista de cards con el tiempo en minutos entre creación, edición y publicación.
    public function tiempoCreacionEdicionPublicacionCard() {
        try {
            $cards = Blog::all();
            $tiemposCards = [];
            foreach ($cards as $card) {
                $createdAt = Carbon::parse($card->created_at);
                $updatedAt = Carbon::parse($card->updated_at);
                $tiempoCreacionEdicion = $createdAt->diffInMinutes($updatedAt);
                $tiemposCards[] = [
                    'id_card' => $card->id_blog,
                    'tiempo_creacion_edicion_minutos' => $tiempoCreacionEdicion
                ];      
            }
            return response()->json([
                "status" => 200,
                'data' => $tiemposCards
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //NUEVAS METRICAS 4.0
}
