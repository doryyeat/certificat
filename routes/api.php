<?php

use App\Http\Controllers\Api\Admin\BusinessController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Business\AnalyticsController;
use App\Http\Controllers\Api\Business\CertificateController as BusinessCertificateController;
use App\Http\Controllers\Api\Business\LocationController;
use App\Http\Controllers\Api\Public\CertificateController as PublicCertificateController;
use App\Http\Controllers\Api\Public\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (без аутентификации)
|--------------------------------------------------------------------------
*/
Route::prefix('api')
    ->name('api.public.')
    ->group(function () {
        Route::get('certificates', [PublicCertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{id}', [PublicCertificateController::class, 'show'])->name('certificates.show');

        Route::post('payments/yookassa', [PaymentController::class, 'yookassa'])->name('payments.yookassa');
        Route::post('payments/cloudpayments', [PaymentController::class, 'cloudpayments'])->name('payments.cloudpayments');

        Route::post('payments/yookassa/webhook', [PaymentController::class, 'yookassaWebhook'])->name('payments.yookassa.webhook');
        Route::post('payments/cloudpayments/webhook', [PaymentController::class, 'cloudpaymentsWebhook'])->name('payments.cloudpayments.webhook');
    });

/*
|--------------------------------------------------------------------------
| Business API Routes (требуется аутентификация и подписка)
|--------------------------------------------------------------------------
*/
Route::prefix('api/business')
    ->middleware(['auth:sanctum', 'verified.business'])
    ->name('api.business.')
    ->group(function () {

        Route::middleware(['subscription:api'])->group(function () {
            Route::apiResource('certificates', BusinessCertificateController::class)
                ->except(['show']);

            Route::post('certificates/split', [BusinessCertificateController::class, 'split'])->name('certificates.split');
            Route::post('certificates/{certificate}/redeem', [BusinessCertificateController::class, 'redeem'])->name('certificates.redeem');
        });

        Route::get('analytics', [AnalyticsController::class, 'index'])
            ->middleware('subscription:analytics')
            ->name('analytics.index');

        Route::get('analytics/detailed', [AnalyticsController::class, 'detailed'])
            ->middleware('subscription:pro')
            ->name('analytics.detailed');

        Route::apiResource('locations', LocationController::class);

        Route::get('realtime', [AnalyticsController::class, 'realtime'])->name('analytics.realtime');
    });

/*
|--------------------------------------------------------------------------
| Admin API Routes (только для админов)
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->name('api.admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::apiResource('businesses', BusinessController::class);
    });
