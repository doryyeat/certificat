<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Certificat API',
    description: 'Основные публичные, бизнес и админ маршруты API.'
)]
#[OA\Server(
    url: '/',
    description: 'Current application host'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumBearer',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Bearer token (Sanctum)'
)]
#[OA\Get(
    path: '/api/api/certificates',
    operationId: 'publicCertificatesIndex',
    tags: ['Public Certificates'],
    summary: 'Список активных сертификатов',
    parameters: [
        new OA\Parameter(name: 'segment', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'lat', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
        new OA\Parameter(name: 'lng', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
        new OA\Parameter(name: 'radius', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50)),
        new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50)),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Успешный ответ'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Get(
    path: '/api/api/certificates/{id}',
    operationId: 'publicCertificatesShow',
    tags: ['Public Certificates'],
    summary: 'Детальная информация о сертификате',
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Успешный ответ'),
        new OA\Response(response: 404, description: 'Сертификат не найден'),
    ]
)]
#[OA\Post(
    path: '/api/api/payments/yookassa',
    operationId: 'publicPaymentsYookassa',
    tags: ['Public Payments'],
    summary: 'Создание платежа через YooKassa',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['order_id'],
            properties: [
                new OA\Property(property: 'order_id', type: 'integer'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Платеж инициирован'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Get(
    path: '/api/api/business/analytics',
    operationId: 'businessAnalyticsIndex',
    tags: ['Business Analytics'],
    summary: 'Аналитика бизнеса',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['week', 'month', 'quarter', 'year'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Успешный ответ'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
    ]
)]
#[OA\Get(
    path: '/api/api/admin/dashboard',
    operationId: 'adminDashboardIndex',
    tags: ['Admin'],
    summary: 'Сводная аналитика платформы',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'period', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['week', 'month', 'quarter', 'year'])),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Успешный ответ'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
    ]
)]
final class OpenApiSpec
{
}
