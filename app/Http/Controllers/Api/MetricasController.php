<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Card;
use App\Models\Empleado;
use App\Models\BlogAuditoria;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MetricasController extends Controller
{
    /**
     * Helper para resolver mes y año
     */
    private function resolveMonthYear(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year  = $request->input('year', Carbon::now()->year);

        if ($month < 1 || $month > 12) {
            abort(400, 'Mes inválido');
        }

        return [$month, $year];
    }

    /* ============================================================
     * 1. METRICAS BLOGS
     * ============================================================
     */

    // 1.1 Cantidad de blogs creados por mes y año
    public function countBlogsByMonth(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);

        $count = BlogAuditoria::where('accion', 'CREAR')
            ->whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->count();

        return response()->json([
            "status" => 200,
            "data" => [
                "month" => $month,
                "year" => $year,
                "total_blogs" => $count
            ]
        ]);
    }

    // 1.2 Blogs creados últimos 12 meses
    public function listBlogsByMonths12()
    {
        $endDate   = Carbon::now()->endOfMonth();
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();

        $raw = BlogAuditoria::selectRaw(
                "YEAR(fecha_hora) as y, MONTH(fecha_hora) as m, COUNT(*) as total"
            )
            ->where('accion', 'CREAR')
            ->whereBetween('fecha_hora', [$startDate, $endDate])
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($i) => $i->y . '-' . str_pad($i->m, 2, '0', STR_PAD_LEFT));

        $data = [];
        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $key  = $date->format('Y-m');

            $data[] = [
                "month" => $date->format('F Y'),
                "total_blogs" => $raw[$key]->total ?? 0
            ];
        }

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }

    // 1.3 Top 5 meses con más blogs
    public function top5MothsWithMoreBlogs()
    {
        $data = BlogAuditoria::selectRaw(
                "YEAR(fecha_hora) as y, MONTH(fecha_hora) as m, COUNT(*) as total"
            )
            ->where('accion', 'CREAR')
            ->groupBy('y', 'm')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($i) => [
                "month" => Carbon::create($i->y, $i->m)->format('F Y'),
                "total_blogs" => $i->total
            ]);

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }

    /* ============================================================
     * 2. METRICAS POR PLANTILLA
     * ============================================================
     */

    // 2.1 Listar cards por plantilla
    public function listOfCardsByPlantilla(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);
        $plantilla = $request->input('id_plantilla');

        $cards = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_plantilla', $plantilla)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->whereMonth('ba.fecha_hora', $month)
            ->select('cards.*')
            ->get();

        return response()->json([
            "status" => 200,
            "data" => $cards
        ]);
    }

    // 2.2 Cantidad de cards por plantilla
    public function countListOfCardsByPlantilla(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);
        $plantilla = $request->input('id_plantilla');

        $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_plantilla', $plantilla)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->whereMonth('ba.fecha_hora', $month)
            ->count();

        return response()->json([
            "status" => 200,
            "count" => $count
        ]);
    }

    // 2.3 Tabla cards por plantilla
    public function tableCardsByIdPlantilla(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);

        $data = [];
        for ($i = 1; $i <= 3; $i++) {
            $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
                ->where('cards.id_plantilla', $i)
                ->where('ba.accion', 'CREAR')
                ->whereYear('ba.fecha_hora', $year)
                ->whereMonth('ba.fecha_hora', $month)
                ->count();

            $data[] = [
                "id_plantilla" => $i,
                "count_cards" => $count
            ];
        }

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }

    /* ============================================================
     * 3. METRICAS POR EMPLEADO
     * ============================================================
     */

    // 3.1 Cards por empleado
    public function listEmpleadoWithCards(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);
        $id = $request->input('id_empleado');

        $cards = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_empleado', $id)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->whereMonth('ba.fecha_hora', $month)
            ->select('cards.*')
            ->get();

        return response()->json([
            "status" => 200,
            "data" => $cards
        ]);
    }

    // 3.2 Cantidad cards por empleado
    public function countListOfCardsByEmpleado(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);
        $id = $request->input('id_empleado');

        $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_empleado', $id)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->whereMonth('ba.fecha_hora', $month)
            ->count();

        return response()->json([
            "status" => 200,
            "count" => $count
        ]);
    }

    // 3.3 Tabla cards por empleado
    public function tableCardsByEmpleado(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);
        $empleados = Empleado::where('id_rol', 1)->get();

        $data = [];
        foreach ($empleados as $empleado) {
            $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
                ->where('cards.id_empleado', $empleado->id_empleado)
                ->where('ba.accion', 'CREAR')
                ->whereYear('ba.fecha_hora', $year)
                ->whereMonth('ba.fecha_hora', $month)
                ->count();

            $data[] = [
                "id_empleado" => $empleado->id_empleado,
                "nombre_empleado" => $empleado->nombre,
                "count_cards" => $count
            ];
        }

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }

    /* ============================================================
     * 4. TIEMPO CREACIÓN → EDICIÓN
     * ============================================================
     */

    public function tiempoCreacionEdicionPublicacionCard(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);

        $data = BlogAuditoria::whereIn('accion', ['CREAR', 'ACTUALIZAR'])
            ->whereYear('fecha_hora', $year)
            ->whereMonth('fecha_hora', $month)
            ->orderBy('id_blog')
            ->get()
            ->groupBy('id_blog')
            ->map(function ($items, $id_blog) {
                $crear = $items->firstWhere('accion', 'CREAR');
                $editar = $items->firstWhere('accion', 'ACTUALIZAR');

                if (!$crear || !$editar) return null;

                return [
                    "id_blog" => $id_blog,
                    "tiempo_minutos" =>
                        Carbon::parse($crear->fecha_hora)
                            ->diffInMinutes(Carbon::parse($editar->fecha_hora))
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }
}
