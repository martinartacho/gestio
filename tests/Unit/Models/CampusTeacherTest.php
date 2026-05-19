<?php

namespace Tests\Unit\Models;

use App\Models\CampusTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_concatenates_first_and_last(): void
    {
        $teacher = CampusTeacher::factory()->make([
            'first_name' => 'Maria',
            'last_name'  => 'García',
        ]);

        $this->assertSame('Maria García', $teacher->full_name);
    }

    public function test_status_constants_exist(): void
    {
        $this->assertArrayHasKey('active', CampusTeacher::STATUSES);
        $this->assertArrayHasKey('inactive', CampusTeacher::STATUSES);
    }

    public function test_payment_status_constants_exist(): void
    {
        foreach (['pending', 'confirmed', 'paid', 'cancelled'] as $key) {
            $this->assertArrayHasKey($key, CampusTeacher::PAYMENT_STATUSES);
            $this->assertArrayHasKey($key, CampusTeacher::PAYMENT_STATUS_COLORS);
        }
    }

    public function test_fiscal_situation_constants_exist(): void
    {
        foreach (['autonom', 'empresa', 'entitat', 'particular'] as $key) {
            $this->assertArrayHasKey($key, CampusTeacher::FISCAL_SITUATIONS);
        }
    }

    public function test_payment_type_constants_exist(): void
    {
        foreach (['own', 'beneficiary', 'waived'] as $key) {
            $this->assertArrayHasKey($key, CampusTeacher::PAYMENT_TYPES);
        }
    }

    public function test_areas_cast_stores_and_retrieves_array(): void
    {
        $teacher = CampusTeacher::factory()->create([
            'areas' => ['Programació', 'Bases de Dades'],
        ]);

        $fresh = $teacher->fresh();
        $this->assertIsArray($fresh->areas);
        $this->assertContains('Programació', $fresh->areas);
        $this->assertContains('Bases de Dades', $fresh->areas);
    }

    public function test_boolean_fields_default_correctly(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->assertTrue($teacher->needs_payment);
        $this->assertFalse($teacher->data_consent);
        $this->assertFalse($teacher->fiscal_responsibility);
        $this->assertFalse($teacher->ceded_confirmation);
    }

    public function test_payment_status_defaults_to_pending(): void
    {
        $teacher = CampusTeacher::factory()->create();

        $this->assertSame('pending', $teacher->payment_status);
    }

    public function test_teacher_has_teacher_payments_relation(): void
    {
        $teacher = CampusTeacher::factory()->make();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $teacher->teacherPayments()
        );
    }
}
