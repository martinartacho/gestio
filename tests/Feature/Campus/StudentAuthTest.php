<?php

namespace Tests\Feature\Campus;

use App\Models\CampusStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/portal/login')->assertSuccessful();
    }

    public function test_register_page_is_accessible(): void
    {
        $this->get('/portal/registre')->assertSuccessful();
    }

    public function test_student_can_register(): void
    {
        $this->post('/portal/registre', [
            'first_name'            => 'Marc',
            'last_name'             => 'Puig',
            'email'                 => 'marc@test.cat',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'data_consent'          => '1',
        ])->assertRedirect('/portal/meus-cursos');

        $this->assertDatabaseHas('campus_students', ['email' => 'marc@test.cat']);
    }

    public function test_student_can_login(): void
    {
        $student = CampusStudent::factory()->create([
            'email'    => 'marc@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/portal/login', [
            'email'    => 'marc@test.cat',
            'password' => 'password123',
        ])->assertRedirect('/portal/meus-cursos');
    }

    public function test_wrong_password_is_rejected(): void
    {
        CampusStudent::factory()->create([
            'email'    => 'marc@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/portal/login', [
            'email'    => 'marc@test.cat',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    }

    public function test_portal_redirects_unauthenticated_student(): void
    {
        $this->get('/portal/meus-cursos')->assertRedirect('/portal/login');
    }

    public function test_authenticated_student_can_access_portal(): void
    {
        $student = CampusStudent::factory()->create();

        $this->actingAs($student, 'student')
             ->get('/portal/meus-cursos')
             ->assertSuccessful();
    }
}
