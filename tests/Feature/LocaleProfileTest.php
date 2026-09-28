<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Auth\EditProfile;
use App\Models\AssociatMember;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\SiteSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class LocaleProfileTest extends TestCase
{
    use InteractsWithFilamentAdmin, RefreshDatabase;

    public function test_student_can_set_locale_in_profile(): void
    {
        $student = CampusStudent::factory()->create();

        $this->actingAs($student, 'student')->get('/campus/portal/perfil')->assertOk();

        $this->actingAs($student, 'student')
            ->post('/campus/portal/perfil', ['locale' => 'es'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Perfil actualizado correctamente.');

        $this->assertSame('es', $student->fresh()->locale);
    }

    public function test_student_can_reset_locale_to_tenant_default(): void
    {
        $student = CampusStudent::factory()->create(['locale' => 'es']);

        $this->actingAs($student, 'student')->post('/campus/portal/perfil', ['locale' => '']);

        $this->assertNull($student->fresh()->locale);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $student = CampusStudent::factory()->create();

        $this->actingAs($student, 'student')
            ->post('/campus/portal/perfil', ['locale' => 'en'])
            ->assertSessionHasErrors('locale');
    }

    public function test_teacher_can_set_locale_in_profile(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->actingAs($teacher, 'teacher')
            ->post('/campus/professorat/portal/perfil', ['locale' => 'es'])
            ->assertSessionHasNoErrors();

        $this->assertSame('es', $teacher->fresh()->locale);
    }

    public function test_member_can_set_locale_in_profile(): void
    {
        $tenant = Tenant::where('slug', 'campus')->first();
        SiteSetting::create(['tenant_id' => $tenant->id, 'key' => 'associats_enabled', 'value' => true]);
        $member = AssociatMember::factory()->create();

        $this->actingAs($member, 'member')->get('/campus/socis/perfil')->assertOk();

        $this->actingAs($member, 'member')
            ->post('/campus/socis/perfil/idioma', ['locale' => 'es'])
            ->assertSessionHasNoErrors();

        $this->assertSame('es', $member->fresh()->locale);

        $this->actingAs($member->fresh(), 'member')
            ->get('/campus/socis/perfil')
            ->assertOk()
            ->assertSee('Número de socio')
            ->assertSee('Volver al carné');
    }

    public function test_admin_can_set_locale_in_filament_profile(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->get('/admin/profile')->assertOk();

        Livewire::test(EditProfile::class)
            ->fillForm(['locale' => 'es'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('es', $admin->fresh()->locale);
    }

    public function test_api_profile_updates_and_returns_locale(): void
    {
        $student = CampusStudent::factory()->create();
        Sanctum::actingAs($student);

        $this->patchJson('/api/profile', ['locale' => 'es'])
            ->assertOk()
            ->assertJsonPath('data.locale', 'es')
            ->assertJsonPath('data.effective_locale', 'es');
    }
}
