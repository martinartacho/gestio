<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CampusStudents\Pages\CreateCampusStudent;
use App\Filament\Resources\CampusStudents\Pages\EditCampusStudent;
use App\Filament\Resources\TeacherResource\Pages\CreateTeacher;
use App\Filament\Resources\TeacherResource\Pages\EditTeacher;
use App\Filament\Support\PasswordField;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

/** Contrasenya de professors i alumnes al panel: la per defecte del .env (només admin/manager) i el canvi des de l'edició. */
class PasswordFieldTest extends TestCase
{
    use InteractsWithFilamentAdmin, RefreshDatabase;

    // Contrasenyes de prova generades a l'executar: cap literal que sembli un secret al repositori.
    private string $defaultTeacher;

    private string $defaultStudent;

    private string $oldPassword;

    private string $newPassword;

    private string $ownPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultTeacher = Str::random(24);
        $this->defaultStudent = Str::random(24);
        $this->oldPassword = Str::random(20);
        $this->newPassword = Str::random(20);
        $this->ownPassword = Str::random(20);

        config(['seeder.teacher_password' => $this->defaultTeacher, 'seeder.student_password' => $this->defaultStudent]);
    }

    private function asAdmin(): void
    {
        $this->actingAs($this->createAdmin());
    }

    // ── Professors ──────────────────────────────────────────────────────────

    public function test_the_teacher_edit_page_leaves_the_password_empty(): void
    {
        $this->asAdmin();
        $teacher = CampusTeacher::factory()->create(['password' => $this->oldPassword]);

        Livewire::test(EditTeacher::class, ['record' => $teacher->getKey()])
            ->assertFormSet(['password' => null]);

        $this->get("/admin/campus/teachers/{$teacher->id}/edit")->assertSuccessful()->assertDontSee($this->defaultTeacher);
    }

    public function test_an_admin_changes_the_teacher_password_and_it_is_stored_hashed(): void
    {
        $this->asAdmin();
        $teacher = CampusTeacher::factory()->create(['password' => $this->oldPassword]);

        Livewire::test(EditTeacher::class, ['record' => $teacher->getKey()])
            ->fillForm(['phone' => '600123456', 'password' => $this->newPassword])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = $teacher->fresh()->password;
        $this->assertNotSame($this->newPassword, $stored);
        $this->assertTrue(Hash::check($this->newPassword, $stored));
        $this->assertFalse(Hash::check($this->oldPassword, $stored));
    }

    public function test_leaving_the_password_empty_keeps_the_current_one(): void
    {
        $this->asAdmin();
        $teacher = CampusTeacher::factory()->create(['password' => $this->oldPassword]);
        $before = $teacher->password;

        Livewire::test(EditTeacher::class, ['record' => $teacher->getKey()])
            ->fillForm(['first_name' => 'Anna', 'phone' => '600123456', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $teacher->refresh();
        $this->assertSame('Anna', $teacher->first_name);
        $this->assertSame($before, $teacher->password);
        $this->assertTrue(Hash::check($this->oldPassword, $teacher->password));
    }

    public function test_a_too_short_password_is_rejected(): void
    {
        $this->asAdmin();
        $teacher = CampusTeacher::factory()->create(['password' => $this->oldPassword]);

        Livewire::test(EditTeacher::class, ['record' => $teacher->getKey()])
            ->fillForm(['password' => Str::random(5)])
            ->call('save')
            ->assertHasFormErrors(['password' => 'min']);

        $this->assertTrue(Hash::check($this->oldPassword, $teacher->fresh()->password));
    }

    public function test_creating_a_teacher_offers_the_default_password_from_the_env_to_an_admin(): void
    {
        $this->asAdmin();

        Livewire::test(CreateTeacher::class)->assertFormSet(['password' => $this->defaultTeacher]);
    }

    public function test_a_teacher_can_be_created_with_the_default_password(): void
    {
        $this->asAdmin();

        Livewire::test(CreateTeacher::class)
            ->fillForm(['first_name' => 'Marta', 'last_name' => 'Puig', 'email' => 'marta@example.org', 'status' => 'active'])
            ->call('create')
            ->assertHasNoFormErrors();

        $teacher = CampusTeacher::where('email', 'marta@example.org')->firstOrFail();
        $this->assertTrue(Hash::check($this->defaultTeacher, $teacher->password));
    }

    public function test_without_a_default_in_the_env_nothing_is_offered(): void
    {
        config(['seeder.teacher_password' => null]);
        $this->asAdmin();

        Livewire::test(CreateTeacher::class)->assertFormSet(['password' => null]);
    }

    public function test_the_default_password_is_only_offered_to_admin_and_manager(): void
    {
        foreach (['createTresoreria', 'createSecretaria', 'createViewer'] as $role) {
            $this->actingAs($this->{$role}());
            $this->assertFalse(PasswordField::canUseDefault('teacher_password'), $role);
        }

        $this->actingAs($this->createAdmin());
        $this->assertTrue(PasswordField::canUseDefault('teacher_password'));
        $this->actingAs($this->createManager());
        $this->assertTrue(PasswordField::canUseDefault('student_password'));

        config(['seeder.student_password' => '']);
        $this->assertFalse(PasswordField::canUseDefault('student_password'), "Sense valor al .env no s'ofereix res.");
    }

    public function test_a_manager_also_gets_the_default_password(): void
    {
        $this->actingAs($this->createManager());

        Livewire::test(CreateTeacher::class)->assertFormSet(['password' => $this->defaultTeacher]);
    }

    // ── Alumnes ─────────────────────────────────────────────────────────────

    public function test_the_student_create_form_offers_the_default_password(): void
    {
        $this->asAdmin();

        Livewire::test(CreateCampusStudent::class)->assertFormSet(['password' => $this->defaultStudent]);
    }

    public function test_an_admin_creates_a_student_with_the_default_password_and_a_verified_email(): void
    {
        $this->asAdmin();

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'Pau', 'last_name' => 'Ferrer', 'email' => 'pau@example.org', 'phone' => '600123456'])
            ->call('create')
            ->assertHasNoFormErrors();

        $student = CampusStudent::where('email', 'pau@example.org')->firstOrFail();
        $this->assertSame(['Pau', 'Ferrer', '600123456'], [$student->first_name, $student->last_name, $student->phone]);
        $this->assertTrue(Hash::check($this->defaultStudent, $student->password));
        $this->assertNotNull($student->email_verified_at);
        $this->assertFalse($student->data_consent);
    }

    public function test_a_student_can_be_created_with_a_custom_password(): void
    {
        $this->asAdmin();

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'Laia', 'last_name' => 'Soler', 'email' => 'laia@example.org', 'password' => $this->ownPassword])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check($this->ownPassword, CampusStudent::where('email', 'laia@example.org')->firstOrFail()->password));
    }

    public function test_the_student_password_is_required_on_create(): void
    {
        $this->asAdmin();

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'Pau', 'last_name' => 'Ferrer', 'email' => 'pau@example.org', 'password' => ''])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required']);

        $this->assertDatabaseMissing('campus_students', ['email' => 'pau@example.org']);
    }

    public function test_a_student_email_must_be_unique_and_valid(): void
    {
        $this->asAdmin();
        CampusStudent::factory()->create(['email' => 'repetit@example.org']);

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'A', 'last_name' => 'B', 'email' => 'repetit@example.org'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique']);

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'A', 'last_name' => 'B', 'email' => 'no-es-correu'])
            ->call('create')
            ->assertHasFormErrors(['email' => 'email']);
    }

    public function test_editing_a_student_changes_the_password_only_when_one_is_written(): void
    {
        $this->asAdmin();
        $student = CampusStudent::factory()->create(['password' => $this->oldPassword, 'phone' => '600123456']);

        Livewire::test(EditCampusStudent::class, ['record' => $student->getKey()])
            ->assertFormSet(['password' => null])
            ->fillForm(['first_name' => 'Canviat', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check($this->oldPassword, $student->fresh()->password));
        $this->assertSame('Canviat', $student->fresh()->first_name);

        Livewire::test(EditCampusStudent::class, ['record' => $student->getKey()])
            ->fillForm(['password' => $this->newPassword])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check($this->newPassword, $student->fresh()->password));
    }

    public function test_the_student_can_log_in_with_the_password_set_by_the_admin(): void
    {
        $this->asAdmin();

        Livewire::test(CreateCampusStudent::class)
            ->fillForm(['first_name' => 'Pau', 'last_name' => 'Ferrer', 'email' => 'pau@example.org'])
            ->call('create')
            ->assertHasNoFormErrors();

        $student = CampusStudent::where('email', 'pau@example.org')->firstOrFail();
        $this->assertTrue(auth('student')->validate(['email' => 'pau@example.org', 'password' => $this->defaultStudent]));
    }
}
