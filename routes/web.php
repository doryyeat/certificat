<?php

use App\Http\Controllers\Api\Public\PaymentController;
use App\Http\Controllers\ClientTypeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GiftCertificateController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\CertificateController as ClientCertificateController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'clientType' => request()->cookie('client_type', 'client'),
    ]);
})->name('home');

// Роут для выбора типа пользователя
Route::post('/set-client-type', [ClientTypeController::class, 'setType'])
    ->name('set-client-type');

// Публичные роуты для клиентов (покупателей)
Route::prefix('client')->name('client.')->group(function () {
    // Публичный каталог (доступен без авторизации)
    Route::get('/certificates', [ClientCertificateController::class, 'index'])->name('certificates.index');

    // Защищенные роуты для покупателей
    Route::middleware(['auth', 'user.type:client'])->group(function () {
        Route::post('/certificates/{certificate}/purchase', [ClientCertificateController::class, 'purchase'])->name('certificates.purchase');
        Route::get('/my-certificates', [ClientCertificateController::class, 'myCertificates'])->name('my-certificates');
        Route::get('/my-certificates/{certificate}', [ClientCertificateController::class, 'showPurchased'])->name('my-certificates.show');
        Route::post('/payment/process/{order}', [PaymentController::class, 'process'])->name('payment.process');
        Route::get('/certificates/{certificate}', [ClientCertificateController::class, 'show'])->name('certificates.show');

    });
    Route::get('/profile', [App\Http\Controllers\Client\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [App\Http\Controllers\Client\ProfileController::class, 'changePassword'])->name('profile.change-password');
});

// Роуты для бизнеса (требуется аутентификация и роль business)
Route::prefix('business')->name('business.')->middleware(['auth', 'user.type:business'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Business/Dashboard');
    })->name('dashboard');

    Route::get('/certificates', [GiftCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/create', [GiftCertificateController::class, 'create'])->name('certificates.create');
    Route::post('/certificates', [GiftCertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/{certificate}/edit', [GiftCertificateController::class, 'edit'])->name('certificates.edit');
    Route::put('/certificates/{certificate}', [GiftCertificateController::class, 'update'])->name('certificates.update');
    Route::delete('/certificates/{certificate}', [GiftCertificateController::class, 'destroy'])->name('certificates.destroy');
    Route::post('/certificates/{certificate}/redeem', [GiftCertificateController::class, 'redeem'])->name('certificates.redeem');
    Route::get('/profile', [App\Http\Controllers\Business\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\Business\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Business\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/analytics', function () {
        return Inertia::render('Business/Analytics');
    })->name('analytics');

    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
});

// Защищенные роуты (общие для всех авторизованных)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
