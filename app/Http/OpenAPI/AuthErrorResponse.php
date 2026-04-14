<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'error',
            type: 'string',
            example: 'No autorizado',
            description: 'Mensaje de error de autenticación'
        ),
    ]
)]
class AuthErrorResponse {}
