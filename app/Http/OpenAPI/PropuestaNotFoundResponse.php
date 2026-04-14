<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestaNotFoundResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 404),
        new OA\Property(property: 'message', type: 'string', example: 'No se encontraron propuestas'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
    ]
)]
class PropuestaNotFoundResponse {}
