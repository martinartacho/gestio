<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampusCourse extends Model
{
    use HasFactory;
    protected $table = 'campus_courses';

    protected $fillable = [
        'code', 'title', 'slug', 'parent_id',
        'season_id', 'category_id', 'space_id', 'time_slot_id',
        'start_date', 'end_date', 'calendar_notes',
        'sessions', 'hours',
        'format', 'max_students', 'price',
        'description', 'objectives', 'requirements',
        'status', 'is_active', 'is_public', 'created_by',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'sessions'     => 'integer',
        'hours'        => 'integer',
        'max_students' => 'integer',
        'price'        => 'decimal:2',
        'is_active'    => 'boolean',
        'is_public'    => 'boolean',
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

    public function mainTeacher(): ?CampusTeacher
    {
        return $this->teachers()
                    ->wherePivot('role', 'main')
                    ->first();
    }

    // ── Helpers ────────────────────────────────────────────────────────────
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

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
