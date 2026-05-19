<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

Route::get('/admin/calendar/events', [\App\Http\Controllers\CalendarEventsController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('calendar.events');
