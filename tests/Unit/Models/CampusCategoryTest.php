<?php

namespace Tests\Unit\Models;

use App\Models\CampusCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_auto_generated_from_name(): void
    {
        $category = CampusCategory::factory()->create(['name' => 'Idiomes i Comunicació', 'slug' => null]);

        $this->assertSame('idiomes-i-comunicacio', $category->slug);
    }

    public function test_slug_not_overwritten_if_provided(): void
    {
        $category = CampusCategory::factory()->create(['name' => 'Idiomes', 'slug' => 'custom-slug']);

        $this->assertSame('custom-slug', $category->slug);
    }

    public function test_color_constants_cover_expected_keys(): void
    {
        $expected = ['gray', 'red', 'orange', 'yellow', 'green', 'blue', 'indigo', 'purple', 'pink'];

        $this->assertSame($expected, array_keys(CampusCategory::COLORS));
    }
}
