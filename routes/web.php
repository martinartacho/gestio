<?php

use Illuminate\Support\Facades\Route;

// Convenient per no deixar la BD arrel en blanc mentre només hi ha un tenant.
// Quan calgui una pàgina real de selecció, es canvia aquí.
Route::redirect('/', '/campus');

// Sense això, '/admin' (un sol segment) el capturaria el grup {tenant} de
// sota com si "admin" fos un slug de tenant, donant 404 en lloc de portar
// al login. Ha d'anar ABANS del grup perquè Laravel el faci servir primer.
Route::redirect('/admin', '/admin/login');

// Totes les rutes públiques (catàleg, portals d'alumnes/professorat/socis...)
// viuen a web-content.php i es munten sota /{tenant}/... — ResolveWebTenant
// lliga el Tenant corresponent al contenidor perquè current_tenant() el
// trobi a qualsevol controlador (BD compartida, sense canvi de connexió).
Route::prefix('{tenant}')
    ->middleware(\App\Http\Middleware\ResolveWebTenant::class)
    ->group(__DIR__.'/web-content.php');

// Endpoints interns de l'admin (JS del calendari Filament), no són pàgines
// públiques del tenant — es queden fora del prefix /{tenant}.
Route::get('/admin/calendar/events', [\App\Http\Controllers\CalendarEventsController::class, 'index'])
    ->middleware(['web', 'auth'])
    ->name('calendar.events');

Route::get('/admin/calendar/export-courses', \App\Http\Controllers\CourseExportController::class)
    ->middleware(['web', 'auth'])
    ->name('calendar.export.courses');
