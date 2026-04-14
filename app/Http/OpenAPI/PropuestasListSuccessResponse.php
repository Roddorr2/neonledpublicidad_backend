<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestasListSuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 200),
        new OA\Property(
            property: 'message',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/PropuestaListItem'),
            description: 'Array de propuestas'
        ),
    ]
)]
class PropuestasListSuccessResponse {}
