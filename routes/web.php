<?php

use App\Http\Controllers\Admin\BusinessUserController as AdminBusinessUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\RegisterRequestController as AdminRegisterRequestController;
use App\Http\Controllers\Api\Public\PaymentController;
use App\Http\Controllers\Business\RegisterRequestController as BusinessRegisterRequestController;
use App\Http\Controllers\Business\TariffController;
use App\Http\Controllers\Client\CertificateController;
use App\Http\Controllers\ClientTypeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GiftCertificateController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
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
Route::post('/set-client-type', [ClientTypeController::class, 'setType'])
    ->name('set-client-type');
Route::post('/register/request',[\App\Http\Controllers\Business\RegisterRequestController::class,'request'])->name('register.request');

Route::get('/business/apply', function () {
    return Inertia::render('Business/Apply');
})->name('business.apply');

Route::post('/business/register-request', [BusinessRegisterRequestController::class, 'request'])
    ->name('business.register-request');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/register-requests', [AdminRegisterRequestController::class, 'index'])->name('register-requests.index');
    Route::get('/register-requests/{register_request}', [AdminRegisterRequestController::class, 'show'])->name('register-requests.show');
    Route::post('/register-requests/{register_request}/accept', [AdminRegisterRequestController::class, 'accept'])->name('register-requests.accept');
    Route::post('/register-requests/{register_request}/reject', [AdminRegisterRequestController::class, 'reject'])->name('register-requests.reject');
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization}/edit', [AdminOrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{organization}', [AdminOrganizationController::class, 'update'])->name('organizations.update');
    Route::get('/business-users', [AdminBusinessUserController::class, 'index'])->name('business-users.index');
    Route::post('/business-users/{user}/block', [AdminBusinessUserController::class, 'block'])->name('business-users.block');
    Route::post('/business-users/{user}/unblock', [AdminBusinessUserController::class, 'unblock'])->name('business-users.unblock');
    Route::delete('/business-users/{user}', [AdminBusinessUserController::class, 'destroy'])->name('business-users.destroy');
});
Route::prefix('client')->name('client.')->group(function () {
    // Публичный каталог (доступен без авторизации)
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');

    // Защищенные роуты для покупателей
    Route::middleware(['auth', 'user.type:client'])->group(function () {
        Route::post('/certificates/{certificate}/purchase', [CertificateController::class, 'purchase'])->name('certificates.purchase');
        Route::get('/my-certificates', [CertificateController::class, 'myCertificates'])->name('my-certificates');
        Route::get('/my-certificates/{certificate}', [CertificateController::class, 'showPurchased'])->name('my-certificates.show');
        Route::post('/payment/process/{order}', [PaymentController::class, 'process'])->name('payment.process');
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
// В routes/web.php добавьте
        Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    });
    Route::get('/profile', [App\Http\Controllers\Client\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Client\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [App\Http\Controllers\Client\ProfileController::class, 'changePassword'])->name('profile.change-password');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:business'])->group(function () {
    Route::get('/business/tariff', [TariffController::class, 'show'])->name('business.tariff.show');
    Route::put('/business/tariff', [TariffController::class, 'update'])->name('business.tariff.update');

    Route::resource('stores', StoreController::class)->except(['show']);
    Route::post('/stores/geocode', [StoreController::class, 'geocode'])->name('stores.geocode');
    Route::resource('certificates', GiftCertificateController::class)->except(['edit', 'show']);
    Route::get('certificates/{certificate}', [GiftCertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/edit', [GiftCertificateController::class, 'edit'])->name('certificates.edit');
    Route::post('certificates/{certificate}/redeem', [GiftCertificateController::class, 'redeem'])->name('certificates.redeem');

    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
