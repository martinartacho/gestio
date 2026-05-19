<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

Route::get('/admin/calendar/events', [\App\Http\Controllers\CalendarEventsController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('calendar.events');

Route::get('/admin/calendar/export-woocommerce', \App\Http\Controllers\WooCommerceExportController::class)
    ->middleware(['web', 'auth'])
    ->name('calendar.export.woocommerce');
