<?php

namespace App\Filament\Pages;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CalendarPage extends Page
{
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.calendar-page';

    public static function getNavigationIcon(): string   { return 'heroicon-o-calendar-days'; }
    public static function getNavigationLabel(): string  { return __('site.calendar'); }
    public static function getNavigationGroup(): string  { return __('site.courses_group'); }
    public function getTitle(): string                   { return __('site.calendar'); }

    public static function canAccess(): bool
    {
        $s = app(\App\Settings\SettingStore::class);
        return $s->getRaw('gestio_enabled', true)
            && $s->getRaw('gestio_calendari_enabled', true)
            && (Auth::user()?->hasAnyRole(['super-admin', 'admin', 'manager']) ?? false);
    }

    // ── Estat Livewire ────────────────────────────────────────────────────
    public ?int $currentSeasonId = null;
    public bool   $showCourseModal = false;
    public ?int   $selectedCourseId = null;
    public string $viewMode = 'month'; // 'month' | 'week'

    private const DAY_NAMES = [
        1 => 'Dilluns', 2 => 'Dimarts', 3 => 'Dimecres',
        4 => 'Dijous', 5 => 'Divendres', 6 => 'Dissabte',
    ];

    public function mount(): void
    {
        $active = CampusSeason::where('tenant_id', current_tenant()?->id)->where('status', 'active')->first();
        $this->currentSeasonId = $active?->id;
    }

    // ── Helpers per a la vista ────────────────────────────────────────────
    private const COLOR_HEX = [
        'red'    => '#ef4444',
        'blue'   => '#3b82f6',
        'purple' => '#a855f7',
        'indigo' => '#6366f1',
        'pink'   => '#ec4899',
        'green'  => '#22c55e',
        'orange' => '#f97316',
        'yellow' => '#eab308',
        'teal'   => '#14b8a6',
        'cyan'   => '#06b6d4',
    ];

    public function getLegendCategories(): \Illuminate\Support\Collection
    {
        return CampusCategory::where('tenant_id', current_tenant()?->id)
            ->whereHas('courses', function ($q) {
                if ($this->currentSeasonId) {
                    $q->where('season_id', $this->currentSeasonId);
                }
            })
            ->orderBy('order')
            ->get()
            ->map(fn($cat) => [
                'name'  => $cat->name,
                'color' => self::COLOR_HEX[$cat->color] ?? '#6b7280',
            ]);
    }

    public function getSeasonOptions(): array
    {
        return CampusSeason::where('tenant_id', current_tenant()?->id)
            ->orderByDesc('year')
            ->orderByDesc('quadrimester')
            ->get()
            ->mapWithKeys(fn($s) => [$s->id => $s->name])
            ->toArray();
    }

    /**
     * Graella d'horari setmanal recurrent: files = franges horàries úniques
     * (independents del dia), columnes = Dilluns→Dissabte. Mostra els cursos
     * de la temporada seleccionada assignats a cada dia+franja.
     *
     * @return array<int, array{start: string, end: string, days: array<int, \Illuminate\Support\Collection>}>
     */
    public function getWeeklyGrid(): array
    {
        $courses = CampusCourse::where('tenant_id', current_tenant()?->id)
            ->with(['timeSlot', 'space', 'category'])
            ->whereHas('timeSlot')
            ->when($this->currentSeasonId, fn($q) => $q->where('season_id', $this->currentSeasonId))
            ->get();

        $timeRanges = $courses
            ->map(fn($c) => $c->timeSlot->start_time . '|' . $c->timeSlot->end_time)
            ->unique()
            ->sort()
            ->values()
            ->map(fn($key) => explode('|', $key));

        $grid = [];
        foreach ($timeRanges as [$start, $end]) {
            $days = [];
            for ($day = 1; $day <= 6; $day++) {
                $days[$day] = $courses->filter(fn($c) =>
                    $c->timeSlot->day_of_week === $day
                    && $c->timeSlot->start_time === $start
                    && $c->timeSlot->end_time === $end
                )->values();
            }
            $grid[] = ['start' => $start, 'end' => $end, 'days' => $days];
        }

        return $grid;
    }

    public function getDayNames(): array
    {
        return self::DAY_NAMES;
    }

    public function colorHex(?string $color): string
    {
        return self::COLOR_HEX[$color] ?? '#6b7280';
    }

    public function getSelectedCourse(): ?CampusCourse
    {
        if (! $this->selectedCourseId) return null;
        return CampusCourse::where('tenant_id', current_tenant()?->id)
            ->with(['category', 'teachers', 'space', 'timeSlot', 'season'])
            ->find($this->selectedCourseId);
    }

    // ── Actions Livewire ──────────────────────────────────────────────────
    public function updatedCurrentSeasonId(): void
    {
        $this->dispatch('calendarSeasonChanged', seasonId: $this->currentSeasonId);
    }

    public function updatedViewMode(): void
    {
        $this->dispatch('calendarViewModeChanged', mode: $this->viewMode);
    }

    #[On('openCourseModal')]
    public function openCourseModal(int $courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->showCourseModal  = true;
    }

    public function closeCourseModal(): void
    {
        $this->showCourseModal  = false;
        $this->selectedCourseId = null;
    }

    #[On('courseDropped')]
    public function handleCourseDropped(int $courseId, string $newStart, string $newEnd = ''): void
    {
        if (! Auth::user()?->hasAnyRole(['super-admin', 'admin'])) {
            $this->dispatch('calendarRevertDrop');
            return;
        }

        $course = CampusCourse::where('tenant_id', current_tenant()?->id)->findOrFail($courseId);
        $start  = Carbon::parse($newStart)->toDateString();
        $end    = blank($newEnd)
            ? $start
            : Carbon::parse($newEnd)->subDay()->toDateString(); // FC end is exclusive

        $course->update(['start_date' => $start, 'end_date' => $end]);

        Notification::make()
            ->title(__('site.course_dates_updated'))
            ->success()
            ->send();
    }
}
