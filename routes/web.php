<?php

use App\Http\Controllers\Associats\MemberAuthController;
use App\Http\Controllers\Associats\MemberPasswordController;
use App\Http\Controllers\Associats\MemberPortalController;
use App\Http\Controllers\Campus\CatalogController;
use App\Http\Controllers\Campus\CheckoutController;
use App\Http\Controllers\Campus\LmsPreviewController;
use App\Http\Controllers\Campus\LmsStudentController;
use App\Http\Controllers\Campus\LmsTeacherController;
use App\Http\Controllers\Campus\LmsTeacherWizardController;
use App\Http\Controllers\Campus\PortalController;
use App\Http\Controllers\Campus\QueueController;
use App\Http\Controllers\Campus\StudentAuthController;
use App\Http\Controllers\Campus\StripeWebhookController;
use App\Http\Controllers\Campus\DocumentController;
use App\Http\Controllers\Campus\TeacherAuthController;
use App\Http\Controllers\Campus\TeacherPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('campus.home'))->name('home');
Route::get('/novetats', fn() => view('campus.releases'))->name('campus.releases');

// ── Cua d'inscripcions (definida ABANS del wildcard /{slug}) ──────────────────
Route::prefix('cursos/cua')->name('campus.queue.')->middleware('campus.enabled')->group(function () {
    Route::get('/',             [QueueController::class, 'join'])
        ->middleware(\App\Http\Middleware\TrackCatalogVisits::class)
        ->name('join');
    Route::post('/',            [QueueController::class, 'store'])->name('store');
    Route::get('/estat',        [QueueController::class, 'status'])
        ->middleware(\App\Http\Middleware\TrackCatalogVisits::class)
        ->name('status');
    Route::post('/codi',        [QueueController::class, 'enterCode'])->name('code');
    Route::get('/canviar-torn', [QueueController::class, 'changeSlot'])->name('change-slot');
    Route::post('/canviar-torn',[QueueController::class, 'updateSlot'])->name('update-slot');
});

// ── Catàleg públic ────────────────────────────────────────────────────────────
Route::prefix('cursos')->name('campus.catalog.')->middleware('campus.enabled')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])
        ->middleware([\App\Http\Middleware\EnsureQueueAccess::class,
                      \App\Http\Middleware\TrackCatalogVisits::class])
        ->name('index');
    Route::get('/{slug}', [CatalogController::class, 'show'])
        ->middleware([\App\Http\Middleware\EnsureQueueAccess::class,
                      \App\Http\Middleware\TrackCatalogVisits::class])
        ->name('show');
});

// ── Auth alumnes ──────────────────────────────────────────────────────────────
Route::prefix('portal')->name('campus.')->middleware('campus.enabled')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::get('/registre', [StudentAuthController::class, 'showRegister'])->name('register');
    Route::post('/registre', [StudentAuthController::class, 'register'])->middleware('throttle:register')->name('register.post');
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');

    // ── Recuperació de contrasenya ────────────────────────────────────────────
    Route::get('/recuperar-contrasenya', [StudentAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/recuperar-contrasenya', [StudentAuthController::class, 'sendPasswordReset'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/recuperar-contrasenya/codi', [StudentAuthController::class, 'showPasswordResetCode'])->name('password.code');
    Route::post('/recuperar-contrasenya/codi', [StudentAuthController::class, 'resetPassword'])->middleware('throttle:10,5')->name('password.reset');

    // ── Verificació d'email per OTP ───────────────────────────────────────────
    Route::get('/verificar-email', [StudentAuthController::class, 'verificationNotice'])
        ->middleware(\App\Http\Middleware\AuthenticateStudent::class)
        ->name('verification.notice');

    Route::post('/verificar-email/codi', [StudentAuthController::class, 'verifyCode'])
        ->middleware([\App\Http\Middleware\AuthenticateStudent::class, 'throttle:10,5'])
        ->name('verification.code');

    Route::post('/reenviar-verificacio', [StudentAuthController::class, 'resendVerification'])
        ->middleware([\App\Http\Middleware\AuthenticateStudent::class, 'throttle:3,10'])
        ->name('verification.resend');

    // ── Portal alumne (requereix auth) ────────────────────────────────────────
    Route::middleware(\App\Http\Middleware\AuthenticateStudent::class)->group(function () {
        Route::get('/meus-cursos', [PortalController::class, 'courses'])->name('portal.courses');
        Route::post('/checkout/{slug}', [CheckoutController::class, 'create'])
            ->middleware(['throttle:checkout', \App\Http\Middleware\EnsureStudentEmailIsVerified::class])
            ->name('checkout.create');
        Route::post('/checkout/{slug}/cancel-enrollment', [CheckoutController::class, 'cancelEnrollment'])->middleware('throttle:checkout')->name('checkout.cancel-enrollment');
        Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    });
});

