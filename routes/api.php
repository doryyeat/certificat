<?php

use App\Http\Controllers\Api\Admin\BusinessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public\CertificateController as PublicCertificateController;
use App\Http\Controllers\Api\Public\PaymentController;
use App\Http\Controllers\Api\Business\CertificateController as BusinessCertificateController;
use App\Http\Controllers\Api\Business\AnalyticsController;
use App\Http\Controllers\Api\Business\LocationController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Public API Routes (без аутентификации)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    // Публичный поиск сертификатов
    Route::get('certificates', [PublicCertificateController::class, 'index']);
    Route::get('certificates/{id}', [PublicCertificateController::class, 'show']);

    // Платежи
    Route::post('payments/yookassa', [PaymentController::class, 'yookassa']);
    Route::post('payments/cloudpayments', [PaymentController::class, 'cloudpayments']);

    // Webhooks для платежных систем
    Route::post('payments/yookassa/webhook', [PaymentController::class, 'yookassaWebhook']);
    Route::post('payments/cloudpayments/webhook', [PaymentController::class, 'cloudpaymentsWebhook']);
});

/*
|--------------------------------------------------------------------------
| Business API Routes (требуется аутентификация и подписка)
|--------------------------------------------------------------------------
*/
Route::prefix('api/business')
    ->middleware(['auth:sanctum', 'verified.business'])
    ->group(function () {

        // Сертификаты (требуется API доступ)
        Route::middleware(['subscription:api'])->group(function () {
            Route::apiResource('certificates', BusinessCertificateController::class)
                ->except(['show']);

            Route::post('certificates/split', [BusinessCertificateController::class, 'split']);
            Route::post('certificates/{certificate}/redeem', [BusinessCertificateController::class, 'redeem']);
        });

        // Аналитика (разные уровни доступа)
        Route::get('analytics', [AnalyticsController::class, 'index'])
            ->middleware('subscription:analytics');

        Route::get('analytics/detailed', [AnalyticsController::class, 'detailed'])
            ->middleware('subscription:pro');

        // Локации
        Route::apiResource('locations', LocationController::class);

        // Статистика в реальном времени (SSE)
        Route::get('realtime', [AnalyticsController::class, 'realtime']);
    });

/*
|--------------------------------------------------------------------------
| Admin API Routes (только для админов)
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index']);
        Route::apiResource('businesses', BusinessController::class);
        Route::get('businesses/{business}/analytics', [BusinessAnalyticsController::class, 'show']);
    });
