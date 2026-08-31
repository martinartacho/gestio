<?php

namespace App\Actions;

use App\Models\AssociatMember;
use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusNews;
use App\Models\CampusSeason;
use App\Models\CampusSpace;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\CampusTimeSlot;
use App\Models\Tenant;

/**
 * Genera dades d'exemple per a un tenant acabat de crear, perquè no calgui
 * fer-ho a mà per BD. Cada peça és independent (pots demanar només socis,
 * per exemple) — els cursos, si se'n demanen, es creen amb una temporada,
 * categoria, espai i franja horària noves (no reutilitza les de cap altre
 * tenant, per no barrejar dades entre entitats).
 */
class SeedTenantSampleData
{
    /**
     * @param array{news?:int,teachers?:int,courses?:int,students?:int,members?:int} $counts
     */
    public function run(Tenant $tenant, array $counts): void
    {
        $teachers = $this->seedTeachers($tenant, $counts['teachers'] ?? 0);
        $courses  = $this->seedCourses($tenant, $counts['courses'] ?? 0, $teachers);

        $this->seedStudents($tenant, $counts['students'] ?? 0, $courses);
        $this->seedMembers($tenant, $counts['members'] ?? 0);
        $this->seedNews($tenant, $counts['news'] ?? 0);
    }

    /** @return \Illuminate\Support\Collection<int,CampusTeacher> */
    private function seedTeachers(Tenant $tenant, int $count): \Illuminate\Support\Collection
    {
        if ($count < 1) {
            return collect();
        }

        return CampusTeacher::factory()->count($count)->create()
            ->each(fn (CampusTeacher $t) => $t->tenants()->sync([$tenant->id]));
    }

    /** @return \Illuminate\Support\Collection<int,CampusCourse> */
    private function seedCourses(Tenant $tenant, int $count, \Illuminate\Support\Collection $teachers): \Illuminate\Support\Collection
    {
        if ($count < 1) {
            return collect();
        }

        $season   = CampusSeason::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
        $category = CampusCategory::factory()->create(['tenant_id' => $tenant->id]);
        $space    = CampusSpace::factory()->create(['tenant_id' => $tenant->id]);
        $timeSlot = CampusTimeSlot::factory()->create(['tenant_id' => $tenant->id]);

        return CampusCourse::factory()->count($count)->active()->create([
            'tenant_id'    => $tenant->id,
            'season_id'    => $season->id,
            'category_id'  => $category->id,
            'space_id'     => $space->id,
            'time_slot_id' => $timeSlot->id,
        ])->each(function (CampusCourse $course) use ($teachers) {
            if ($teachers->isNotEmpty()) {
                $course->teachers()->attach($teachers->random()->id, ['role' => 'main']);
            }
        });
    }

    private function seedStudents(Tenant $tenant, int $count, \Illuminate\Support\Collection $courses): void
    {
        if ($count < 1) {
            return;
        }

        CampusStudent::factory()->count($count)->create()
            ->each(function (CampusStudent $student) use ($tenant, $courses) {
                $student->tenants()->sync([$tenant->id]);

                if ($courses->isNotEmpty()) {
                    CampusEnrollment::factory()->create([
                        'tenant_id'  => $tenant->id,
                        'student_id' => $student->id,
                        'course_id'  => $courses->random()->id,
                        'status'     => 'paid',
                        'first_name' => $student->first_name,
                        'last_name'  => $student->last_name,
                        'email'      => $student->email,
                    ]);
                }
            });
    }

    private function seedMembers(Tenant $tenant, int $count): void
    {
        if ($count < 1) {
            return;
        }

        AssociatMember::factory()->count($count)->create()
            ->each(fn (AssociatMember $m) => $m->tenants()->sync([$tenant->id]));
    }

    private function seedNews(Tenant $tenant, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            CampusNews::create([
                'tenant_id'    => $tenant->id,
                'title'        => fake('es_ES')->sentence(6),
                'summary'      => fake('es_ES')->sentence(12),
                'body'         => '<p>' . fake('es_ES')->paragraphs(2, true) . '</p>',
                'labels'       => ['sistema'],
                'version'      => null,
                'published_at' => now()->subDays($count - $i),
            ]);
        }
    }
}
