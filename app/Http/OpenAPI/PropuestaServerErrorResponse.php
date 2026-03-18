<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestaServerErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 500),
        new OA\Property(property: 'message', type: 'string', example: 'Error en el servidor'),
    ]
)]
class PropuestaServerErrorResponse {}
