<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'KPI API v2',
        description: 'Modern REST API for KPI (Kayak Polo Information) - Symfony 7.3 + API Platform 4.2'
    ),
    servers: [
        new OA\Server(url: 'https://kpi.localhost/api2', description: 'Development server'),
        new OA\Server(url: 'https://kayak-polo.info/api2', description: 'Production server')
    ],
    tags: [
        new OA\Tag(name: '1. App2 - Authentication', description: 'Authentication endpoint for app2'),
        new OA\Tag(name: '2. App2 - Public', description: 'Public endpoints used by app2 (events, games, charts, statistics, ratings, game sheets)'),
        new OA\Tag(name: '3. App2 - Staff', description: 'Staff endpoints used by app2 (scrutineering) - Require token'),
        new OA\Tag(name: '4. Report', description: 'Game reports with events and players (requires token)'),
        new OA\Tag(name: '6. WSM - Web Score Management', description: 'Live scoring and match management (requires token)')
    ]
)]
#[OA\SecurityScheme(
    securityScheme: 'BasicAuth',
    type: 'http',
    scheme: 'basic',
    description: 'HTTP Basic Authentication for login endpoint'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiToken',
    type: 'apiKey',
    in: 'header',
    name: 'X-Auth-Token',
    description: 'Token obtained from /login endpoint. Can also be sent via cookie `kpi_app`.'
)]
class OpenApiConfiguration
{
    // This class only holds OpenAPI metadata via attributes
}
