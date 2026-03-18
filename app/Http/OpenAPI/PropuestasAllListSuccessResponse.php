<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestasAllListSuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 200),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PropuestaListWithClientItem'),
            description: 'Array de propuestas con información del cliente'
        ),
    ]
)]
class PropuestasAllListSuccessResponse {}
