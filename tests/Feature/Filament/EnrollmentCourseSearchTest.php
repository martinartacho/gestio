<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EnrollmentResource\Pages\CreateEnrollment;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

/** Inscripcions: el desplegable de curs es pot cercar pel títol, pel codi o per la temporada. */
class EnrollmentCourseSearchTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createAdmin());
    }

    private function course(string $title, string $code, string $status = 'active', ?CampusSeason $season = null): CampusCourse
    {
        return CampusCourse::factory()->create([
            'title'     => $title,
            'code'      => $code,
            'status'    => $status,
            'season_id' => ($season ?? CampusSeason::factory()->create(['name' => 'Tardor 2025']))->id,
        ]);
    }

    /** Resultats de cerca del camp «Curs» tal com els veu l'administrador. */
    private function search(string $term): array
    {
        $found = [];
        Livewire::test(CreateEnrollment::class)->assertFormFieldExists('course_id', function (Select $field) use ($term, &$found): bool {
            $found = $field->getSearchResults($term);

            return true;
        });

        return $found;
    }

    public function test_the_create_page_loads(): void
    {
        $this->get('/admin/campus/enrollments/create')->assertSuccessful();
    }

    public function test_a_course_is_found_by_its_title(): void
    {
        $course = $this->course('Alimentació i Salut Cardiovascular', 'ALI-101');
        $this->course('Aprenentatge Cooperatiu', 'APR-202');

        $this->assertSame([$course->id], array_keys($this->search('cardiovas')));
    }

    public function test_a_course_is_found_by_its_code_and_the_label_shows_the_code(): void
    {
        $course = $this->course('Alimentació i Salut Cardiovascular', 'ALI-101');
        $this->course('Aprenentatge Cooperatiu', 'APR-202');

        $found = $this->search('ali-101');

        $this->assertSame([$course->id], array_keys($found));
        $this->assertSame('[Tardor 2025] ALI-101 · Alimentació i Salut Cardiovascular', $found[$course->id]);
    }

    public function test_a_partial_code_finds_every_course_that_matches(): void
    {
        $a = $this->course('Primer', 'MUS-001');
        $b = $this->course('Segon', 'MUS-002');
        $this->course('Altre', 'DAN-001');

        $found = array_keys($this->search('MUS-'));
        sort($found);

        $this->assertSame([$a->id, $b->id], $found);
    }

    public function test_the_season_name_also_finds_its_courses(): void
    {
        $spring = CampusSeason::factory()->create(['name' => 'Primavera 2026']);
        $inSpring = $this->course('Cuina', 'CUI-1', 'active', $spring);
        $this->course('Pintura', 'PIN-1');

        $this->assertSame([$inSpring->id], array_keys($this->search('primavera')));
    }

    public function test_draft_courses_are_never_offered(): void
    {
        $this->course('Esborrany', 'ESB-1', 'draft');

        $this->assertSame([], $this->search('esborrany'));
        $this->assertSame([], $this->search('ESB-1'));
    }

    public function test_a_search_with_no_match_gives_nothing_and_a_percent_sign_is_not_a_wildcard(): void
    {
        $this->course('Cuina', 'CUI-1');

        $this->assertSame([], $this->search('zzzz'));
        $this->assertSame([], $this->search('%'));
    }

    public function test_the_first_options_are_listed_with_their_codes_and_capped_at_fifty(): void
    {
        foreach (range(1, 55) as $n) {
            $this->course(sprintf('Curs %02d', $n), sprintf('C-%02d', $n));
        }

        $options = [];
        Livewire::test(CreateEnrollment::class)->assertFormFieldExists('course_id', function (Select $field) use (&$options): bool {
            $options = $field->getOptions();

            return true;
        });

        $this->assertCount(50, $options);
        $this->assertSame('[Tardor 2025] C-01 · Curs 01', reset($options));
    }

    public function test_the_label_of_a_chosen_course_is_shown_even_if_it_has_no_code(): void
    {
        $course = CampusCourse::factory()->create(['title' => 'Sense codi', 'code' => null, 'status' => 'active']);

        $label = null;
        Livewire::test(CreateEnrollment::class)->assertFormFieldExists('course_id', function (Select $field) use ($course, &$label): bool {
            $label = $field->getOptionLabel(true);

            return true;
        })->fillForm(['course_id' => $course->id])->assertFormFieldExists('course_id', function (Select $field) use (&$label): bool {
            $label = $field->getOptionLabel();

            return true;
        });

        $this->assertStringEndsWith('] Sense codi', (string) $label);
        $this->assertStringNotContainsString('·', (string) $label);
    }
}
