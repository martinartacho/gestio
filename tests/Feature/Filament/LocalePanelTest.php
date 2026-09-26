<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

/** Totes les pàgines del panell s'han de poder renderitzar en català i en castellà. */
class LocalePanelTest extends TestCase
{
    use InteractsWithFilamentAdmin, RefreshDatabase;

    private const PAGES = [
        '', 'settings-page', 'calendar-page', 'tresoreria-dashboard',
        'associat-members', 'associat-members/create', 'associat-quotes', 'associat-quotes/create',
        'associat-sepa-remittances', 'associat-sepa-remittances/create', 'blocked-ips', 'blocked-ips/create',
        'campus-students', 'campus-students/create', 'categories', 'categories/create',
        'courses', 'courses/create', 'documents', 'documents/create', 'enrollments', 'enrollments/create',
        'holidays', 'holidays/create', 'noticies', 'noticies/create', 'payments', 'payments/create',
        'roles', 'roles/create', 'seasons', 'seasons/create', 'spaces', 'spaces/create',
        'teacher-payments', 'teacher-payments/create', 'teachers', 'teachers/create',
        'tenants', 'tenants/create', 'time-slots', 'time-slots/create',
        'tresoreria-quotes', 'tresoreria-quotes/create', 'tresoreria-remittances', 'tresoreria-remittances/create',
        'users', 'users/create',
    ];

    public function test_all_panel_pages_render_in_both_locales(): void
    {
        $admin = $this->createAdmin();
        $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']));

        foreach (['ca', 'es'] as $locale) {
            $admin->update(['locale' => $locale]);
            $this->actingAs($admin->fresh());

            foreach (self::PAGES as $page) {
                $this->get('/admin/campus/'.$page)->assertOk();
            }
        }

        $this->get('/admin/campus/courses')->assertSee('Cursos');
        $this->get('/admin/campus/settings-page')->assertSee('Configuración del sitio');
    }
}