// ── Auth professorat ─────────────────────────────────────────────────────────
Route::prefix('professorat')->name('teacher.')->middleware('campus.enabled')->group(function () {
    Route::get('/login', [TeacherAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [TeacherAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [TeacherAuthController::class, 'logout'])->name('logout');

    Route::get('/recuperar-contrasenya', [TeacherAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/recuperar-contrasenya', [TeacherAuthController::class, 'sendPasswordReset'])->name('password.send');
    Route::get('/recuperar-contrasenya/codi', [TeacherAuthController::class, 'showPasswordResetCode'])->name('password.code');
    Route::post('/recuperar-contrasenya/codi', [TeacherAuthController::class, 'resetPassword'])->name('password.reset');

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

// ── LMS Alumnes ───────────────────────────────────────────────────────────────
Route::prefix('portal/lms')->name('campus.lms.')
    ->middleware([\App\Http\Middleware\AuthenticateStudent::class, 'feature:lms_enabled'])
    ->group(function () {
        Route::get('/curs/{slug}', [LmsStudentController::class, 'index'])->name('course');
        Route::get('/curs/{slug}/sessio/{lesson}', [LmsStudentController::class, 'show'])->name('lesson');
        Route::post('/sessio/{lesson}/completar', [LmsStudentController::class, 'complete'])->name('lesson.complete');
        Route::post('/sessio/{lesson}/resposta/{questionIndex}', [LmsStudentController::class, 'saveResponse'])->name('lesson.response.save');
        Route::get('/curs/{slug}/certificat', [LmsStudentController::class, 'certificate'])->name('course.certificate');
    });

// ── LMS Professors ────────────────────────────────────────────────────────────
Route::prefix('professorat/portal/lms')->name('teacher.lms.')
    ->middleware([\App\Http\Middleware\AuthenticateTeacher::class, 'feature:lms_enabled'])
    ->group(function () {
        // Wizard: Crear curs
        Route::get('/crear',            [LmsTeacherWizardController::class, 'step1'])->name('wizard.step1');
        Route::post('/crear/pas-1',     [LmsTeacherWizardController::class, 'storeStep1'])->name('wizard.store1');
        Route::get('/crear/sessions',   [LmsTeacherWizardController::class, 'step2'])->name('wizard.step2');
        Route::post('/crear/pas-2',     [LmsTeacherWizardController::class, 'storeStep2'])->name('wizard.store2');
        Route::get('/crear/revisio',    [LmsTeacherWizardController::class, 'step3'])->name('wizard.step3');
        Route::post('/crear/confirmar', [LmsTeacherWizardController::class, 'confirm'])->name('wizard.confirm');

        Route::get('/curs/{slug}', [LmsTeacherController::class, 'index'])->name('course');
        Route::get('/curs/{slug}/sessio/{lesson}', [LmsTeacherController::class, 'show'])->name('lesson');

        // Gestió de sessions (crear / editar)
        Route::get('/curs/{slug}/sessions/nova', [LmsTeacherController::class, 'create'])->name('lesson.create');
        Route::post('/curs/{slug}/sessions', [LmsTeacherController::class, 'store'])->name('lesson.store');
        Route::get('/curs/{slug}/sessions/{lesson}/editar', [LmsTeacherController::class, 'edit'])->name('lesson.edit');
        Route::patch('/curs/{slug}/sessions/{lesson}', [LmsTeacherController::class, 'update'])->name('lesson.update');
        Route::delete('/curs/{slug}/sessions/{lesson}', [LmsTeacherController::class, 'destroy'])->name('lesson.destroy');
    });

// ── LMS Previsualització admin ─────────────────────────────────────────────────
Route::get('/lms/preview/{lesson}', [LmsPreviewController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('lms.preview');

// ── Documents (descàrrega segura) ────────────────────────────────────────────
Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
    ->middleware('feature:documents_enabled')
    ->name('campus.documents.download');

// ── Stripe Webhook (exclou CSRF) ──────────────────────────────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Portal Socis (Associats) ──────────────────────────────────────────────────
Route::prefix('socis')->name('member.')->middleware('associats.enabled')->group(function () {
    Route::get('/login',  [MemberAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [MemberAuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::post('/logout',[MemberAuthController::class, 'logout'])->name('logout');

    Route::get('/recuperar-contrasenya',          [MemberPasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/recuperar-contrasenya',         [MemberPasswordController::class, 'sendReset'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/recuperar-contrasenya/{token}',  [MemberPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/recuperar-contrasenya/{token}', [MemberPasswordController::class, 'resetPassword'])->name('password.update');

    Route::middleware(\App\Http\Middleware\AuthenticateMember::class)->group(function () {
        Route::get('/carnet', [MemberPortalController::class, 'card'])->name('card');
        Route::get('/perfil', [MemberPortalController::class, 'profile'])->name('profile');
    });
});

Route::get('/admin/calendar/events', [\App\Http\Controllers\CalendarEventsController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('calendar.events');

Route::get('/admin/calendar/export-woocommerce', \App\Http\Controllers\WooCommerceExportController::class)
    ->middleware(['web', 'auth'])
    ->name('calendar.export.woocommerce');
