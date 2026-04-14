<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'empleado',
            type: 'object',
            properties: [
                new OA\Property(property: 'id_empleado', type: 'integer'),
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
            ]
        ),
    ]
)]
class UserResponse {}
