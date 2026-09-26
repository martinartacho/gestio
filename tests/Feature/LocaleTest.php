<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CampusStudent;
use App\Models\SiteSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_tenant_default_locale(): void
    {
        $tenant = $this->tenantWithLocale('es');

        $this->get("/{$tenant->slug}/privacy")->assertOk();

        $this->assertSame('es', app()->getLocale());
    }

    public function test_user_locale_overrides_tenant_default(): void
    {
        $tenant = $this->tenantWithLocale('es');
        $student = CampusStudent::factory()->create(['locale' => 'ca']);

        $this->actingAs($student, 'student')->get("/{$tenant->slug}/privacy")->assertOk();

        $this->assertSame('ca', app()->getLocale());
    }

    public function test_user_without_locale_follows_tenant_default(): void
    {
        $tenant = $this->tenantWithLocale('es');
        $student = CampusStudent::factory()->create(['locale' => null]);

        $this->actingAs($student, 'student')->get("/{$tenant->slug}/privacy")->assertOk();

        $this->assertSame('es', app()->getLocale());
    }

    public function test_unsupported_locale_is_ignored(): void
    {
        $tenant = $this->tenantWithLocale('es');
        $student = CampusStudent::factory()->create(['locale' => 'fr']);

        $this->actingAs($student, 'student')->get("/{$tenant->slug}/privacy")->assertOk();

        $this->assertSame('es', app()->getLocale());
    }

    public function test_preferred_locale_falls_back_to_users_tenant(): void
    {
        $student = CampusStudent::factory()->create(['locale' => null]);
        $student->tenants()->sync([$this->tenantWithLocale('es')->id]);

        $this->assertSame('es', $student->preferredLocale());

        $student->update(['locale' => 'ca']);
        $this->assertSame('ca', $student->fresh()->preferredLocale());
    }

    public function test_api_uses_user_locale(): void
    {
        $student = CampusStudent::factory()->create(['locale' => 'es']);
        Sanctum::actingAs($student);

        $this->getJson('/api/me')->assertOk();

        $this->assertSame('es', app()->getLocale());
    }

    public function test_public_pages_render_in_tenant_locale(): void
    {
        $tenant = $this->tenantWithLocale('es');
        SiteSetting::create(['tenant_id' => $tenant->id, 'key' => 'campus_enabled', 'value' => true]);

        $this->get("/{$tenant->slug}/portal/login")->assertOk()->assertSee('Acceso alumnos')->assertDontSee('Accés alumnes');
        $this->get("/{$tenant->slug}/portal/registre")->assertOk()->assertSee('Crear cuenta');
        $this->get("/{$tenant->slug}/cursos")->assertOk()->assertSee('Cursos disponibles');
        $this->get("/{$tenant->slug}/novetats")->assertOk()->assertSee('Historial de versiones');
    }

    public function test_public_pages_stay_in_catalan_by_default(): void
    {
        $this->get('/campus/portal/login')->assertOk()->assertSee('Accés alumnes');
        $this->get('/campus/novetats')->assertOk()->assertSee('Historial de versions');
    }

    public function test_home_link_points_to_current_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        SiteSetting::create(['tenant_id' => $tenant->id, 'key' => 'campus_enabled', 'value' => true]);

        $this->get("/{$tenant->slug}/portal/login")
            ->assertOk()
            ->assertSee('href="'.url("/{$tenant->slug}").'"', false);
    }

    private function tenantWithLocale(string $locale): Tenant
    {
        $tenant = Tenant::factory()->create();
        SiteSetting::create(['tenant_id' => $tenant->id, 'key' => 'locale', 'value' => $locale]);

        return $tenant;
    }
}
