<?php

namespace Tests\Feature\Filament;

use App\Models\CampusCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    public function test_can_list_categories(): void
    {
        CampusCategory::factory()->count(3)->create();

        $this->actingAs($this->admin)
             ->get('/admin/campus/categories')
             ->assertSuccessful();
    }

    public function test_can_access_create_category_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/campus/categories/create')
             ->assertSuccessful();
    }

    public function test_can_access_edit_category_form(): void
    {
        $category = CampusCategory::factory()->create();

        $this->actingAs($this->admin)
             ->get("/admin/campus/categories/{$category->id}/edit")
             ->assertSuccessful();
    }

    public function test_slug_auto_generated_on_create(): void
    {
        $category = CampusCategory::factory()->create(['name' => 'Idiomes', 'slug' => null]);

        $this->assertSame('idiomes', $category->fresh()->slug);
    }

    public function test_category_stored_in_database(): void
    {
        CampusCategory::factory()->create(['name' => 'Tecnologia', 'color' => 'blue']);

        $this->assertDatabaseHas('campus_categories', ['name' => 'Tecnologia', 'color' => 'blue']);
    }
}
