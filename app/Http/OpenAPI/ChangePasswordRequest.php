<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangePasswordRequest',
    type: 'object',
    required: ['currentPassword', 'newPassword'],
    properties: [
        new OA\Property(
            property: 'currentPassword',
            type: 'string',
            format: 'password',
            example: 'currentPass123',
            description: 'Contraseña actual del usuario'
        ),
        new OA\Property(
            property: 'newPassword',
            type: 'string',
            format: 'password',
            minLength: 8,
            example: 'newPassword123',
            description: 'Nueva contraseña (mínimo 8 caracteres)'
        ),
    ]
)]
class ChangePasswordRequest {}
