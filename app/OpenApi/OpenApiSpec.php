<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Certificat API',
    description: 'Документация API и web-маршрутов для клиентской, бизнес и админ частей.'
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
#[OA\Tag(name: 'Public API')]
#[OA\Tag(name: 'Business API')]
#[OA\Tag(name: 'Admin API')]
#[OA\Tag(name: 'Web Public')]
#[OA\Tag(name: 'Web Client')]
#[OA\Tag(name: 'Web Business')]
#[OA\Tag(name: 'Web Admin')]
#[OA\Get(
    path: '/api/api/certificates',
    operationId: 'publicCertificatesIndex',
    tags: ['Public API'],
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
    tags: ['Public API'],
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
    tags: ['Public API'],
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
    tags: ['Business API'],
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
#[OA\Put(
    path: '/api/api/business/certificates/{certificate}',
    operationId: 'businessCertificatesUpdate',
    tags: ['Business API'],
    summary: 'Обновить сертификат бизнеса',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'certificate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(type: 'object')
    ),
    responses: [
        new OA\Response(response: 200, description: 'Сертификат обновлен'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Delete(
    path: '/api/api/business/certificates/{certificate}',
    operationId: 'businessCertificatesDestroy',
    tags: ['Business API'],
    summary: 'Удалить сертификат бизнеса',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'certificate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Сертификат удален'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 404, description: 'Сертификат не найден'),
    ]
)]
#[OA\Put(
    path: '/api/api/business/locations/{location}',
    operationId: 'businessLocationsUpdate',
    tags: ['Business API'],
    summary: 'Обновить локацию бизнеса',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'location', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(type: 'object')
    ),
    responses: [
        new OA\Response(response: 200, description: 'Локация обновлена'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Delete(
    path: '/api/api/business/locations/{location}',
    operationId: 'businessLocationsDestroy',
    tags: ['Business API'],
    summary: 'Удалить локацию бизнеса',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'location', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Локация удалена'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 404, description: 'Локация не найдена'),
    ]
)]
#[OA\Get(
    path: '/api/api/admin/dashboard',
    operationId: 'adminDashboardIndex',
    tags: ['Admin API'],
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
#[OA\Put(
    path: '/api/api/admin/businesses/{business}',
    operationId: 'adminBusinessesUpdate',
    tags: ['Admin API'],
    summary: 'Обновить бизнес',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(type: 'object')
    ),
    responses: [
        new OA\Response(response: 200, description: 'Бизнес обновлен'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Delete(
    path: '/api/api/admin/businesses/{business}',
    operationId: 'adminBusinessesDestroy',
    tags: ['Admin API'],
    summary: 'Удалить бизнес',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'business', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Бизнес удален'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 404, description: 'Бизнес не найден'),
    ]
)]
#[OA\Post(
    path: '/set-client-type',
    operationId: 'webSetClientType',
    tags: ['Web Public'],
    summary: 'Сохранить тип клиента в cookie',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['client_type'],
            properties: [
                new OA\Property(property: 'client_type', type: 'string', enum: ['client', 'business']),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Тип клиента сохранен'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Post(
    path: '/register/request',
    operationId: 'webBusinessRegisterRequestLegacy',
    tags: ['Web Public'],
    summary: 'Отправить заявку на регистрацию бизнеса (legacy endpoint)',
    responses: [
        new OA\Response(response: 200, description: 'Заявка отправлена'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Post(
    path: '/business/register-request',
    operationId: 'webBusinessRegisterRequest',
    tags: ['Web Public'],
    summary: 'Отправить заявку на регистрацию бизнеса',
    responses: [
        new OA\Response(response: 200, description: 'Заявка отправлена'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Post(
    path: '/client/certificates/{certificate}/purchase',
    operationId: 'webClientCertificatePurchase',
    tags: ['Web Client'],
    summary: 'Покупка сертификата клиентом',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'certificate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 302, description: 'Покупка инициирована'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Post(
    path: '/client/payment/process/{order}',
    operationId: 'webClientPaymentProcess',
    tags: ['Web Client'],
    summary: 'Обработка оплаты заказа клиента',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Платеж обработан'),
        new OA\Response(response: 302, description: 'Редирект после оплаты'),
        new OA\Response(response: 401, description: 'Не авторизован'),
    ]
)]
#[OA\Post(
    path: '/business/branding',
    operationId: 'webBusinessBrandingUpdate',
    tags: ['Web Business'],
    summary: 'Обновить брендирование бизнеса',
    security: [['sanctumBearer' => []]],
    responses: [
        new OA\Response(response: 302, description: 'Брендирование обновлено'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
    ]
)]
#[OA\Post(
    path: '/business/pos/redeem',
    operationId: 'webBusinessPosRedeem',
    tags: ['Web Business'],
    summary: 'Погасить сертификат на POS',
    security: [['sanctumBearer' => []]],
    responses: [
        new OA\Response(response: 200, description: 'Сертификат погашен'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
        new OA\Response(response: 422, description: 'Ошибка валидации'),
    ]
)]
#[OA\Post(
    path: '/admin/register-requests/{register_request}/accept',
    operationId: 'webAdminRegisterRequestAccept',
    tags: ['Web Admin'],
    summary: 'Подтвердить заявку на регистрацию',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'register_request', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 302, description: 'Заявка подтверждена'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
    ]
)]
#[OA\Post(
    path: '/admin/register-requests/{register_request}/reject',
    operationId: 'webAdminRegisterRequestReject',
    tags: ['Web Admin'],
    summary: 'Отклонить заявку на регистрацию',
    security: [['sanctumBearer' => []]],
    parameters: [
        new OA\Parameter(name: 'register_request', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 302, description: 'Заявка отклонена'),
        new OA\Response(response: 401, description: 'Не авторизован'),
        new OA\Response(response: 403, description: 'Нет доступа'),
    ]
)]
final class OpenApiSpec
{
}
