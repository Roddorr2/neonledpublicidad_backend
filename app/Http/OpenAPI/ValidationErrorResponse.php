<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['currentPassword' => ['Campo requerido'], 'newPassword' => ['Mínimo 8 caracteres']]
        ),
    ]
)]
class ValidationErrorResponse {}
