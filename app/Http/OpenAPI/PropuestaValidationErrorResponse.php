<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestaValidationErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 422),
        new OA\Property(
            property: 'message',
            type: 'object',
            example: ['id_cliente' => ['El id_cliente es requerido', 'El id_cliente debe ser numérico']]
        ),
    ]
)]
class PropuestaValidationErrorResponse {}
