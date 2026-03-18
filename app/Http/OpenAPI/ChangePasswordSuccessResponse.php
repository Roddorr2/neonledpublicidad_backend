<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangePasswordSuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Contraseña cambiada exitosamente',
            description: 'Mensaje de confirmación'
        ),
    ]
)]
class ChangePasswordSuccessResponse {}
