<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleReviewsService;
use Illuminate\Http\Request;

class GoogleReviewsController extends Controller
{
    /**
     * Devuelve las reseñas de Google Business (cacheadas) para el carrusel
     * de testimonios en /nosotros.
     *
     * GET /api/testimonios-google
     * GET /api/testimonios-google?refresh=1  -> fuerza a re-consultar Google
     */
    public function index(Request $request, GoogleReviewsService $service)
    {
        $forzarRefresco = $request->boolean('refresh');

        $data = $service->obtenerResenas($forzarRefresco);

        return response()->json($data, $data['ok'] ? 200 : 502);
    }
}