<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para obtener reseñas de Google Business (Places API - Place Details).
 *
 * Google solo devuelve un máximo de 5 reseñas por consulta y las elige con
 * su propio criterio de "relevancia" (no se puede elegir ni paginar).
 *
 * La respuesta se cachea (ver config('services.google_places.cache_ttl'))
 * para no consumir cuota de la API en cada visita al sitio.
 */
class GoogleReviewsService
{
    protected const CACHE_KEY = 'google_places_reviews';

    protected const ENDPOINT = 'https://maps.googleapis.com/maps/api/place/details/json';

    /**
     * Devuelve las reseñas cacheadas, o las consulta a Google si el caché expiró.
     *
     * @return array{ok: bool, reviews: array, rating: float|null, total: int|null, cached: bool, error?: string}
     */
    public function obtenerResenas(bool $forzarRefresco = false): array
    {
        $ttl = (int) config('services.google_places.cache_ttl', 86400);

        if ($forzarRefresco) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, $ttl, function () {
            return $this->consultarGoogle();
        });
    }

    /**
     * Llama directamente a la API de Google (sin caché). Uso interno.
     */
    protected function consultarGoogle(): array
    {
        $apiKey  = config('services.google_places.api_key');
        $placeId = config('services.google_places.place_id');

        if (! $apiKey || ! $placeId) {
            Log::warning('GoogleReviewsService: falta GOOGLE_PLACES_API_KEY o GOOGLE_PLACES_ID en .env');

            return [
                'ok'      => false,
                'reviews' => [],
                'rating'  => null,
                'total'   => null,
                'cached'  => false,
                'error'   => 'Credenciales de Google Places no configuradas',
            ];
        }

        try {
            $response = Http::timeout(8)->get(self::ENDPOINT, [
                'place_id' => $placeId,
                'fields'   => 'reviews,rating,user_ratings_total',
                'language' => 'es',
                'key'      => $apiKey,
            ]);

            if (! $response->successful()) {
                Log::error('GoogleReviewsService: error HTTP consultando Google Places', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return $this->respuestaVacia('Error al consultar Google Places (HTTP '.$response->status().')');
            }

            $data = $response->json();

            if (($data['status'] ?? null) !== 'OK') {
                Log::error('GoogleReviewsService: status distinto de OK', $data);

                return $this->respuestaVacia('Google Places respondió: '.($data['status'] ?? 'desconocido'));
            }

            $result  = $data['result'] ?? [];
            $reviews = collect($result['reviews'] ?? [])->map(function ($review) {
                return [
                    'id'     => $review['time'] ?? uniqid('review_'),
                    'name'   => $review['author_name'] ?? 'Cliente Google',
                    'avatar' => $review['profile_photo_url'] ?? null,
                    'rating' => $review['rating'] ?? 5,
                    'text'   => $review['text'] ?? '',
                    'date'   => $review['relative_time_description'] ?? '',
                    'source' => 'google',
                ];
            })->values()->all();

            return [
                'ok'      => true,
                'reviews' => $reviews,
                'rating'  => $result['rating'] ?? null,
                'total'   => $result['user_ratings_total'] ?? null,
                'cached'  => false,
            ];
        } catch (\Throwable $e) {
            Log::error('GoogleReviewsService: excepción consultando Google Places', [
                'message' => $e->getMessage(),
            ]);

            return $this->respuestaVacia('Excepción al consultar Google Places');
        }
    }

    protected function respuestaVacia(string $error): array
    {
        return [
            'ok'      => false,
            'reviews' => [],
            'rating'  => null,
            'total'   => null,
            'cached'  => false,
            'error'   => $error,
        ];
    }
}