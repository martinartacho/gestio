<?php

use App\Http\Controllers\Campus\CatalogController;
use App\Http\Controllers\Campus\CheckoutController;
use App\Http\Controllers\Campus\PortalController;
use App\Http\Controllers\Campus\StudentAuthController;
use App\Http\Controllers\Campus\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/cursos'));

// ── Catàleg públic ────────────────────────────────────────────────────────────
Route::prefix('cursos')->name('campus.catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('/{slug}', [CatalogController::class, 'show'])->name('show');
});

// ── Auth alumnes ──────────────────────────────────────────────────────────────
Route::prefix('portal')->name('campus.')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login'])->name('login.post');
    Route::get('/registre', [StudentAuthController::class, 'showRegister'])->name('register');
    Route::post('/registre', [StudentAuthController::class, 'register'])->name('register.post');
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');

    // ── Portal alumne (requereix auth) ────────────────────────────────────────
    Route::middleware(\App\Http\Middleware\AuthenticateStudent::class)->group(function () {
        Route::get('/meus-cursos', [PortalController::class, 'courses'])->name('portal.courses');
        Route::post('/checkout/{slug}', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    });
});

// ── Stripe Webhook (exclou CSRF) ──────────────────────────────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/admin/calendar/events', [\App\Http\Controllers\CalendarEventsController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('calendar.events');

Route::get('/admin/calendar/export-woocommerce', \App\Http\Controllers\WooCommerceExportController::class)
    ->middleware(['web', 'auth'])
    ->name('calendar.export.woocommerce');
