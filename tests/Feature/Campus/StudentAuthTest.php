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
        $this->get('/campus/portal/login')->assertSuccessful();
    }

    public function test_register_page_is_accessible(): void
    {
        $this->get('/campus/portal/registre')->assertSuccessful();
    }

    public function test_student_can_register(): void
    {
        $this->post('/campus/portal/registre', [
            'first_name'            => 'Marc',
            'last_name'             => 'Puig',
            'email'                 => 'marc@test.cat',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'data_consent'          => '1',
        ])->assertRedirect('/campus/portal/verificar-email'); // redirigeix a la pàgina de verificació

        $this->assertDatabaseHas('campus_students', [
            'email'             => 'marc@test.cat',
            'email_verified_at' => null, // no verificat fins que es faci clic a l'email
        ]);
    }

    public function test_student_can_login(): void
    {
        $student = CampusStudent::factory()->create([
            'email'    => 'marc@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/campus/portal/login', [
            'email'    => 'marc@test.cat',
            'password' => 'password123',
        ])->assertRedirect('/campus/portal/meus-cursos');
    }

    public function test_wrong_password_is_rejected(): void
    {
        CampusStudent::factory()->create([
            'email'    => 'marc@test.cat',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/campus/portal/login', [
            'email'    => 'marc@test.cat',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');
    }

    public function test_portal_redirects_unauthenticated_student(): void
    {
        $this->get('/campus/portal/meus-cursos')->assertRedirect('/campus/portal/login');
    }

    public function test_authenticated_student_can_access_portal(): void
    {
        $student = CampusStudent::factory()->create();

        $this->actingAs($student, 'student')
             ->get('/campus/portal/meus-cursos')
             ->assertSuccessful();
    }
}
