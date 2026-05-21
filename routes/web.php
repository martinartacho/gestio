<?php

use App\Http\Controllers\Campus\CatalogController;
use App\Http\Controllers\Campus\CheckoutController;
use App\Http\Controllers\Campus\PortalController;
use App\Http\Controllers\Campus\StudentAuthController;
use App\Http\Controllers\Campus\StripeWebhookController;
use App\Http\Controllers\Campus\DocumentController;
use App\Http\Controllers\Campus\TeacherAuthController;
use App\Http\Controllers\Campus\TeacherPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('campus.home'))->name('home');

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

// ── Auth professorat ─────────────────────────────────────────────────────────
Route::prefix('professorat')->name('teacher.')->group(function () {
    Route::get('/login', [TeacherAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [TeacherAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [TeacherAuthController::class, 'logout'])->name('logout');

    // ── Portal professor (requereix auth) ─────────────────────────────────
    Route::middleware(\App\Http\Middleware\AuthenticateTeacher::class)
        ->prefix('portal')->name('portal.')->group(function () {
            Route::get('/', [TeacherPortalController::class, 'courses'])->name('courses');
            Route::get('/curs/{slug}', [TeacherPortalController::class, 'course'])->name('course');
            Route::post('/curs/{slug}/documents', [TeacherPortalController::class, 'uploadDocument'])->name('course.documents.upload');
            Route::delete('/curs/{slug}/documents/{document}', [TeacherPortalController::class, 'deleteDocument'])->name('course.documents.delete');
            // ── Gestor global de documents ─────────────────────────────────
            Route::get('/documents', [TeacherPortalController::class, 'documents'])->name('documents');
            Route::get('/documents/{document}/editar', [TeacherPortalController::class, 'editDocument'])->name('documents.edit');
            Route::post('/documents/{document}', [TeacherPortalController::class, 'updateDocument'])->name('documents.update');
            Route::delete('/documents/{document}', [TeacherPortalController::class, 'destroyDocument'])->name('documents.destroy');
            Route::get('/perfil', [TeacherPortalController::class, 'editProfile'])->name('profile');
            Route::post('/perfil', [TeacherPortalController::class, 'updateProfile'])->name('profile.update');
            Route::get('/liquidacions', [TeacherPortalController::class, 'liquidations'])->name('liquidations');
        });
});

// ── Documents (descàrrega segura) ────────────────────────────────────────────
Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
    ->middleware('feature:documents_enabled')
    ->name('campus.documents.download');

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
