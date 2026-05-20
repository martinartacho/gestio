<?php

namespace Tests\Unit\Models;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_statuses_constants_exist(): void
    {
        $this->assertArrayHasKey('pending',   CampusEnrollment::STATUSES);
        $this->assertArrayHasKey('paid',      CampusEnrollment::STATUSES);
        $this->assertArrayHasKey('cancelled', CampusEnrollment::STATUSES);
        $this->assertArrayHasKey('refunded',  CampusEnrollment::STATUSES);
    }

    private function baseEnrollmentData(CampusStudent $student, CampusCourse $course): array
    {
        return [
            'student_id'      => $student->id,
            'course_id'       => $course->id,
            'first_name'      => $student->first_name,
            'last_name'       => $student->last_name,
            'email'           => $student->email,
            'enrollment_date' => now()->toDateString(),
        ];
    }

    public function test_enrollment_defaults_to_pending(): void
    {
        $student = CampusStudent::factory()->create();
        $course  = CampusCourse::factory()->create();

        $enrollment = CampusEnrollment::create($this->baseEnrollmentData($student, $course));

        $this->assertSame('pending', $enrollment->fresh()->status);
    }

    public function test_is_paid_returns_true_when_paid(): void
    {
        $student = CampusStudent::factory()->create();
        $course  = CampusCourse::factory()->create();

        $enrollment = CampusEnrollment::create(array_merge(
            $this->baseEnrollmentData($student, $course),
            ['status' => 'paid', 'paid_at' => now()],
        ));

        $this->assertTrue($enrollment->isPaid());
    }

    public function test_enrollment_belongs_to_student(): void
    {
        $student    = CampusStudent::factory()->create();
        $course     = CampusCourse::factory()->create();
        $enrollment = CampusEnrollment::create($this->baseEnrollmentData($student, $course));

        $this->assertInstanceOf(CampusStudent::class, $enrollment->student);
    }

    public function test_enrollment_belongs_to_course(): void
    {
        $student    = CampusStudent::factory()->create();
        $course     = CampusCourse::factory()->create();
        $enrollment = CampusEnrollment::create($this->baseEnrollmentData($student, $course));

        $this->assertInstanceOf(CampusCourse::class, $enrollment->course);
    }
}
