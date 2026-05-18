<?php

namespace Tests\Unit\Models;

use App\Models\CampusSeason;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusSeasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_quadrimester_label_q1(): void
    {
        $season = CampusSeason::factory()->q1()->make();
        $this->assertSame('Tardor (set–gen)', $season->quadrimester_label);
    }

    public function test_quadrimester_label_q2(): void
    {
        $season = CampusSeason::factory()->q2()->make();
        $this->assertSame('Primavera (feb–jun)', $season->quadrimester_label);
    }

    public function test_full_name_attribute(): void
    {
        $season = CampusSeason::factory()->q1(2026)->make(['name' => 'Tardor 2026']);
        $this->assertSame('Tardor 2026 · Tardor (set–gen)', $season->full_name);
    }

    public function test_active_scope_returns_only_active(): void
    {
        CampusSeason::factory()->q1(2025)->create(['is_active' => false]);
        $active = CampusSeason::factory()->q2(2025)->create(['is_active' => true]);

        $result = CampusSeason::active();

        $this->assertNotNull($result);
        $this->assertEquals($active->id, $result->id);
    }

    public function test_active_scope_returns_null_when_none_active(): void
    {
        CampusSeason::factory()->q1()->create(['is_active' => false]);

        $this->assertNull(CampusSeason::active());
    }

    public function test_unique_year_quadrimester_throws_on_duplicate(): void
    {
        CampusSeason::factory()->q1(2026)->create();

        $this->expectException(QueryException::class);

        CampusSeason::factory()->q1(2026)->create();
    }
}
