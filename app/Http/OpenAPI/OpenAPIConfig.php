<?php

namespace App\Http\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: 'Neon House LED - Backend API',
        description: 'API REST para gestión de propuestas, clientes, blogs, productos y más',
        version: '1.0.0',
        contact: new OA\Contact(
            name: 'Neon House LED',
            email: 'support@neonhouseled.com',
            url: 'https://neonhouseled.com'
        ),
        license: new OA\License(
            name: 'MIT',
            url: 'https://opensource.org/licenses/MIT'
        ),
    ),
    servers: [
        new OA\Server(
            url: 'http://localhost:8000/api',
            description: 'Desarrollo local'
        ),
        new OA\Server(
            url: 'http://localhost:3000/api',
            description: 'Producción (ejemplo)'
        ),
    ],
    components: new OA\Components(
        securitySchemes: [
            'bearerAuth' => new OA\SecurityScheme(
                description: 'Login para obtener el token Bearer jwt',
                name: 'Authorization',
                in: 'header',
                bearerFormat: 'JWT',
                scheme: 'bearer',
                type: 'http',
                securityScheme: 'bearerAuth',
            ),
        ],
    ),
)]
class OpenAPIConfig {}
