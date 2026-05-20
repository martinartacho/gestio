<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_index_is_accessible(): void
    {
        $this->get('/cursos')->assertSuccessful();
    }

    public function test_catalog_shows_public_active_courses(): void
    {
        $season = CampusSeason::factory()->create(['status' => 'active']);

        $public = CampusCourse::factory()->create([
            'season_id' => $season->id,
            'status'    => 'active',
            'is_public' => true,
            'title'     => 'Curs Visible',
        ]);

        $private = CampusCourse::factory()->create([
            'season_id' => $season->id,
            'status'    => 'active',
            'is_public' => false,
            'title'     => 'Curs Ocult',
        ]);

        $this->get('/cursos')
             ->assertSee('Curs Visible')
             ->assertDontSee('Curs Ocult');
    }

    public function test_course_detail_page_loads(): void
    {
        $season = CampusSeason::factory()->create(['status' => 'active']);
        $course = CampusCourse::factory()->create([
            'season_id' => $season->id,
            'status'    => 'active',
            'is_public' => true,
            'title'     => 'Curs de Proves',
        ]);

        $this->get('/cursos/' . $course->slug)->assertSuccessful()->assertSee('Curs de Proves');
    }

    public function test_non_public_course_returns_404(): void
    {
        $course = CampusCourse::factory()->create(['is_public' => false]);

        $this->get('/cursos/' . $course->slug)->assertNotFound();
    }
}
