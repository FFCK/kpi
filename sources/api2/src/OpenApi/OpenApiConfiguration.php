<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        title: 'KPI API v2',
        description: 'Modern REST API for KPI (Kayak Polo Information) - Symfony 7.4 + API Platform 4.2'
    ),
    servers: [
        new OA\Server(url: '%env(API_DOCS_SERVER_URL)%', description: '%env(API_DOCS_SERVER_DESCRIPTION)%')
    ],
    tags: [
        new OA\Tag(name: '1. App2 - Authentication', description: 'Authentication endpoint for app2'),
        new OA\Tag(name: '2. App2 - Public', description: 'Public endpoints used by app2 (events, games, charts, statistics, ratings, game sheets)'),
        new OA\Tag(name: '3. App2 - Staff', description: 'Staff endpoints used by app2 (scrutineering) - Require token'),
        new OA\Tag(name: '4. Report', description: 'Game reports with events and players (requires token)'),
        new OA\Tag(name: '6. WSM - Web Score Management', description: 'Live scoring and match management (requires token)'),
        new OA\Tag(name: '20. App4 - Authentication', description: 'JWT Authentication for App4 admin interface'),
        new OA\Tag(name: '21. App4 - Filters', description: 'Filters for App4 admin interface'),
        new OA\Tag(name: '22. App4 - Events', description: 'Events management for App4 admin interface'),
        new OA\Tag(name: '23. App4 - Statistics', description: 'Statistics and reports for App4 admin interface'),
        new OA\Tag(name: '24. App4 - Operations', description: 'Season and team operations for App4 admin interface'),
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
    // This class holds OpenAPI metadata via attributes
    // The %env(VAR_NAME)% placeholders are processed by Symfony's parameter processor
}
