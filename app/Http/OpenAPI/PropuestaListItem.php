<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropuestaListItem',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'nombre', type: 'string', example: 'Propuesta Premium'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Descripción de la propuesta'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-03-15T10:30:00'),
        new OA\Property(property: 'id_cliente', type: 'integer', example: 5),
        new OA\Property(
            property: 'images',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uri'),
            example: ['/storage/cliente/5/propuestas/1/imagenes/image1.jpg'],
            description: 'URLs de imágenes almacenadas'
        ),
        new OA\Property(
            property: 'videos',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uri'),
            example: ['/storage/cliente/5/propuestas/1/videos/video1.mp4'],
            description: 'URLs de videos almacenados'
        ),
    ]
)]
class PropuestaListItem {}
