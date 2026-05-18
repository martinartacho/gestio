<?php

namespace Tests\Unit\Models;

use App\Models\CampusCourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_generated_from_code_on_create(): void
    {
        $course = CampusCourse::factory()->create(['code' => 'CRS-001', 'title' => 'Curs de Prova']);

        $this->assertSame('crs-001', $course->slug);
    }

    public function test_slug_generated_from_title_when_code_is_null(): void
    {
        $course = CampusCourse::factory()->create(['code' => null, 'title' => 'Curs de Prova']);

        $this->assertSame('curs-de-prova', $course->slug);
    }

    public function test_slug_is_unique_on_conflict(): void
    {
        CampusCourse::factory()->create(['code' => null, 'title' => 'Curs de Prova', 'slug' => 'curs-de-prova']);
        $second = CampusCourse::factory()->create(['code' => null, 'title' => 'Curs de Prova', 'slug' => null]);

        $this->assertSame('curs-de-prova-1', $second->slug);
    }

    public function test_is_template_when_no_parent(): void
    {
        $course = CampusCourse::factory()->make(['parent_id' => null]);

        $this->assertTrue($course->isTemplate());
        $this->assertFalse($course->isEdition());
    }

    public function test_is_edition_when_has_parent(): void
    {
        $parent = CampusCourse::factory()->create();
        $edition = CampusCourse::factory()->edition($parent->id)->make();

        $this->assertTrue($edition->isEdition());
        $this->assertFalse($edition->isTemplate());
    }

    public function test_has_unlimited_places_when_max_students_is_null(): void
    {
        $course = CampusCourse::factory()->unlimited()->make();

        $this->assertTrue($course->hasUnlimitedPlaces());
    }

    public function test_has_limited_places_when_max_students_is_set(): void
    {
        $course = CampusCourse::factory()->make(['max_students' => 20]);

        $this->assertFalse($course->hasUnlimitedPlaces());
    }

    public function test_status_label_returns_correct_value(): void
    {
        $course = CampusCourse::factory()->make(['status' => 'active']);

        $this->assertSame('Actiu', $course->status_label);
    }

    public function test_status_color_returns_correct_value(): void
    {
        $course = CampusCourse::factory()->make(['status' => 'planning']);

        $this->assertSame('blue', $course->status_color);
    }
}
