<?php

namespace Tests\Unit\Models;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_be_created(): void
    {
        $student = CampusStudent::factory()->create([
            'first_name' => 'Marc',
            'last_name'  => 'Puig',
            'email'      => 'marc@test.cat',
        ]);

        $this->assertDatabaseHas('campus_students', [
            'email' => 'marc@test.cat',
        ]);
        $this->assertSame('Marc Puig', $student->full_name);
    }

    public function test_dni_is_encrypted(): void
    {
        $student = CampusStudent::factory()->create(['dni' => '12345678A']);

        $this->assertSame('12345678A', $student->fresh()->dni);
        $this->assertDatabaseMissing('campus_students', ['dni' => '12345678A']);
    }

    public function test_student_has_enrollments_relation(): void
    {
        $student = CampusStudent::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $student->enrollments());
    }

    public function test_student_has_courses_relation(): void
    {
        $student = CampusStudent::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $student->courses());
    }
}
