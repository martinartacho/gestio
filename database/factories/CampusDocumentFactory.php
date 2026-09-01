<?php

namespace Database\Factories;

use App\Models\CampusCourse;
use App\Models\CampusDocument;
use App\Models\CampusTeacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusDocumentFactory extends Factory
{
    protected $model = CampusDocument::class;

    public function definition(): array
    {
        return [
            'tenant_id'          => fn () => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'title'              => $this->faker->sentence(3),
            'description'        => $this->faker->optional()->sentence(),
            'type'               => 'url',
            'file_path'          => null,
            'file_name'          => null,
            'file_size'          => null,
            'mime_type'          => null,
            'url'                => $this->faker->url(),
            'course_id'          => null,
            'teacher_id'         => null,
            'created_by'         => null,
            'visibility'         => 'enrolled',
            'inherit_to_editions'=> false,
            'available_from'     => null,
            'session_number'     => null,
            'sort_order'         => 0,
            'status'             => 'active',
        ];
    }

    public function forCourse(int $courseId): static
    {
        return $this->state(['course_id' => $courseId]);
    }

    public function forTeacher(int $teacherId): static
    {
        return $this->state(['teacher_id' => $teacherId]);
    }

    public function public(): static
    {
        return $this->state(['visibility' => 'public']);
    }

    public function private(): static
    {
        return $this->state(['visibility' => 'private']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
