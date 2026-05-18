<?php

namespace Tests\Feature\Filament;

use App\Models\CampusSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeasonResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create(['active' => true]);
        $this->admin->assignRole('admin');
    }

    public function test_can_list_seasons(): void
    {
        CampusSeason::factory()->q1(2026)->create();
        CampusSeason::factory()->q2(2026)->create();

        $this->actingAs($this->admin)
             ->get('/admin/seasons')
             ->assertSuccessful();
    }

    public function test_can_access_create_season_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/seasons/create')
             ->assertSuccessful();
    }

    public function test_can_access_edit_season_form(): void
    {
        $season = CampusSeason::factory()->q1(2026)->create();

        $this->actingAs($this->admin)
             ->get("/admin/seasons/{$season->id}/edit")
             ->assertSuccessful();
    }

    public function test_season_is_persisted_in_database(): void
    {
        CampusSeason::factory()->q1(2026)->create(['name' => 'Tardor 2026']);

        $this->assertDatabaseHas('campus_seasons', [
            'year'         => 2026,
            'quadrimester' => 1,
            'name'         => 'Tardor 2026',
        ]);
    }

    public function test_unique_year_quadrimester_constraint(): void
    {
        CampusSeason::factory()->q1(2026)->create();

        $this->assertDatabaseCount('campus_seasons', 1);

        $this->expectException(\Illuminate\Database\QueryException::class);
        CampusSeason::factory()->q1(2026)->create();
    }
}
