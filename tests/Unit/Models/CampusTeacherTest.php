<?php

namespace Tests\Unit\Models;

use App\Models\CampusTeacher;
use Tests\TestCase;

class CampusTeacherTest extends TestCase
{
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
}
