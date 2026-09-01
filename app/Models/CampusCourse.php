<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CampusCourse extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'campus_courses';

    protected $fillable = [
        'tenant_id',
        'code', 'title', 'slug', 'parent_id',
        'season_id', 'category_id', 'space_id', 'time_slot_id',
        'start_date', 'end_date', 'calendar_notes',
        'sessions', 'hours',
        'format', 'max_students', 'price',
        'description', 'objectives', 'requirements',
        'status', 'is_public', 'open_enrollment', 'created_by',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'sessions'        => 'integer',
        'hours'           => 'integer',
        'max_students'    => 'integer',
        'price'           => 'decimal:2',
        'is_public'       => 'boolean',
        'open_enrollment' => 'boolean',
    ];

    // ── Constants ──────────────────────────────────────────────────────────
    public const FORMATS = [
        'presencial'    => 'Presencial',
        'online'        => 'Online',
        'semipresencial'=> 'Semipresencial',
        'hibrid'        => 'Híbrid',
    ];

    public const STATUSES = [
        'draft'    => 'Esborrany',
        'planning' => 'Planificació',
        'active'   => 'Actiu',
        'closed'   => 'Tancat',
    ];

    public const STATUS_COLORS = [
        'draft'    => 'gray',
        'planning' => 'blue',
        'active'   => 'green',
        'closed'   => 'red',
    ];

    // ── Boot: slug automàtic ───────────────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $course) {
            if (empty($course->slug)) {
                $course->slug = static::generateUniqueSlug($course);
            }
        });

        static::updating(function (self $course) {
            if ($course->isDirty('title') && empty($course->slug)) {
                $course->slug = static::generateUniqueSlug($course);
            }
        });
    }

    protected static function generateUniqueSlug(self $course): string
    {
        $base  = Str::slug($course->code ?? $course->title);
        $slug  = $base;
        $count = 1;

        while (static::where('slug', $slug)
                      ->where('id', '!=', $course->id ?? 0)
                      ->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    // ── Relacions ──────────────────────────────────────────────────────────
    public function season(): BelongsTo
    {
        return $this->belongsTo(CampusSeason::class, 'season_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CampusCategory::class, 'category_id');
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(CampusSpace::class, 'space_id');
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(CampusTimeSlot::class, 'time_slot_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CampusCourse::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CampusCourse::class, 'parent_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusTeacher::class,
            'campus_course_teacher',
            'course_id',
            'teacher_id'
        )->withPivot('role', 'sessions_assigned')
         ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CampusEnrollment::class, 'course_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CampusDocument::class, 'course_id')->orderBy('sort_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(LmsLesson::class, 'course_id')
            ->orderBy('sort_order')
            ->orderBy('session_number');
    }

    public function lmsCertificates(): HasMany
    {
        return $this->hasMany(LmsCourseCertificate::class, 'course_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusStudent::class,
            'campus_course_student',
            'course_id',
            'student_id'
        )->withPivot('enrollment_id', 'enrolled_at')
         ->withTimestamps();
    }

    public function mainTeacher(): ?CampusTeacher
    {
        return $this->teachers()
                    ->wherePivot('role', 'main')
                    ->first();
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function isVisible(): bool
    {
        return $this->status === 'active' && $this->is_public;
    }

    public function isTemplate(): bool
    {
        return is_null($this->parent_id);
    }

    public function isEdition(): bool
    {
        return ! is_null($this->parent_id);
    }

    public function hasUnlimitedPlaces(): bool
    {
        return is_null($this->max_students);
    }

    /**
     * Estàtus que reserven plaça.
     * Exclou 'cancelled' i 'refunded' perquè alliberen el lloc.
     */
    public const ACTIVE_STATUSES = ['pending', 'paid', 'confirmed', 'completed'];

    /**
     * Nombre de matrícules actives (pending+paid+confirmed+completed).
     * Usa `active_enrollments_count` si l'has carregat amb withCount().
     */
    public function activeEnrollmentsCount(): int
    {
        // Aprofita el withCount si ja existeix per evitar queries extra
        if (isset($this->attributes['active_enrollments_count'])) {
            return (int) $this->attributes['active_enrollments_count'];
        }

        return $this->enrollments()
                    ->whereIn('status', self::ACTIVE_STATUSES)
                    ->count();
    }

    /** Places disponibles: null si el curs no té límit. */
    public function availableSlots(): ?int
    {
        if ($this->hasUnlimitedPlaces()) {
            return null;
        }

        return max(0, $this->max_students - $this->activeEnrollmentsCount());
    }

    /** Retorna true si el curs ha assolit el màxim d'alumnes permesos. */
    public function isFull(): bool
    {
        if ($this->hasUnlimitedPlaces()) {
            return false;
        }

        return $this->activeEnrollmentsCount() >= $this->max_students;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nombre de sessions ja celebrades (fins avui).
     * Retorna null si no hi ha prou dades per calcular-ho.
     */
    public function sessionsPast(): ?int
    {
        if (! $this->sessions || ! $this->start_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($today < $this->start_date) {
            return 0;
        }

        // Prioritat: dates reals de calendar_notes
        if ($this->calendar_notes) {
            $dates = $this->parseSessionDates($this->calendar_notes, $this->start_date);
            if ($dates->isNotEmpty()) {
                return $dates->filter(fn($d) => $d->lte($today))->count();
            }
        }

        // Fallback: estimació setmanal fins end_date
        $end = $this->end_date ?? $this->start_date->copy()->addWeeks($this->sessions - 1);

        if ($today >= $end) {
            return $this->sessions;
        }

        $weeksDone = (int) ($this->start_date->diffInDays($today) / 7) + 1;

        return min($weeksDone, $this->sessions);
    }

    /**
     * Parseja tokens "d/m" o "d/m/yyyy" des de calendar_notes.
     */
    public function parseSessionDates(string $notes, Carbon $startDate): Collection
    {
        $year      = $startDate->year;
        $prevMonth = 0;

        return collect(array_filter(array_map('trim', explode(',', $notes))))
            ->map(function (string $part) use (&$year, &$prevMonth) {
                if (! preg_match('/^(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?$/', $part, $m)) {
                    return null;
                }

                $day   = (int) $m[1];
                $month = (int) $m[2];
                $y     = isset($m[3])
                    ? (strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3])
                    : null;

                if ($y === null) {
                    if ($prevMonth > 0 && $month < $prevMonth) {
                        $year++;
                    }
                    $y = $year;
                }

                $prevMonth = $month;

                try {
                    return Carbon::createFromDate($y, $month, $day)->startOfDay();
                } catch (\Exception) {
                    return null;
                }
            })
            ->filter();
    }
}
