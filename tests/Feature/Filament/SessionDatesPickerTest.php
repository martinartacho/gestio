<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\CourseResource\Pages\EditCourse;
use App\Models\CampusCourse;
use App\Support\SessionDates;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

/** Calendari de sessions del curs: dies marcats ⇄ text de calendar_notes. */
class SessionDatesPickerTest extends TestCase
{
    use InteractsWithFilamentAdmin, RefreshDatabase;

    private function course(?string $notes, string $start = '2026-10-05'): CampusCourse
    {
        return CampusCourse::factory()->create([
            'start_date'     => $start,
            'end_date'       => '2027-01-31',
            'calendar_notes' => $notes,
            'sessions'       => 3,
        ]);
    }

    public function test_dates_round_trip_across_new_year(): void
    {
        $notes = '17/10, 7/11, 5/12, 4/1, 11/1';
        $start = Carbon::parse('2026-10-05');

        $dates = SessionDates::parse($notes, $start);

        $this->assertSame(['2026-10-17', '2026-11-07', '2026-12-05', '2027-01-04', '2027-01-11'], $dates);
        $this->assertSame($notes, SessionDates::serialize($dates, $start));
    }

    public function test_a_date_outside_the_deduced_year_gets_an_explicit_year(): void
    {
        $text = SessionDates::serialize(['2026-10-17', '2028-01-04'], Carbon::parse('2026-10-05'));

        $this->assertSame('17/10, 4/1/2028', $text);
        $this->assertSame(['2026-10-17', '2028-01-04'], SessionDates::parse($text, Carbon::parse('2026-10-05')));
    }

    public function test_empty_and_unreadable_text_give_no_dates(): void
    {
        $this->assertSame([], SessionDates::parse(null, null));
        $this->assertSame([], SessionDates::parse('Dilluns i dimecres', Carbon::parse('2026-10-05')));
        $this->assertSame('', SessionDates::serialize([], null));
    }

    public function test_the_edit_form_shows_the_saved_days(): void
    {
        $this->actingAs($this->createAdmin());
        $course = $this->course('17/10, 7/11, 4/1');

        Livewire::test(EditCourse::class, ['record' => $course->getKey()])
            ->assertFormSet(['calendar_notes' => ['2026-10-17', '2026-11-07', '2027-01-04']]);
    }

    public function test_marking_days_saves_the_text_and_counts_the_sessions(): void
    {
        $this->actingAs($this->createAdmin());
        $course = $this->course('5/10');

        Livewire::test(EditCourse::class, ['record' => $course->getKey()])
            ->fillForm(['calendar_notes' => ['2026-10-17', '2026-11-07', '2026-12-05', '2027-01-04']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('17/10, 7/11, 5/12, 4/1', $course->fresh()->calendar_notes);
        $this->assertCount(4, $course->fresh()->parseSessionDates($course->fresh()->calendar_notes, $course->start_date));
    }

    public function test_free_text_that_is_not_dates_is_kept_when_nothing_is_marked(): void
    {
        $this->actingAs($this->createAdmin());
        $course = $this->course('Dilluns i dimecres');

        Livewire::test(EditCourse::class, ['record' => $course->getKey()])
            ->assertFormSet(['calendar_notes' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Dilluns i dimecres', $course->fresh()->calendar_notes);
    }

    public function test_removing_every_day_clears_the_calendar(): void
    {
        $this->actingAs($this->createAdmin());
        $course = $this->course('17/10, 7/11');

        Livewire::test(EditCourse::class, ['record' => $course->getKey()])
            ->fillForm(['calendar_notes' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($course->fresh()->calendar_notes);
    }
}
