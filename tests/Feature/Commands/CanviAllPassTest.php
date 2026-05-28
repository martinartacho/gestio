<?php

namespace Tests\Feature\Commands;

use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CanviAllPassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('seeder.admin_password',   'nova-admin-pass');
        Config::set('seeder.user_password',    'nova-user-pass');
        Config::set('seeder.student_password', 'nova-student-pass');
        Config::set('seeder.teacher_password', 'nova-teacher-pass');

        Role::firstOrCreate(['name' => 'admin',     'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
    }

    public function test_actualitza_contrasenyes_de_tots_els_grups(): void
    {
        $admin = User::factory()->create(['password' => Hash::make('vella')]);
        $admin->assignRole('admin');

        $user = User::factory()->create(['password' => Hash::make('vella')]);
        $user->assignRole('secretaria');

        $student = CampusStudent::factory()->create(['password' => Hash::make('vella')]);
        $teacher = CampusTeacher::factory()->create(['password' => Hash::make('vella')]);

        $this->artisan('app:canvi-all-pass', ['--force' => true])
             ->assertExitCode(0);

        $this->assertTrue(Hash::check('nova-admin-pass',   $admin->fresh()->password));
        $this->assertTrue(Hash::check('nova-user-pass',    $user->fresh()->password));
        $this->assertTrue(Hash::check('nova-student-pass', $student->fresh()->password));
        $this->assertTrue(Hash::check('nova-teacher-pass', $teacher->fresh()->password));
    }

    public function test_falla_si_falta_password_al_env(): void
    {
        Config::set('seeder.admin_password', null);

        $this->expectException(\RuntimeException::class);

        $this->artisan('app:canvi-all-pass', ['--force' => true]);
    }
}
