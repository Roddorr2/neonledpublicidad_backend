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
use Illuminate\Validation\Rule;

class MetricasController extends Controller
{
    /**
     * Helper para resolver mes y año con validación estricta
     */
    private function resolveMonthYear(Request $request)
    {
        // Limites razonables: mes 1..12, año entre 1970 y (año actual + 1)
        $currentYear = (int) Carbon::now()->year;
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year'  => ['nullable', 'integer', 'min:2000', 'max:' . ($currentYear + 5)],
        ]);

        // Si no se envía mes, devolvemos null para permitir filtro anual
        $month = $request->has('month') ? $validated['month'] : null;
        $year  = $validated['year'] ?? $currentYear;

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
            ->when($month, fn($q) => $q->whereMonth('fecha_hora', $month))
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

        $validated = $request->validate([
            'id_plantilla' => ['required', 'integer', 'min:1'],
            // Si existe tabla plantillas, habilitar esta regla:
            // Rule::exists('plantillas', 'id_plantilla')
        ]);

        $plantilla = $validated['id_plantilla'];

        $cards = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_plantilla', $plantilla)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
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

        $validated = $request->validate([
            'id_plantilla' => ['required', 'integer', 'min:1'],
            // Rule::exists('plantillas', 'id_plantilla')
        ]);

        $plantilla = $validated['id_plantilla'];

        $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_plantilla', $plantilla)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
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
        $counts = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
            ->selectRaw('cards.id_plantilla, count(*) as count_cards')
            ->groupBy('cards.id_plantilla')
            ->pluck('count_cards', 'id_plantilla');

        $data = collect([1, 2, 3])->map(fn($i) => [
            'id_plantilla' => $i,
            'count_cards'  => $counts[$i] ?? 0,
        ]);
        
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

        $validated = $request->validate([
            'id_empleado' => [
                'required', 'integer', 'min:1',
                // Si el campo clave en tabla empleados es id_empleado:
                // Rule::exists('empleados', 'id_empleado')
            ],
        ]);

        $id = $validated['id_empleado'];

        $cards = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_empleado', $id)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
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

        $validated = $request->validate([
            'id_empleado' => [
                'required', 'integer', 'min:1',
                // Rule::exists('empleados', 'id_empleado')
            ],
        ]);

        $id = $validated['id_empleado'];

        $count = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('cards.id_empleado', $id)
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
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

        // Optimización: Obtener conteos agrupados en una sola consulta
        $counts = Card::join('blog_auditoria as ba', 'ba.id_blog', '=', 'cards.id_blog')
            ->where('ba.accion', 'CREAR')
            ->whereYear('ba.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('ba.fecha_hora', $month))
            ->select('cards.id_empleado', DB::raw('count(*) as total'))
            ->groupBy('cards.id_empleado')
            ->pluck('total', 'id_empleado');

        $empleados = Empleado::where('id_rol', 1)->get();

        $data = [];
        foreach ($empleados as $empleado) {
            $data[] = [
                "id_empleado" => $empleado->id_empleado,
                "nombre_empleado" => $empleado->nombre,
                "count_cards" => $counts[$empleado->id_empleado] ?? 0
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
    // 4. TIEMPO CREACIÓN → EDICIÓN
    public function tiempoCreacionEdicionPublicacionCard(Request $request)
    {
        [$month, $year] = $this->resolveMonthYear($request);

        $data = BlogAuditoria::from('blog_auditoria as crear')
            ->join('blog_auditoria as editar', function ($join) {
                $join->on('crear.id_blog', '=', 'editar.id_blog')
                     ->where('editar.accion', '=', 'ACTUALIZAR');
            })
            ->where('crear.accion', 'CREAR')
            ->whereYear('crear.fecha_hora', $year)
            ->when($month, fn($q) => $q->whereMonth('crear.fecha_hora', $month))
            ->selectRaw('crear.id_blog, TIMESTAMPDIFF(MINUTE, crear.fecha_hora, MIN(editar.fecha_hora)) as tiempo_minutos')
            ->groupBy('crear.id_blog', 'crear.fecha_hora')
            ->get();

        return response()->json([
            "status" => 200,
            "data" => $data
        ]);
    }
}